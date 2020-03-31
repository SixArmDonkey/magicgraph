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


/**
 * A property that accepts float values 
 */
class FloatProperty extends BoundedProperty implements IFloatProperty
{
  /**
   * Create a new FloatProperty instance.
   * @param IBoundedPropertyBuilder $builder
   */
  public function __construct( IBoundedPropertyBuilder $builder )
  {
    parent::__construct( $builder );
  }
  
  
  /**
   * Retrieve the stored value as an float
   * @return float value 
   */
  public function getValueAsFloat() : float
  {
    return (float)$this->getValue();
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
    
    if ( filter_var( $value, FILTER_VALIDATE_FLOAT ) === false )
      throw new ValidationException( sprintf( 'Value for property %s must be an float.  Got %s', $this->getName(), gettype( $value )));
    else if ( $value < $this->getMin())
    {      
      throw new ValidationException( sprintf( 'Value %f for property %s must be an float greater than or equal to %f', $value, $this->getName(), $this->getMin()));
    }
    else if ( $value > $this->getMax())
      throw new ValidationException( sprintf( 'Value %f for property %s must be an float less than or equal to %f', $value, $this->getName(), $this->getMax()));
  }
  
  
  /**
   * Called when setting a property value.
   * Casts the value to a float.
   * @param mixed $value Value being set
   * @return float Value to set 
   */
  protected function setPropertyValue( $value )
  {
    return (float)$value;
  }
}
