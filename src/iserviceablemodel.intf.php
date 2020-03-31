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

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\IPropertyServiceProvider;


/**
 * A model that has service providers attached.
 */
interface IServiceableModel extends IModel
{
  /**
   * Retrieve a list of property service providers
   * @return IPropertyServiceProvider[]
   */
  public function getPropertyProviders() : array;
}
