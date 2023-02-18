<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */

declare( strict_types=1 );

namespace buffalokiwi\magicgraph\property;

use buffalokiwi\magicgraph\money\IMoney;
use buffalokiwi\magicgraph\money\IMoneyFactory;
use buffalokiwi\magicgraph\ValidationException;
use Exception;


/**
 * A property that stores money.
 * 
 * At the time of writing, this is locked to whatever currency is used with the
 * system-wide money factory, and is NOT automatically stored or read from the associated model data.  
 * I understand this is undesirable, and that the currency needs to be stored with the money value.  
 * Conversions would need to happen in the Money library (which I doubt works with the live exchange rates) 
 * at runtime to match the currency within the factory.
 * 
 * @todo Make something that can do money conversions with live exchange rates.
 */
class MoneyProperty extends BoundedProperty implements IMoneyProperty
{
  /**
   * Money Factory 
   * @var IMoneyFactory
   */
  private $factory;
  
  
  /**
   * Create a new ObjectProperty instance 
   * @param IObjectPropertyBuilder $builder Builder 
   */
  public function __construct( IBoundedPropertyBuilder $builder, IMoneyFactory $factory )
  {
    parent::__construct( $builder );
    $this->factory = $factory;
  }
  
  
  /**
   * Initialize the value property with some value.
   * This will be immediately overwritten by the initial call to reset(), but 
   * is useful for when value is some object type that must not be null. 
   * 
   * Returns null by default.
   * 
   * @return mixed value 
   * @throws Exception 
   */
  protected function initValue()
  {
    return $this->factory->getMoney( '0' );
  }  
  
  
  protected function validatePropertyValue( $value ) : void
  {
    if ( $this->getFlags()->hasVal( IPropertyFlags::USE_NULL ) && $value === null )
      return;
    
    if ( !( $value instanceof IMoney ))
      throw new ValidationException( sprintf( 'Value "%s" for property %s must be an instance of %s Got %s %s', (string)$value, $this->getName(), IMoney::class, gettype( $value ), ( is_object( $value )) ? ' of class ' . get_class( $value ) : '' ));
  }    
  
  
  public function getValueAsMoney() : IMoney
  {
    return $this->getValue();
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
    if ( !( $value instanceof IMoney ))
    {
      if ( empty( $value ))
        $value = '0';
      
      
      if ( is_string( $value ) && strpos( $value, '.' ) === false )
        $value .= '.00';
      
      return $this->factory->getMoney( $value );
      
    }
    else
      return $value;
  }
  
  
  /**
   * Test for empty money.
   * 
   * @param type $value
   * @return bool
   */
  protected function isPropertyEmpty( $value ) : bool
  {
    if ( $value instanceof IMoney )
    {
      if ( $this->defaultValue instanceof IMoney )
        return $value->equals( $this->getDefaultValue());
      else if ( is_scalar( $this->getDefaultValue()))
        return (string)$value->getAmount == (string)$this->getDefaultValue();
      else
        throw new \Exception( 'Money property default value must be an instance of IMoney or scalar.' );
    }
    
    return empty( $value ) || $value === $this->getDefaultValue();
  }  
}
