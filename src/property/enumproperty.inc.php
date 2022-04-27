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


class EnumProperty extends ObjectProperty implements IEnumProperty
{
  private bool $inInit = false;
  
  /**
   * Create a new EnumProperty instance 
   * @param IObjectPropertyBuilder $builder Builder 
   */
  public function __construct( IObjectPropertyBuilder $builder ) 
  {
    parent::__construct( $builder );
  }  
  
  /**
   * Retrieve a clone of the stored enum value.
   * @return IEnum stored enum 
   */
  public function getValueAsEnum() : IEnum
  {    
    $val = $this->getValue();
    if ( $val == null )
      return $this->initValue();
    
    return $val;
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
    if ((( $value instanceof IEnum ) && !is_a( $value, $this->getClass())) //( get_class( $value ) != $this->getClass()))
      || ( !( $value instanceof IEnum ) && !$cur->isValid( $value )))
    {
      throw new ValidationException( sprintf( 'Value "%s" for property %s must be an instance of %s or a valid member or constant name of the defined IEnum instance.  Got %s %s', (string)$value, $this->getName(), $this->getClass(), gettype( $value ), ( is_object( $value )) ? ' of class ' . get_class( $value ) : '' ));
    }
  }
  
  
  /**
   * Called when setting a property value.
   * Override this in child classes to modify the value prior to committing it.
   * This is the default implementation which simply returns the supplied value.
   * @param mixed $value Value being set
   * @param mixed $curValue the current value 
   * @return mixed Value to set 
   */
  protected function setPropertyValue( $value, $curValue )
  {
    if ( is_array( $value ) && !empty( $value ))
      $value = array_values( $value )[0];
    
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
   * @throws \Exception 
   */
  protected function initValue() 
  {
    if ( $this->inInit )
      return;
    
    try {
      $this->inInit = true;
      $enum = parent::initValue();
    } finally {
      $this->inInit = false;
    }
    
    if ( $enum == null )
    {
      return $enum;
    }
    
    $dv = $this->getDefaultValue();
    if ( is_string( $dv ) && $enum->isValid( $dv ))
      $enum->setValue( $dv );
    
    /* @var $enum IEnum */
    $behavior = $this->getPropertyBehavior();
    
    $prop = $this;
    
    //..Sets the edited flag when the enum changes 
    //..The default value has already been set, so this should be fine.
    $enum->setOnChange( function() use($prop) {
      $prop->setEdited();
    });
    
    
    
    /* @var $b array */
    if ( $behavior != null )
    {
      $enum->setOnChange( function( IEnum $e, string $oldValue, string $newValue ) use($behavior,$prop) : void {
        foreach( $behavior as $b )
        {
          /* @var $b IPropertyBehavior */        
          $f = $b->getOnChangeCallback();
          if ( $f != null )
          {
            $f( $prop, $oldValue, $newValue );
          }
        }
      });
    }    
    
    return $enum;
  }
}
