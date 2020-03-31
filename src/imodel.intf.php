<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph;

use buffalokiwi\buffalotools\types\IBigSet;
use buffalokiwi\buffalotools\types\ISet;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertySet;
use InvalidArgumentException;
use IteratorAggregate;
use stdClass;


/**
 * Defines a model.  
 * @todo Write documentation
 */
interface IModel extends IteratorAggregate
{
  /**
   * Create a copy/clone of the model and all properties.
   * @param bool $copyIsSaveable If this is false, then the primary key flags
   * are removed from the copied model.  This will cause the repository save
   * method to fail when a save is attempted.
   * @return IModel Copied model 
   */
  public function createCopy( bool $copyIsSaveable = true ) : IModel;

  
  /**
   * Clears the internal edited flags for each property
   * @return void
   */
  public function clearEditFlags() : void;
  
  /**
   * A way to determine if this model OR if a property set is an instance of some interface.
   * This is used due to decorators.
   * @param string $interface Interface name
   * @return bool if this implements it 
   */
  public function instanceOf( string $interface ) : bool;
  
  
  /**
   * Detect if any properties have been edited in this model 
   * @param string $prop Property name.  If $prop is not empty then this would test that the supplied property name is not empty. 
   * Otherwise, this tests if any property was edited.
   * @return bool has edits
   */
  public function hasEdits( string $prop = '' ) : bool;  
  
  
  /**
   * Retrieve the property set used for this model.
   * 
   * This method FORMERLY returned a clone of the internal property set.
   * Currently, this returns the internal PropertySet.
   * 
   * @reutrn IPropertySet properties
   */
  public function getPropertySet() : IPropertySet;
  
  
  /**
   * Retrieve a set with the property name bits toggled on for properties
   * with the supplied flags.
   * @param string $flags Flags 
   * @return IBigSet names 
   */
  public function getPropertyNameSetByFlags( string ...$flags ) : IBigSet;  
  
  
  /**
   * Retrieve a property config instance attached to this model.
   * @param string $intf Interface of the config instance 
   * @return IPropertyConfig The config instance
   * @throws \Exception if The requested interface was not used to build this
   * model.
   */
  public function getPropertyConfig( string $intf ) : IPropertyConfig;
  
  
  /**
   * Retrieve property names as an IBigSet instance.
   * This will return a set containing all of the property names, and have 
   * zero members active.  This is available due to how expensive cloning 
   * the backing IPropertySet instance can be.  Use this for simple operations
   * such as determining if a property name is valid.
   * @return IBigSet property names 
   */
  public function getPropertyNameSet() : IBigSet;
  
  
  
  /**
   * Convert the model to a json representation.
   * @return string JSON object 
   */
  public function toObject( ?IBigSet $properties = null, bool $includeArrays = false, bool $includeModels = false ) : stdClass;
  
  
  /**
   * Convert this model to an array.
   * @param IPropertySet $properties Properties to include 
   */
  public function toArray( ?IBigSet $properties = null, bool $includeArrays = false, bool $includeModels = false ) : array;
  
  
  public function fromArray( array $data ) : void;  
  
  /**
   * Test if this model is equal to some other model
   * @param IModel $that model to compare
   * @return bool is equals
   */
  public function equals( IModel $that ) : bool;
  
  
  /**
   * Retrieve the value of some property
   * @param string $property Property 
   * @return mixed value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function getValue( string $property, array $context = [] );
  
  
  /**
   * Sets the value of some property
   * @param string $property Property to set
   * @param mixed $value property value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function setValue( string $property, $value ) : void;
  
  
  /**
   * Retrieve a list of modified properties 
   * @return ISet modified properties 
   */
  public function getModifiedProperties() : IBigSet;
  
  /**
   * Gets A propertyset containing properties for insert
   * @return IBigSet insert properties
   */
  public function getInsertProperties() : IBigSet;
  
  /**
   * Test to see if this model is valid prior to save()
   * @throws ValidationException
   */
  public function validate() : void;
  
  
  /**
   * If you want to validate each property and return a list of errors indexed by property name, this 
   * is the method to call.
   * 
   * Note: This simply calls validate() in a loop, catches exceptions and tosses some errors in a list.  
   * 
   * @return array [property name => message]
   */
  public function validateAll( bool $debugErrors = false ) : array;
  
  
  /**
   * Retrieve a unique hash for this object 
   * @return string hash
   */
  public function hash() : string;
  
  
  /**
   * Test that the model has all primary key values 
   * @return bool has values 
   */
  public function hasPrimaryKeyValues() : bool;  
}
