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
use Stringable;
use UnexpectedValueException;


/**
 * Defines a property that can be attached to some IModel.
 * This is essentially a variant property with getter/setter, validation, 
 * default value, and some callbacks to modify behavior.
 * 
 * The property flags are mostly used for the persistence layer, and are normally
 * accessed via IPropertySet.
 */
interface IProperty extends Stringable
{
  /**
   * Checks the internal edited flag.
   * This is set to true when setValue() is called 
   * @return bool is edited 
   */
  public function isEdited() : bool;
  
  
  /**
   * Sets the internal edited flag to false 
   * @return void
   * @deprecated Use hydrate() and reset()
   */
  public function clearEditFlag() : void;
  
  
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
   * Prevents overridable method calls within the constructor and potential inconsistent/incomplete program state.
   * 
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
  public function getDefaultValue() : mixed;
  
  
  /**
   * Bypasses readonly, no_insert and no_update restrictions.  This will always set the edited state to false.
   * @param mixed $value Value to write 
   * @return void
   * @throws ValidationException 
   * @throws UnexpectedValueException if internal edited state is true 
   */
  public function hydrate( $value ) : void;  
  
  
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
  public function getValue( array $context = [] ) : mixed;
  
  
  /**
   * Retrieve the tag value for this attribute 
   * @return string tag 
   */
  public function getTag() : string;  
  
  
  /**
   * Tests that the value is empty.
   * If no behavior is found (IS_EMPTY) then 
   * this simply does empty( value ) && value != '0000-00-00 00:00:00'.
   * If behavior is used, the above logic is ignored and the is_empty callback
   * determines empty state.
   * 
   * Override AbstractProperty::isPropertyEmpty() to customize isEmpty without the use of behaviors.
   * @return bool
   * @todo Reconsider. Empty is a generic term, and it may be better to test against known values via getValue()
   * @todo It makes no sense testing for an empty sql timestamp.  They differ between databases and should we consider the epoch to be empty?  Leave this for property implementations to decide.
   */
  public function isEmpty() : bool;
}
