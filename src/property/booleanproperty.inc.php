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
use InvalidArgumentException;
use UI\Exception\InvalidArgumentException as InvalidArgumentException2;
use function ctype_digit;


/**
 * A boolean property 
 */
class BooleanProperty extends AbstractProperty implements IBooleanProperty
{
  /**
   * Create a new Property instance 
   */
  public function __construct( IPropertyBuilder $builder )
  {
    parent::__construct( $builder );
  }
  
  
  /**
   * Retrieve the stored value as a boolean.
   * @return bool boolean 
   */
  public function getValueAsBoolean() : bool
  {
    return (bool)$this->getValue();
  }
  
  
  public function __toString()
  {
    return ( $this->getValue()) ? '1' : '0';
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
    
    $value = $this->strToBool( $value );
    if ( !is_bool( $value ) || ( $value !== true && $value !== false ))
      throw new ValidationException( sprintf( 'Value for property %s must be a boolean.  Got %s', $this->getName(), gettype( $value )));
  }    
  
  
  /**
   * Called when setting a property value.
   * Casts the value to a boolean.
   * @param mixed $value Value being set
   * @return bool Value to set 
   */
  protected function setPropertyValue( $value )
  {    
    $value = $this->strToBool( $value );
    return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
  }  
  
  
  /**
   * Turns a string representation of a boolean into a boolean.
   * This accepts:
   * true, yes, y, 1, (bool)true
   * false, no, n, 0, (bool)false
   * @param string $s Boolean value as a string or a bool
   * @return boolean Boolean value for $s
   * @throws InvalidArgumentException2 if $s cannot be converted
   * @static
   */
  protected function strToBool( $s )
  {
    if ( empty( $s ))
      return false;
    else if ( is_bool( $s ))
      return $s;
    else if ( ctype_digit( (string)$s ))
      return ( $s == 1 );
    else if ( in_array( strtolower( $s ), array( 'true', 'yes', 'y', '1', 'on' )))
      return true;
    else if ( in_array( strtolower( $s ), array( 'false', 'no', 'n', '0', 'off' )))
      return false;
    else
      throw new InvalidArgumentException( "StrToBool conversion failure for value " . $s );
  }  
}
