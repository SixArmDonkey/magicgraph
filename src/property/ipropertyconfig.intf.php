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

use buffalokiwi\magicgraph\IModel;

/**
 * Defines an object that can be used as configuration for the PropertyFactory
 */
interface IPropertyConfig 
{
  /**
   * Retrieve an array containing the definition of a property.
   * This will be used by IConfigMapper to set properties of a property builder, which is
   * used to create an instance of IProperty 
   * @return array config 
   */
  public function getConfig() : array;
  
  
  /**
   * Retrieve a list of property names defined via this config 
   * @return array names 
   */
  public function getPropertyNames() : array;  
  
  
  /**
   * Called via SaveableMappingObjectFactory, and happens as part of the 
   * beforeSave event.
   * @param \buffalokiwi\magicgraph\property\IModel $model Model being saved 
   * @return void
   */
  public function beforeSave( IModel $model ) : void;
  
  
  /**
   * Called via SaveableMappingObjectFactory, and happens as part of the 
   * afterSave event.
   * @param \buffalokiwi\magicgraph\property\IModel $model Model being saved 
   * @return void
   */
  public function afterSave( IModel $model ) : void;
  
  
  /**
   * After each property set is loaded via the property factory, this is called to allow some
   * property set to modify properties of another property set.
   * @param array $config Config array - Modify this directly.
   * @return void
   */
  public function modifyConfig( array &$config ) : void;
}
