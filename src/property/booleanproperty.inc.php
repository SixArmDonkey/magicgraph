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
use function ctype_digit;


/**
 * A boolean property.
 * 
 * When setting the value, you may use a boolean or one of the following strings:
 * 
 * true, yes, y, 1, on
 * false, no, n, 0, off
 * 
 */
class BooleanProperty extends AbstractProperty implements IBooleanProperty
{
  /**
   * @param IPropertyBuilder $builder Property configuration 
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
  
  
  public function __toString() : string
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
    
    try {
      $this->strToBool( $value );
    } catch( \InvalidArgumentException $e ) {
      throw new ValidationException( sprintf( 'Value for property %s must be a boolean.  Got %s', $this->getName(), gettype( $value )));
    }      
  }
  
  
  /**
   * Called when setting a property value.
   * Casts the value to a boolean.
   * @param mixed $value Value being set
   * @param mixed $curValue the current value 
   * @return bool Value to set 
   */
  protected function setPropertyValue( $value, $curValue ) : ?bool
  {    
    if ( $this->isUseNull() && $value === null )
      return $value;
    
    return filter_var( $this->strToBool( $value ), FILTER_VALIDATE_BOOLEAN );
  }  
  
  
  /**
   * Turns a string representation of a boolean into a boolean.
   * This accepts:
   * true, yes, y, 1, (bool)true
   * false, no, n, 0, (bool)false
   * @param string $s Boolean value as a string or a bool
   * @return boolean Boolean value for $s
   * @throws InvalidArgumentException if $s cannot be converted
   * @static
   */
  protected final function strToBool( $s ) : bool
  {
    if ( is_bool( $s ))
      return $s;
    else if ( ctype_digit((string)$s ))
      return ( $s == 1 );
    else if ( in_array( strtolower( $s ), array( 'true', 'yes', 'y', '1', 'on' )))
      return true;
    else if ( in_array( strtolower( $s ), array( 'false', 'no', 'n', '0', 'off' )))
      return false;
    else
      throw new InvalidArgumentException( "StrToBool conversion failure for value " . $s );
  }  
}
