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
 * A string property.
 * This can be validated with min/max length and/or a pattern
 */
class StringProperty extends BoundedProperty implements IStringProperty
{
  /**
   * Optional pattern to use when validating the string.
   * @var string
   */
  private $pattern;
  
  
  /**
   * Create a new StringProperty instance 
   * @param IStringPropertyBuilder $builder Builder
   */
  public function __construct( IStringPropertyBuilder $builder )
  {
    parent::__construct( $builder );
    $this->pattern = $builder->getPattern();
  }
  
  
  /**
   * Retrieve a regular expression used to validate the string property value
   * during calls to IProperty::validate().
   * @return string regex
   */
  public function getPattern() : string
  {
    return $this->pattern;
  }
  
  
  /**
   * Retrieve the property value as a string 
   * @return string
   */
  public function getValueAsString() : string
  {
    return (string)$this->getValue();
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
    
    if ( !is_string( $value ) || $value == null ) //..is_string returns true for null.
      throw new ValidationException( sprintf( 'Value for property %s must be a string.  Got %s', $this->getName(), gettype( $value )));
    else if ( $this->getMin() > -1 && strlen( $value ) < $this->getMin())
      throw new ValidationException( sprintf( 'Value for property %s must be a string with a character length greater than %d', $this->getName(), $this->getMin()));
    else if ( $this->getMax() > -1 && strlen( $value ) > $this->getMax())
      throw new ValidationException( sprintf( 'Value for property %s must be a string with a character length less than %d', $this->getName(), $this->getMax()));
    else if ( !empty( $this->pattern ) && !preg_match( $this->pattern, (string)$value ))
      throw new ValidationException( sprintf( 'Value for property %s must match the pattern %s', $this->getName(), $this->pattern ));
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
    if ( $value == null && !$this->getFlags()->hasVal( IPropertyFlags::USE_NULL ))
      $value = '';
    
    return $value;
  }  
  
  
  /**
   * Called when setting a property value.
   * Casts the value to a string.
   * @param mixed $value Value being set
   * @return string Value to set 
   */
  protected function setPropertyValue( $value )
  {
    return (string)$value;
  }
}
