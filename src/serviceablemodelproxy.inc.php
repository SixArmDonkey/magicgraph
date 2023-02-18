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


class ServiceableModelProxy extends ProxyModel implements IServiceableModel
{
  public function __construct( IServiceableModel $model )
  {
    parent::__construct( $model );
  }
  
  
  /**
   * Retrieve a list of property service providers
   * @return IPropertyServiceProvider[]
   */
  public function getPropertyProviders() : array
  {
    return $this->getModel()->getPropertyProviders();
  }
  
  
  /**
   * Retrieves the model
   * @return IModel model 
   */
  protected function getModel() : IServiceableModel
  {
    return parent::getModel();
  }  
}
