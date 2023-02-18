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

namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\IPropertyServiceProvider;
use \InvalidArgumentException;


/**
 * A model that has service providers attached.
 */
interface IServiceableModel extends IModel
{
  /**
   * Retrieve a model property provider 
   * @param string $name
   * @return IModelPropertyProvider
   * @throws InvalidArgumentException
   */
  public function getProvider( string $name ) : IModelPropertyProvider ;
  
  
  /**
   * Retrieve a list of property service providers
   * @return IPropertyServiceProvider[]
   */
  public function getPropertyProviders() : array;
}
