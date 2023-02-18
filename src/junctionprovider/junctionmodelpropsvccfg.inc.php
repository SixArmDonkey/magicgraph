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

namespace buffalokiwi\magicgraph\junctionprovider;

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\property\BasePropSvcCfg;
use \Exception;


class JunctionModelPropSvcCfg extends BasePropSvcCfg
{
  /**
   * Parent model property name containing the primary key (id).
   * @var string name
   */
  private $parentIdProperty;
  
  
  /**
   * Parent model property containing the property name of the array, which 
   * will contain the target model instances.
   * @var 
   */
  private $parentArrayProperty;
  
  
  /**
   * Create a new Junction model property service configuration instance 
   * @param string $parentIdProperty Parent model property name containing 
   * the primary key (id).
   * @param string $parentArrayProperty Parent model property containing the 
   * property name of the array, which will contain the target model instances.
   */
  public function __construct( string $parentIdProperty, string $parentArrayProperty, \buffalokiwi\magicgraph\property\INamedPropertyBehavior ...$behavior )
  {    
    parent::__construct( ...$behavior );
    $this->parentIdProperty = $parentIdProperty;
    $this->parentArrayProperty = $parentArrayProperty;
  }
  
  
  /**
   * Retrieve the property name used to load the backing model for a property service.
   * In an alternate configuration, this property can be used as the backing array 
   * of model property name;
   * @return string name
   */
  public function getPropertyName() : string
  {
    return $this->parentIdProperty;
  }
  
  
  /**
   * Retrieve the property name used for the backing model for some property service.
   * In an alternate configuration, this function may return an empty string.
   * 
   * @return string name
   */
  public function getModelPropertyName() : string
  {
    return $this->parentArrayProperty;
  }
  
  
  /**
   * Retrieve a save function to be used with some transaction.
   * @param IModel $parent Model this provider is linked to 
   * @return \buffalokiwi\retailrack\address\IRunnable Something the saves data 
   * @throws Exception
   */
  /*
   * There is no need to override this as the save code is present in the junction provider itself.
  public function getSaveFunction( IModel $parent ) : array
  {
    return (new JunctionProviderSaveFunction())->getSaveFunction();
  }
   * 
   */
  
  
  /**
   * Does nothing in this implementation.
   * @return array config array 
   */
  protected function createConfig() : array
  {
    return [];  
  }
}
