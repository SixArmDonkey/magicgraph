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

use buffalokiwi\buffalotools\types\IBigSet;
use Closure;
use Exception;
use InvalidArgumentException;


/**
 * Defines a set of properties that are used within some model.
 * ie: table column definitions 
 * 
 * Note: Property sets MUST be clonable.  Be sure to implement __clone() and
 * clone instances of each property in this set.
 * 
 * @todo Write documentation
 */
interface IPropertySet extends IBigSet
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
   * An array of closures for model validation
   * f( IModel )
   * @return array Closures
   */
  public function getModelValidationArray() : array;
  
  
  /**
   * Used to add properties to this property set.
   * This MUST be marked as final in the implementing class if it is called 
   * by the constructor.
   * @param IPropertyConfig ...$config config to add
   * @throws InvalidArgumentException 
   */
  public function addPropertyConfig( IPropertyConfig ...$config );
  
  
  /**
   * Retrieve the primary key property names.
   * @return string[] names
   */
  public function getPrimaryKeyNames() : array;
  
  
  /**
   * Retrieve an array of keys flagged as primary 
   * @return array IProperty[] primary keys
   */
  public function getPrimaryKeys() : array;
    
  
  /**
   * Retrieve the property that represents the primary key.
   * If multiple primary keys are used, this returns the first one in the list.
   * Use getPrimaryKeys() if using compound primary keys.
   * @return IProperty property
   */
  public function getPrimaryKey() : IProperty;
  
  
  /**
   * Retrieve a list of all the properties 
   * @param string ...$name Optional list of properties to return by name 
   * @return IProperty[] properties
   */
  public function getProperties( string ...$name ) : array;
  
  
  /**
   * Retrieve a property by name 
   * @param string $name name
   * @return IProperty property 
   */
  public function getProperty( string $name ) : IProperty;
  
  
  /**
   * Retrieve a list of properties by flag.
   * @param IPropertyFlags $flags Flags to test 
   * @return IProperty[] properties
   */
  public function findProperty( IPropertyFlags $flags ) : array;
  
  
  /**
   * Retrieve a list of properties by data type.
   * @param IPropertyType $type Type
   * @return IProperty[] properties
   */
  public function getPropertiesByType( IPropertyType $type ) : array;
  
  /**
   * Retrieve the property configuration 
   * @param string $interface The interface name of the desired property config.
   * If the internal instance does not implement $interface, then an exception is thrown.
   * @return IPropertyConfig
   * @throws Exception if config does not implement $interface
   */
  public function getPropertyConfig( string $interface ) : IPropertyConfig;
  
  
  /**
   * Determine if this property set was built using a specific interface.
   * @param string $interface Interface to test.
   * @return bool Implements 
   */
  public function containsConfig( string $interface ) : bool;

  
  /**
   * Retrieve the product property configuration array 
   * @return array
   */
  public function getPropertyConfigArray() : array;
  
  
  /**
   * Retrieve a list of all of the currently enabled property config instances 
   * attached to this property set.
   * @return array IPropertyConfig[] config instances 
   */
  public function getConfigObjects() : array;  
  
  
  /**
   * Retrieve a list of properties by flag.
   * @param string $flags flags 
   * @return array properties 
   */
  public function getPropertiesByFlag( string ...$flags ) : array;  
  
  
  /**
   * Retrieve a list of property names that have been added via addPropertyConfig().
   * @return array names 
   */
  public function getNewMembers() : array;  
  
  
  /**
   * Callback when a member is added 
   * @param Closure $callback function 
   * @return void
   */
  public function setOnAddMember( Closure $callback ) : void;  
  
  
  /**
   * Retrieve a multi-dimensional array, which defines all properties in this object.
   * @return array schema
   */
  public function getSchema() : array;  
  
  
  /**
   * Adds a property to the property set.  For a more robust solution, please use the preferred method: addPropertyConfig().
   * @param IProperty $prop Property to add
   * @return void
   */
  public function addProperty( IProperty $prop ) : void;  
}
