<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */


declare( strict_types=1 );

namespace buffalokiwi\magicgraph\property;

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\persist\IRunnable;
use Exception;


/**
 * Property service configuration 
 * 
 * This should be used with relationship providers.
 * Simply adding properties can be accomplished with IPropertyConfig alone.
 */
interface IPropertySvcConfig extends IPropertyConfig
{
  /**
   * Retrieve the property name used to load the backing model for a property service.
   * In an alternate configuration, this property can be used as the backing array 
   * of model property name;
   * @return string name
   */
  public function getPropertyName() : string;
  
  
  /**
   * Retrieve the property name used for the backing model for some property service.
   * In an alternate configuration, this function may return an empty string.
   * 
   * @return string name
   */
  public function getModelPropertyName() : string;
  
  
  /**
   * Retrieve a save function to be used with some transaction.
   * @param IModel $parent Model this provider is linked to 
   * @return \buffalokiwi\retailrack\address\IRunnable Something the saves data 
   * @throws Exception
   */
  public function getSaveFunction( IModel $parent ) : array;
}
