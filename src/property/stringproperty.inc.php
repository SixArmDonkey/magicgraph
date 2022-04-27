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
  private string $pattern;
  private bool $hasPattern;
  private bool $hasMin;
  private bool $hasMax;
  private bool $hasValidation;
  private int $min;
  private int $max;
  
  /**
   * Create a new StringProperty instance 
   * @param IStringPropertyBuilder $builder Builder
   */
  public function __construct( IStringPropertyBuilder $builder )
  {
    parent::__construct( $builder );
    $this->pattern = $builder->getPattern();
    $this->hasPattern = !empty( $this->pattern );
    $this->hasMin = $this->getMin() > -1;
    $this->hasMax = $this->getMax() > -1;
    $this->min = ( $this->getMin() != PHP_FLOAT_MIN ) ? (int)$this->getMin() : PHP_FLOAT_MIN;
    $this->max = ( $this->getMax() != PHP_FLOAT_MAX ) ? (int)$this->getMax() : PHP_FLOAT_MAX;
    $this->hasValidation = $this->hasMin || $this->hasMax || $this->hasPattern;
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
    if ( $this->isUseNull() && $value === null )
      return; //..This is ok        
    else if ( !is_string( $value )) //..is_string returns true for null.
      throw new ValidationException( sprintf( 'Value for property %s must be a string.  Got %s', $this->getName(), ( $value == null ) ? 'null' : gettype( $value )));
    
    //..The profiler said that this could improve things a bit considering this validate method can be called thousands of times.
    if ( $this->hasValidation )
    {
      if ( $value === null )
        $value = '';

      $len = ( $this->hasMin || $this->hasMax ) ? strlen( $value ) : 0;

      if ( $this->hasMin && $len < $this->min )
        throw new ValidationException( sprintf( 'Value for property %s must be a string with a character length greater than %d', $this->getName(), $this->getMin()));
      else if ( $this->hasMax && $len > $this->max )
        throw new ValidationException( sprintf( 'Value for property %s must be a string with a character length less than %d', $this->getName(), $this->getMax()));
      else if ( $this->hasPattern && !preg_match( $this->pattern, $value ))
        throw new ValidationException( sprintf( 'Value for property %s must match the pattern %s', $this->getName(), $this->pattern ));
    }
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
    if ( $value == null && !$this->isUseNull())
      $value = '';
    
    return $value;
  }  
  
  
  /**
   * Called when setting a property value.
   * Casts the value to a string.
   * @param mixed $value Value being set
   * @param mixed $curValue the current value 
   * @return string Value to set 
   */
  protected function setPropertyValue( $value, $curValue )
  {
    return (string)$value;
  }
}
