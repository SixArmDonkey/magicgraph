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

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\IModelFactory;
use buffalokiwi\magicgraph\IModelMapper;
use buffalokiwi\magicgraph\IPropertyServiceProvider;
use buffalokiwi\magicgraph\ModelFactory;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyFactory;
use buffalokiwi\magicgraph\property\IPropertySetFactory;
use buffalokiwi\magicgraph\property\QuickPropertyConfig;
use Exception;


/**
 * Responsible for assembling IModel instances.
 * 
 * @todo This is most likely very expensive to run for many products.  
 * Optimize this, add caching, etc.
 * 
 * @todo This class no longer makes any sense as it is only a proxy to model factory.
 * @deprecated Should be safe for removal.
 */
class AttributeModelFactory extends ModelFactory 
{
  /**
   * Create a new Factory instance 
   * @param IPropertyConfig $modelConfig IModel configuration data
   * @param IPropertyFactory $factory Property factory for creating properties 
   * via model configuration arrays.
   * @param IModelMapper $modelFactory The data mapper used for creating new IModel instances 
   * @param IPropertySetFactory $factory The property set factory 
   * @param IPropertyServiceProvider $services Services used as a backing model for some property.
   */
  public function __construct( IAttributeRepo $repo, IPropertyConfig $modelConfig, 
    IModelMapper $modelFactory, IPropertySetFactory $setFactory, 
    IPropertyServiceProvider ...$services )
  {
    parent::__construct( $modelConfig, $modelFactory, $setFactory, ...$services );
  }
  
  
  /**
   * Create a  model and initialize any service provider backed properties
   * @param array $data Key/value pairs to add to model.
   * @param array $config Extra config data for attributes 
   * @return IModel
   * @throws Exception if an IModel was not generated
   * @see IModelFactory::create
   */
  public function createModel( array $data, array $config = [], IPropertyConfig ...$baseConfig ) : IModel
  {
    $model = $this->create( $data, $config, ...$baseConfig );
    if ( !( $model instanceof IModel ))
    {
      throw new Exception( sprintf( "%s instance did not generate an instance of %s.  Got %s.",
         AttributeModelFactory::class,
         IModel::class,
         ( is_object( $model )) ? get_class( $model ) : gettype( $model )));
    }
    
    return $model;
  }
  
  
  protected function createExtraPropertyConfig( array $extraConfig ) : IPropertyConfig 
  {
    return new QuickPropertyConfig( $extraConfig );
  }  
}
