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

use buffalokiwi\magicgraph\ValidationException;
use buffalokiwi\buffalotools\types\IEnum;
use buffalokiwi\buffalotools\types\RuntimeEnum;


class RuntimeEnumProperty extends AbstractProperty implements IEnumProperty
{
  /**
   * Enum config array/members 
   * @var array
   */
  private $members = [];
  
  /**
   * Create a new EnumProperty instance 
   * @param IObjectPropertyBuilder $builder Builder 
   */
  public function __construct( IPropertyBuilder $builder )
  {
    parent::__construct( $builder );
    
    $c = $builder->getConfig();
    if ( is_array( $c ))
    {
      //..do nothing
    }
    else if ( empty( $c ))
    {
      throw new \InvalidArgumentException( 'Missing enum configuration' );    
    }
    else if ( $c[0] == '[' )
    {
      $c = json_decode( $c );
      if ( json_last_error() != JSON_ERROR_NONE || !is_array( $c ))
        throw new \InvalidArgumentException( 'Cannot create runtime enum.  Invalid configuration data' );
      
    }
    else if ( is_string( $c ))
    {
      $c = explode( ',', $c );
    }
    
    if ( !is_array( $c ))
      throw new \InvalidArgumentException( 'Cannot create runtime enum.  Configuration data must be an array.' );
    
    $this->members = $c;
  }  
  
  /**
   * Retrieve a clone of the stored enum value.
   * @return IEnum stored enum 
   */
  public function getValueAsEnum() : IEnum
  {
    $val = $this->getValue();
    if ( $val == null )
      $val = $this->initValue();
    
    return $this->getValue();
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

    $cur = $this->getValueAsEnum();
    if ((( $value instanceof IEnum ) && !$cur->isValid( $value->value())) //( get_class( $value ) != $this->getClass()))
      || ( !( $value instanceof IEnum ) && !$cur->isValid( $value )))
    {
      throw new ValidationException( sprintf( 'Value "%s" for property %s must be an instance of %s or a valid member or constant name of the defined IEnum instance.  Got %s %s', (string)$value, $this->getName(), __CLASS__, gettype( $value ), ( is_object( $value )) ? ' of class ' . get_class( $value ) : '' ));
    }    
  }
  
  
  /**
   * Called when setting a property value.
   * Override this in child classes to modify the value prior to committing it.
   * This is the default implementation which simply returns the supplied value.
   * @param mixed $value Value being set
   * @return mixed Value to set 
   */
  protected function setPropertyValue( $value )
  {
    $this->getValueAsEnum()->setValue(( $value instanceof IEnum ) ? $value->value() : $value );
    return $this->getValueAsEnum();
  }
  
  
  /**
   * Called when getting a property value.
   * Override this in child classes to modify the value prior to returning it from the getValue() method.
   * This is the default implementation which simply returns the supplied value.
   * @param mixed $value Value being returned
   * @return mixed Value to return 
   */
  protected function getPropertyValue( $value )
  {
    /* @var $value IEnum */    
    return $value;
  }  
  
  
  /**
   * Initialize the value property with some value.
   * This will be immediately overwritten by the initial call to reset(), but 
   * is useful for when value is some object type that must not be null. 
   * 
   * Returns null by default.
   * 
   * @return mixed value 
   */
  protected function initValue()
  {
    return new RuntimeEnum( $this->members );    
  }    
}
