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

namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\property\IPropertyConfig;
use UI\Exception\InvalidArgumentException;


/**
 * Defines a factory for creating model instances using optional service providers.
 */
interface IModelFactory
{
  /**
   * Retrieve the service configuration data as a map of 
   * property name => IPropertyConfig 
   * @return array config data.
   */
  public function getServiceConfigMap() : array;
  
  
  /**
   * Retrieve a specific config instance by property name.
   * @param string $propertyName Property name
   * @return IPropertyConfig Service provider config
   * @throws InvalidArgumentException if the config is not listed by the specified property name.
   */
  public function getServiceConfig( string $propertyName ) : IPropertyConfig;
  

  /**
   * Create a  model and initialize any service provider backed properties
   * @param array $data
   * @param array $extraConfig Extra configuration data for creating other properties.
   * If this is included, the cached property set is not used.
   * @return IModel
   */
  public function create( array $data = [], array $extraConfig = [] ) : IModel;
}
