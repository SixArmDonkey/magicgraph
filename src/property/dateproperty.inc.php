<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */

declare( strict_types=1 );

namespace buffalokiwi\magicgraph\property;

use buffalokiwi\buffalotools\date\DateTimeWrapper;
use buffalokiwi\buffalotools\date\IDateFactory;
use buffalokiwi\magicgraph\ValidationException;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;


/**
 * A property backed by a DateTime object in UTC.
 * This expects ALL supplied values to ALWAYS BE IN UTC.  Using local timezones
 * will make things all sorts of stupid.  Don't be stupid.
 * 
 * If using DateTime mysql column types, ensure they are fed with UTC dates/times.
 * If using Timestamp mysql column types, the default MariaDBConnectionProperties object 
 * shipped with this package will automatically set the time zone offset to +00:00.  
 * 
 * Ensure the storage engine returns datetime in UTC.
 * 
 * I can tell you again, but whatever.
 */
class DateProperty extends AbstractProperty implements IDateProperty
{
  /**
   *
   * @var IDateFactory 
   */
  private $dateFactory;
  
  /**
   * Foramt to use when converting this property to a string.
   * This will be in UTC.
   * @var string
   */
  private $toStringFormat;
  
  
  /**
   * Create a new DateProperty instance 
   * @param IPropertyBuilder $builder Builder
   * @param IDateFactory $dateFactory Factory for parsing date strings 
   * @param string $toStringFormat Format to use when converting this property to a string.  ie: to write to a database.
   */
  public function __construct( IPropertyBuilder $builder, IDateFactory $dateFactory, string $toStringFormat = 'Y-m-d H:i:s' )
  {
    parent::__construct( $builder );
    
    if ( empty( $toStringFormat ))
      throw new \InvalidArgumentException( 'toStringFormat must not be empty' );
    
    $this->dateFactory = $dateFactory;
    $this->toStringFormat = $toStringFormat;
  }
  
  

  /**
   * Retrieve the stored value as a DateTime object 
   * @return DateTimeInterface value 
   */
  public function getValueAsDateTime() : DateTimeInterface
  {
    return $this->getValue()->getUTC();
  }
  
  
  
  /**
   * All properties must be able to be cast to a string.
   * If value is an array, it will be serialized by default.
   * Classes overriding this method may change this behavior.
   * 
   * Values other than array are simply cast to a string.  Here be dragons.
   * 
   * @return string property value 
   */
  public function __toString()
  {
    $val = $this->getValue();
    if ( $val === null )
      return null;
    
    return $this->getValueAsDateTime()->format( $this->toStringFormat );
  }  
  
  
  /**
   * Validate some property value.
   * Child classes should implement some sort of validation based on the 
   * property type.
   * @param mixed $value The property value 
   * @throws ValidationException If the supplied value is not valid 
   */
  protected function validatePropertyValue( $value ) : void
  {
    if ( $this->getFlags()->hasVal( SPropertyFlags::USE_NULL ) && $value === null )
      return; //..This is ok    
  }
  
  
  /**
   * Called after the behavior callback setter, and BEFORE validate.
   * Override this to prepare data for validation.
   * 
   * DO NOT USE THIS TO COMMIT DATA.
   * 
   * @param mixed $value Value being set.
   * @return mixed value to validate and set
   */
  protected function preparePropertyValue( $value )
  {
    if ( $value === null )
      return $value;
    
    if ( $value instanceof DateTimeInterface )
    {
      //..This is ok.
      if ( $value->getTimezone() !== false && $value->getTimezone()->getName() != 'UTC' )      
      {
        $dt = new \DateTime();
        $dt->setTimestamp( $value->getTimestamp());
        $dt->setTimezone( new DateTimeZone( 'UTC' ));
        $value = \DateTimeImmutable::createFromMutable( $dt );
      }
    }
    else if ( !( $value instanceof IDateTime ))
    {
      if ( empty( $value ))
        $value = '0000-00-00 00:00:00';
      
      try {
        $value = $this->dateFactory->createDateTime( $value );
      } catch( \Exception $e ) {
        throw new ValidationException( sprintf( 'Value %s for property %s must be parsable into a DateTime object.  Got %s', $value, $this->getName(), $e->getMessage()), 0, $e );
      }
    }
    
    return new DateTimeWrapper( $value, $this->dateFactory->getLocalTimeZone());
  }  
}
