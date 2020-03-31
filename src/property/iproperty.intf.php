<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\property;

use buffalokiwi\magicgraph\ValidationException;
use InvalidArgumentException;


/**
 * Defines a property that can be attached to some IModel.
 * This is essentially a variant property with getter/setter, validation, 
 * default value, and some callbacks to modify behavior.
 * 
 * The property flags are mostly used for the persistence layer, and are normally
 * accessed via IPropertySet.
 */
interface IProperty
{
  /**
   * Sets the property set to read only.
   * This is a method because the model still needs to be written to when 
   * creating instances populated from persistent storage.  The idea is for the
   * mapping object factory to call this method after filling the model, but 
   * before returning it.
   */
  public function setReadOnly() : void;
  
  
  /**
   * Retrieve the prefix used to identify properties of a child model.
   * ie: 
   * parent model has property foo_bar 
   * child model has property bar
   * 
   * Prefix = foo_ 
   * 
   * parent->foo_bar = 'baz';
   * 
   * sets bar equal to 'baz' in the child model.
   * 
   * @return string
   */
  public function getPrefix() : string;
  
  /**
   * Retrieve random config data
   * @return mixed
   */
  public function getConfig();
  
  /**
   * Retrieve the optionally set unique identifier for this property.
   * This may be zero if unassigned.
   * @return int optional id 
   */
  public function getId() : int;
  
  /**
   * Retrieve the attribute caption/label.
   * If no caption is listed, this returns name.
   * @return string
   */
  public function getCaption() : string;  
  
  /**
   * Initialize and/or reset the property state to default.
   * First checks for an init callback attached to IPropertyBehavior.  If it exists, then
   * the result of that callback is used as the default value, otherwise the default value
   * specified during object construction is used.
   * 
   * Second, calls setValue() with the derived default value.
   * 
   * This allows the default value to go through validation.
   * 
   * I really don't like this, but it makes object construction more clear.
   * @return IProperty this - Makes object initialization a single statement.
   * @throws InvalidArgumentException
   * @throws ValidationException 
   */
  public function reset() : IProperty;
  
  
  /**
   * Retrieve the property name
   * @return string name 
   */
  public function getName() : string;
  
  
  /**
   * Retrieve the property type
   * @return IPropertyType type
   */
  public function getType() : IPropertyType;
  
  
  /**
   * Retrieve the set of flags for this property
   * @return IPropertyFlags flags
   */
  public function getFlags() : IPropertyFlags;
  
  
  /**
   * Retrieve the object containing callbacks that can modify some behavior
   * of the property.
   * @return IPropertyBehavior[]
   */
  public function getPropertyBehavior() : array;
  

  /**
   * Test to see if some value is valid 
   * @param type $value
   * @throws ValidationException 
   */
  public function validate( $value ) : void;    
    
  /**
   * Retrieve the default value for some property 
   * @return mixed Default value 
   */
  public function getDefaultValue();
  
  
  /**
   * Sets the property value 
   * @param mixed $value Value to set
   * @return void
   * @throws ValidationException 
   */
  public function setValue( $value ) : void;
  
  
  /**
   * Retrieve the stored property value 
   * @return mixed value 
   */
  public function getValue( array $context = [] );    
  
  
  /**
   * All properties must be able to be cast to a string
   * @return string property value 
   */
  public function __toString();
  
  
  /**
   * Retrieve the tag value for this attribute 
   * @return string tag 
   */
  public function getTag() : string;  
}
