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
use buffalokiwi\magicgraph\persist\IRunnable;


/**
 * A property provider is used with a ModelFactory to add additional 
 * properties to a model in addition to the model's base configuration.
 * 
 * This interface makes it possible to attach more interactive property configurations
 * to a ServicableModel instance.
 */
interface IPropertyServiceProvider 
{
  /**
   * Get the property config for the main property set 
   * @return IPropertyConfig config 
   */
  public function getPropertyConfig() : IPropertyConfig;
}
