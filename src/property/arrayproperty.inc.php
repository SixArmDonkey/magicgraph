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

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\ValidationException;
use function \json_decode;
use function \json_encode;


/**
 * A strictly typed array property
 * 
 * Array properties are marked as edited when accessed
 * 
 * @todo This needs to be tested with IModel and edits
 * @todo This needs to be tested with scalar types 
 * @todo This needs to be tested with relationship providers and lazy/eager loading 
 */
class ArrayProperty extends AbstractProperty
{
  private $clazz;
  private $initialValue;
  
  
  /**
   * Create a new ArrayProperty instance 
   * @param IObjectPropertyBuilder $builder Builder 
   */
  public function __construct( IObjectPropertyBuilder $builder )
  {
    parent::__construct( $builder );
    
    $this->clazz = $builder->getClass();    
    $this->initialValue = $this->getDefaultValue();
  }
  
  
  public function isEdited(): bool
  {
    
    if ( !$this->isRetrieved())
      return parent::isEdited();
    
    
    foreach( $this->getValue() as $v )
    {
      if (( $v instanceof IModel ) && $v->hasEdits())
      {
        return true;        
      }
    }
    
    return parent::isEdited();
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
    
    if ( !is_array( $value ))
      throw new ValidationException( sprintf( 'Value "%s" for property %s must be an array.  Got %s', (string)$value, $this->getName(), gettype( $value )));
    
    if ( !empty( $this->clazz ))
    {
      foreach( $value as $k => &$v )
      {
        $this->validateEntry( $k, $v );
      }
    }
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
  protected function initValue() : mixed
  {
    if ( $this->isUseNull())
      return null;
    else
      return [];
  }  
  
  
  /**
   * Called when setting a property value.
   * This is called AFTER validate.
   * Override this in child classes to modify the value prior to committing it.
   * This is the default implementation which simply returns the supplied value.
   * @param mixed $value Value being set
   * @param mixed $curValue the current value 
   * @return mixed Value to set 
   */
  protected function setPropertyValue( $value, $curValue ) : ?array
  {
    if ( is_a( $value, $this->clazz ))
    {
      $curValue[] = $value;
      return $curValue;
    }
    
    return $value;
  }
  
  
  /**
   * Tests for a string and attempts to decode it as if it were json.
   * If that passes, and the result is an array, that value is returned for 
   * validation.  Otherwise, the passed value is returned unchanged.
   * 
   * Called after the behavior callback setter, and BEFORE validate.
   * Override this to prepare data for validation.
   * 
   * DO NOT USE THIS TO COMMIT DATA.
   * 
   * @param mixed $value Value being set.
   * @return mixed value to validate and set
   */
  protected function preparePropertyValue( $value ) : ?array
  {
    if ( !is_array( $value ))
    {
      if ( $value === null && $this->isUseNull())
        return null;
      else 
      {
        throw new ValidationException( 'Property ' . $this->getName() 
          . ': When USE_NULL is not set, values must be string or array' );
      }
    }
    
    return $value;
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
  public function __toString() : string
  {
    return json_encode( $this->getValue());
  }
  
  
  /**
   * Called when getting a property value.
   * Override this in child classes to modify the value prior to returning it from the getValue() method.
   * This is the default implementation which simply returns the supplied value.
   * @param mixed $value Value being returned
   * @param array $context Context array
   * @return mixed Value to return 
   */
  protected function getPropertyValue( $value, array $context = [] ) : ?array
  {    
    return $value;
  }
  
  
  private function validateEntry( int|string $k, mixed &$v ) : void
  {
    if ( !$this->isValidValue( $v ))
    {
      $cls = is_object( $v ) ? ' (' . get_class( $v ) . ')' : '';
      throw new ValidationException( sprintf( 'Array index "%s" for property %s must be of type %s.  Got %s', (string)$k, $this->getName(), $this->clazz, gettype( $v ) . $cls ));
    }
  }  
  
  
  private function isValidValue( mixed &$v ) : bool
  {
    switch( $this->clazz )
    {
      case 'int':
        if ( !is_int( $v ))
          return false;

      case 'long':
        if ( !is_long( $v ))
          return false;

      case 'float':
        if ( !is_float( $v ))
          return false;

      case 'double':
        if ( !is_double( $v ))
          return false;

      case 'bool':            
        if ( !is_bool( $v ))
          return false;

      case 'string':
        if ( !is_string( $v ))
          return false;
      break;

      default:
        if ( !is_a( $v, $this->clazz, false ) && !is_subclass_of( $v, $this->clazz, false ))
          return false;
      break;            
    } 
  }
}
