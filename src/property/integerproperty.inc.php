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
 * A property that accepts integer values 
 */
class IntegerProperty extends BoundedProperty implements IIntegerProperty
{
  /**
   * Create a new IntegerProperty instance.
   * The range for accepted integer valus is between the defined range in 
   * the IBoundedPropertyBuilder instance inclusive.
   * @param IBoundedPropertyBuilder $builder
   */
  public function __construct( IBoundedPropertyBuilder $builder )
  {
    parent::__construct( $builder );
  }
  
  
  /**
   * Retrieve the stored value as an integer
   * @return int value 
   */
  public function getValueAsInt() : int
  {
    return (int)$this->getValue();
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
    
    if ( filter_var( $value, FILTER_VALIDATE_INT ) === false )
      throw new ValidationException( sprintf( 'Value %s for property %s must be an integer.  Got %s', $value, $this->getName(), gettype( $value )));
    else if ( $value < $this->getMin())
      throw new ValidationException( sprintf( 'Value %d for property %s must be an integer greater than %d', $value, $this->getName(), $this->getMin()));
    else if ( $value > $this->getMax())
      throw new ValidationException( sprintf( 'Value %d for property %s must be an integer less than %d', $value, $this->getName(), $this->getMax()));
  }
  
  
  /**
   * Called when setting a property value.
   * Casts the value to an integer.
   * @param mixed $value Value being set
   * @param mixed $curValue the current value 
   * @return int Value to set 
   */
  protected function setPropertyValue( $value, $curValue )
  {
    return (int)$value;
  }
  
  
  protected function isPropertyEmpty( $value ) : bool
  {
   
    if ( is_string( $value ) && ctype_digit((string)$value ))
      return empty((int)$value);
    
    return parent::isPropertyEmpty( $value );
      
    
  }
}
