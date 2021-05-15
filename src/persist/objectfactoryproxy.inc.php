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

namespace buffalokiwi\magicgraph\persist;

use buffalokiwi\buffalotools\types\IBigSet;
use buffalokiwi\magicgraph\DBException;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertySet;


/**
 * IObject factory decorator proxy 
 */
class ObjectFactoryProxy implements IObjectFactory
{
  /**
   * Factory
   * @var IObjectFactory
   */
  private $factory;
  
  
  public function __construct( IObjectFactory $factory )
  {
    $this->factory = $factory;
  }
  
  
  /**
   * Create a new Model instance using the internal data mapper.
   * @param array $data Raw data to use 
   * @param bool $readOnly Set the produced model to read only 
   * @return IModel model instance 
   * @throws DBException For db errors 
   */
  public function create( array $data = [], bool $readOnly = false ) : IModel
  {
    return $this->factory->create( $data, $readOnly );
  }
  
  
  public function createPropertyNameSet() : IBigSet
  {
    return $this->factory->createPropertyNameSet();
  }
  
  
  public function createPropertySet() : IPropertySet
  {
    return $this->factory->createPropertySet();
  }
  
  
  
  /**
   * Adds an additional property config to this repo.
   * When models reference themselves, sometimes it's necessary for a property 
   * config to reference the repository (circular).  
   * 
   * Feels a bit like cheating to me...
   * 
   * @param type $config
   */
  public function addPropertyConfig( IPropertyConfig ...$config )
  {
    $this->factory->addPropertyConfig( ...$config );
  }
  
  
  /**
   * Test if models created by this repo are of some type.  
   * @param string $clazz interface or class name 
   * @return bool
   */
  public function isA( string $clazz ) : bool
  {
    return $this->factory->isA( $clazz );
  }
}
