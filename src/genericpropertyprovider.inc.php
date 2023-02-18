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

use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertySvcConfig;
use buffalokiwi\magicgraph\persist\IRunnable;
use Closure;
use InvalidArgumentException;


/**
 * A generic property provider.
 * Simply maps the supplied closures to methods defined in IModelPropertyProvider.
 */
class GenericPropertyProvider implements IModelPropertyProvider
{
  /**
   * init fucntion 
   * @var \Closure 
   */
  private $init;
  
  /**
   * getValue function 
   * @var \Closure 
   */
  private $getValue;
  
  /**
   * setValue function 
   * @var \Closure
   */
  private $setValue;
  
  /**
   * validate function 
   * @var \Closure
   */
  private $validate;
  
  /**
   * getConfig function
   * @var \Closure
   */
  private $getConfig;
  
  
  /**
   * 
   * @param Closure $init f( IModel ) : void
   * @param Closure $getValue f( IModel, mixed, array ) : mixed 
   * @param Closure $setValue f( IModel, mixed ) : void
   * @param Closure $validate f( IModel ) : void throws ValidationException
   * @param Closure $getConfig f() : IPropertySvcCfg 
   * @param Closure $onSave f( IModel $parent ) : IRunnable[] 
   */
  public function __construct( Closure $init, Closure $getValue, Closure $setValue, 
    Closure $validate, Closure $getConfig, Closure $onSave )
  {
    $this->init = $init;
    $this->getValue = $getValue;
    $this->setValue = $setValue;
    $this->validate = $validate;
    $this->getConfig = $getConfig;
    $this->onSave = $onSave;
  }
  
  
  /**
   * Initialize the  model.
   * @param IModel $model Model instance 
   * @return void
   */
  public function init( IModel $model ) : void
  {
    $f = $this->init;
    $f( $model );
  }
  

  /**
   * Retrieve the value of some property
   * @param string $property Property 
   * @return mixed value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function getValue( IModel $model, $value,  array $context = []  )
  {
    $f = $this->getValue;
    return $f( $model, $value, $context );
  }
  
  
  /**
   * Sets the value of some property
   * @param string $property Property to set
   * @param mixed $value property value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function setValue( IModel $model, $value ) : mixed
  {
    $f = $this->setValue;
    $f( $model, $value );
    return $value;
  }
  
  
  /**
   * Test to see if this model is valid prior to save()
   * @throws ValidationException
   */
  public function validate( IModel $model ) : void
  {
    $f = $this->validate;
    $f( $model );
  }
  

  /**
   * Retrieve the configuration used to build this provider
   * @return IPropertySvcConfig config 
   */  
  public function getModelServiceConfig() : IPropertySvcConfig
  {
    $f = $this->getConfig;
    return $f();
  }
  
  
  /**
   * Get the property config for the main property set 
   * @return IPropertyConfig config 
   */
  public function getPropertyConfig() : IPropertyConfig
  {
    return $this->getModelServiceConfig();
  }
  
  
  /**
   * Retrieve the save function used for saving stuff from the provider.
   * @param IModel $parent
   * @return IRunnable
   */
  public function getSaveFunction( IModel $parent ) : array
  {
    $f = $this->onSave;
    return $f( $parent );
  }
}
