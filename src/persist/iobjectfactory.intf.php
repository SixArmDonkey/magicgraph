<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\persist;

use buffalokiwi\buffalotools\types\IBigSet;
use buffalokiwi\magicgraph\DBException;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertySet;


interface IObjectFactory
{
  /**
   * Create a new Model instance using the internal data mapper.
   * @param array $data Raw data to use 
   * @param bool $readOnly Set the produced model to read only 
   * @return IModel model instance 
   * @throws DBException For db errors 
   */
  public function create( array $data = [], bool $readOnly = false ) : IModel;
  
  
  /**
   * Retrieves a set containing the property names.
   * @return IBigSet set
   */
  public function createPropertyNameSet() : IBigSet;
  
  /**
   * Clones and returns the internal property set used to construct objects.
   * @return IPropertySet property set
   */
  public function createPropertySet() : IPropertySet;  
  
  
  /**
   * Adds an additional property config to this repo.
   * When models reference themselves, sometimes it's necessary for a property 
   * config to reference the repository (circular).  
   * 
   * Feels a bit like cheating to me...
   * 
   * @param type $config
   */
  public function addPropertyConfig( IPropertyConfig ...$config );  
  
  
  /**
   * Test if models created by this repo are of some type.  
   * @param string $clazz interface or class name 
   * @return bool
   */
  public function isA( string $clazz ) : bool;  
  
  
  /**
   * Specify columns to select.
   * @param string $names Zero or more names.  Leave names empty to select all columns.
   * @return IObjectFactory this 
   */
  public function select( string ...$names ) : IObjectFactory;  
}
