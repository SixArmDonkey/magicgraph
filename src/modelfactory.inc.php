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
use buffalokiwi\magicgraph\IModelFactory;
use buffalokiwi\magicgraph\IModelMapper;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyFactory;
use buffalokiwi\magicgraph\IPropertyServiceProvider;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\property\IPropertySetFactory;
use buffalokiwi\magicgraph\property\QuickPropertyConfig;
use Exception;
use InvalidArgumentException;

/**
 * Builds models with service providers.
 * While this is a factory, it should only be used with a single model type.
 * 
 * @todo This might be expensive to run.  This will need testing.
 * @todo figure out how to remove this.
 * @deprecated Replaced by ServiceableRepository and ServiceableModel
 */
class ModelFactory implements IModelFactory, IModelMapper
{
  /**
   * Property factory instance 
   * @var IPropertySetFactory 
   */
  private $setFactory;
  
  /**
   * Data mapper for IModel instances 
   * @var IModelMapper
   */
  private $modelFactory;
  
  /**
   * Property set used for all  models 
   * @var IPropertySet 
   */
  private $propertySet;
  
  
  /**
   * Map of property name => IPropertyConfig for any of the attached services 
   * @var array
   */
  private $serviceConfig;
  
  /**
   * IModel Property config 
   * @var IPropertyConfig[]
   */
  private $modelConfig;
  
  
  
  /**
   * Create a new Factory instance 
   * @param IPropertyConfig $modelConfig IModel configuration data
   * @param IPropertyFactory $factory Property factory for creating properties 
   * via model configuration arrays.
   * @param IModelMapper $modelFactory The data mapper used for creating new IModel instances 
   * @param IPropertySetFactory $factory The property set factory 
   * @param IPropertyServiceProvider $services Services used as a backing model for some property.
   */
  public function __construct( array $modelConfig, IModelMapper $modelFactory, IPropertySetFactory $setFactory, IPropertyServiceProvider ...$services )
  {
    $this->modelConfig = $modelConfig;
    $this->modelFactory = $modelFactory;
    $this->setFactory = $setFactory;
    
    //..Add the additional config from each service provider 
    $config = [];
    foreach( $services as $service )
    {
      $cfg = $service->getPropertyConfig();
      $config[] = $cfg;
      
      //..Well shit,  WTF is this.
      //..This disables caching of the config array.
      $q = new QuickPropertyConfig( $cfg->getConfig());
      
      $this->serviceConfig[$cfg->getPropertyName()] = $this->setFactory->createPropertySet( $q );
    }
    
    $this->serviceConfig = $config;
    
    $this->propertySet = $this->setFactory->createPropertySet( ...$modelConfig, ...$config );
  }
  
  
  /**
   * Retrieve the class or interface name this mapper works with.
   * @return string name 
   */
  public function getClass() : string
  {
    return $this->modelFactory->getClass();
  }


  /**
   * Test that the supplied model implements the interface or class name returned
   * by getClass().
   * @param IModel ...$models One or more models to test
   * @return void
   * @throws Exception if the model does not implement the interface.
   */
  public function test( IModel ...$models ) : void
  {
    $this->modelFactory->test( ...$models );
  }
  
  
  /**
   * Retrieve the service configuration data as a map of 
   * property name => IPropertyConfig 
   * @return array config data.
   */
  public function getServiceConfigMap() : array
  {
    return $this->serviceConfig;
  }
  
  
  /**
   * Retrieve a specific config instance by property name.
   * @param string $propertyName Property name
   * @return IPropertyConfig Service provider config
   * @throws InvalidArgumentException if the config is not listed by the specified property name.
   */
  public function getServiceConfig( string $propertyName ) : IPropertyConfig
  {
    if ( !isset( $this->serviceConfig[$propertyName] ))
      throw new InvalidArgumentException( $propertyName . ' is not a valid (listed) IPropertyServiceProvider property name for this model factory' );
    
    return $this->serviceConfig[$propertyName];
  }
  
  
  /**
   * Maps a map of raw data (attribute => value) to an IModel instance.
   * @param array $data data to map
   * 
   * Optionally accepts an IPropertySet instance.  Supplied if passed to the IModelMapper
   * constructor.
   * f( ?IPropertySet $propertySet, array $data ) : IModel
   * 
   * @param \buffalokiwi\magicgraph\IPropertySet|null $propertySet This is unused
   * within the model factory.
   * 
   * @return IModel model instance 
   * @throws Exception if the create model callback does not return an instance of IModel 
   */
  public function createAndMap( array $data, ?IPropertySet $propertySet = null ) : IModel
  {
    return $this->modelFactory->createAndMap( $data, clone $this->propertySet );
  }
  
  
  
  /**
   * Map some data to properties in some model.
   * Invalid properties are silently ignored.
   * @param IModel $model Model to push data into
   * @param array $data data to push
   */
  public function map( IModel $model, array $data ) : void  
  {
    $this->modelFactory->map( $model, $data );
  }
  
  
  /**
   * Create a  model and initialize any service provider backed properties
   * @param array $data
   * @param array $extraConfig Extra configuration data for creating other properties.
   * If this is included, the cached property set is not used.
   * @return IModel
   */
  public function create( array $data = [], array $extraConfig = [], \buffalokiwi\magicgraph\property\IPropertyConfig ...$baseConfig ) : IModel
  {
    return $this->modelFactory->createAndMap( $data, $this->getNewPropertySet( $extraConfig, ...$baseConfig ));
  }
  
  
  private function getNewPropertySet( array $extraConfig, \buffalokiwi\magicgraph\property\IPropertyConfig ...$baseConfig ) : IPropertySet
  {
    $extraConfig = $this->cleanExtraConfig( $extraConfig );

    $props = clone $this->propertySet;
    
    if ( !empty( $extraConfig ))
      $props->addPropertyConfig( new QuickPropertyConfig( $extraConfig ));
    
    if ( !empty( $baseConfig ))
      $props->addPropertyConfig( ...$baseConfig );
    
    return $props;
  }
  
  
  private function cleanExtraConfig( array $config )
  {
    foreach( array_keys( $config ) as $name )
    {
      if ( $this->propertySet->isMember((string)$name ))
        unset( $config[$name] );
    }
    
    return $config;
  }
}
