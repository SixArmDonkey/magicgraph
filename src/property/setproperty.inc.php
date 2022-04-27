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
use buffalokiwi\buffalotools\types\ISet;
use InvalidArgumentException;


/**
 * A property backed by an ISet instance
 * 
 * NOTE: Set properties are marked as edited when accessed
 */
class SetProperty extends ObjectProperty implements ISetProperty
{
  /**
   * Create a new SetProperty instance 
   * @param IObjectPropertyBuilder $builder Builder 
   */
  public function __construct( IObjectPropertyBuilder $builder )
  {
    parent::__construct( $builder );
  }  
  
  /**
   * Retrieve a clone of the stored set value.
   * @return ISet stored set 
   */
  public function getValueAsSet() : ISet
  {    
    if ( !( $this->getValue() instanceof ISet ))
    {
      //..There's a problem and someone created a getter or something that 
      //..is overriding the internal set value.
      //..First try to see if this is a member of the set
      $c = $this->initValue();
      $val = (string)$this->getValue();
      
      if ( !empty( $val ) && !$c->isMember( $val ))
        throw new \InvalidArgumentException( '"' . $val . '" is not a member of ' . get_class( $c ));
      
      return $c;
    }
    else
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

    if ( is_object( $value ))
      $value = (string)$value;
    
    if ( !is_array( $value ))
      $value = explode( ',', $value);
    
    if ( !is_array( $value ))
      $value = [$value];
    
    $cur = $this->getValueAsSet();
    foreach( $value as $v )
    {
      if ( !empty( $v ) 
        && ((( $v instanceof ISet ) && !is_a( $v, $this->getClass())) 
          || ( !( $v instanceof ISet ) && !$cur->isMember( $v ))))
      {
        throw new ValidationException( sprintf( 'Value for property %s must be an instance of %s or a valid member or constant name of the defined ISet instance.  Got %s of class %s', $this->getName(), $this->getClass(), gettype( $v ), ( is_object( $v )) ? get_class( $v ) : '(not an object)' ));
      }
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
    /* @var $value ISet */
    $set = $this->getValueAsSet();
    $set->clear();
    
    //..Empty sets are ok.
    if ( empty( $value ))
      return $set;
    
    if ( $value instanceof ISet )
    {
      $set->add( ...$value->getActiveMembers());
    }
    else if ( is_array( $value ))
    {
      $set->add( ...$value );  
    }
    else if ( is_string( $value ))
      $set->add( ...explode( ',', $value ));
    else
      throw new InvalidArgumentException( 'Invalid set value' );
    
    
    return $set;
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
