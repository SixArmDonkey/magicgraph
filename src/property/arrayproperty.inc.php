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
 * A property that can contain an array.
 * WARNING: Arrays may be cast to strings.
 * 
 * NOTE: Array properties are marked as edited when accessed
 */
class ArrayProperty extends AbstractProperty
{
  private $clazz;
  
  /**
   * Create a new ArrayProperty instance 
   * @param IObjectPropertyBuilder $builder Builder 
   */
  public function __construct( IObjectPropertyBuilder $builder )
  {
    parent::__construct( $builder );
    
    $this->clazz = $builder->getClass();    
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
        switch( $this->clazz )
        {
          case 'int':
            if ( !is_int( $v ))
              throw new ValidationException( sprintf( 'Array index "%s" for property %s must be an int.  Got %s', (string)$k, $this->getName(), gettype( $v )));

          case 'long':
            if ( !is_long( $v ))
              throw new ValidationException( sprintf( 'Array index "%s" for property %s must be a long.  Got %s', (string)$k, $this->getName(), gettype( $v )));
            
          case 'float':
            if ( !is_float( $v ))
              throw new ValidationException( sprintf( 'Array index "%s" for property %s must be a float.  Got %s', (string)$k, $this->getName(), gettype( $v )));

          case 'double':
            if ( !is_double( $v ))
              throw new ValidationException( sprintf( 'Array index "%s" for property %s must be a double.  Got %s', (string)$k, $this->getName(), gettype( $v )));
            
          case 'bool':            
            if ( !is_bool( $v ))
              throw new ValidationException( sprintf( 'Array index "%s" for property %s must be a bool.  Got %s', (string)$k, $this->getName(), gettype( $v )));

          case 'string':
            if ( !is_string( $v ))
              throw new ValidationException( sprintf( 'Array index "%s" for property %s must be a string.  Got %s', (string)$k, $this->getName(), gettype( $v )));
          break;

          default:
            if ( !is_a( $v, $this->clazz, false ) && !is_subclass_of( $v, $this->clazz, false ))
            {
              $cls = is_object( $v ) ? ' (' . get_class( $v ) . ')' : '';
              throw new ValidationException( sprintf( 'Array index "%s" for property %s must be an instance of %s.  Got %s', (string)$k, $this->getName(), $this->clazz, gettype( $v ) . $cls ));
            }
          break;            
        }
      }
    }
  }
  
  
  /**
   * Called when setting a property value.
   * This is called AFTER validate.
   * Override this in child classes to modify the value prior to committing it.
   * This is the default implementation which simply returns the supplied value.
   * @param mixed $value Value being set
   * @return mixed Value to set 
   */
  protected function setPropertyValue( $value )
  {
    if ( is_a( $value, $this->clazz ))
    {
      $cur = $this->getValue();
      $cur[] = $value;
      return $cur;
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
  protected function preparePropertyValue( $value )
  {
    //..Test for json.
    if ( is_string( $value ))
    {
      $decoded = json_decode( $value );
      if ( json_last_error() == JSON_ERROR_NONE && is_array( $decoded ))
      {
        return $decoded;
      }
    }
    
    if ( !is_array( $value ))
    {
      return [];
    }
    
    if ( is_array( $value ) && !empty( $value ))
    {
      //..There's some crazy thing that can cause extra spaces to be added 
      //..This can cause issues with other parts of the code.
      //..I figured trim that here so we don't have to 
      $newValue = [];
      foreach( $value as $k => $v )
      {
        if ( is_string( $k ))
          $k = trim( $k );
        
        if ( is_string( $v ))
          $v = trim( $v );
        
        $newValue[$k] = $v;
      }
      $value = $newValue;
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
  public function __toString()
  {
    return json_encode( $this->getValue());
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
    //..Since we can't know if anyone edited the underlying set, we set this to edited when accessed.
    $this->setEdited();
    /* @var $value ISet */    
    return $value;
  }    
}
