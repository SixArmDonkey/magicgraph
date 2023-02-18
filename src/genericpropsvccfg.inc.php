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

use buffalokiwi\magicgraph\property\BasePropSvcCfg;


/**
 * A generic property service configuration object.
 * Contains information about how a property service functions.
 * 
 * This is a way to inline basic configurations without creating more classes.
 */
class GenericPropSvcCfg extends GenericPropertyConfig implements property\IPropertySvcConfig
{
  /**
   * Id/link property name 
   * @var string
   */
  private $propertyName;
  
  /**
   * Model property name containing the generated model, etc.
   * @var string
   */
  private $modelPropertyName;
  
  
  /**
   * Create a new GenericPropertyConfig instance 
   * @param string $propertyName Property name containing the id of the model to load 
   * @param string $modelPropertyName The property name containing the model
   * @param array $config
   */
  public function __construct( string $propertyName, string $modelPropertyName, array $config = [] )
  {
    parent::__construct( $config );
    $this->propertyName = $propertyName;
    $this->modelPropertyName = $modelPropertyName;
  }
  
  
  /**
   * Retrieve the property name used to load the backing model for a property service.
   * In an alternate configuration, this property can be used as the backing array 
   * of model property name;
   * @return string name
   */
  public function getPropertyName() : string
  {
    return $this->propertyName;
  }
  
  
  /**
   * Retrieve the property name used for the backing model for some property service.
   * In an alternate configuration, this function may return an empty string.
   * 
   * @return string name
   */
  public function getModelPropertyName() : string
  {
    return $this->modelPropertyName;
  }
  
  
  /**
   * Retrieve a save function to be used with some transaction.
   * @param IModel $parent Model this provider is linked to 
   * @return \buffalokiwi\retailrack\address\IRunnable Something the saves data 
   * @throws Exception
   */
  public function getSaveFunction( IModel $parent ) : array
  {
    //..Do nothing 
    return [];
  }  
}
