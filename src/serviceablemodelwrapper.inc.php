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

use buffalokiwi\buffalotools\types\IBigSet;
use buffalokiwi\magicgraph\IServiceableModel;
use buffalokiwi\magicgraph\property\IPropertySet;
use InvalidArgumentException;


/**
 * An IModel wrapper that can interact with service providers to add functionality.
 */
class ServiceableModelWrapper extends ProxyModel implements IServiceableModel
{
  /**
   * Model-backed Service providers.
   * @var IModelPropertyProvider[] 
   */
  private $providers;
  


  
  /**
   * Create a model wrapper that can interact with service providers.
   * @param IModel $model Model instance 
   * @param IModelPropertyProvider $providers A list of service providers for this model
   */
  public function __construct( IModel $model, IModelPropertyProvider ...$providers )
  {
    parent::__construct( $model );
    
    foreach( $providers as $p )
    {
      if ( empty( $p->getModelServiceConfig()->getModelPropertyName()))
        throw new \InvalidArgumentException( 'Model property name must not be empty' );
      else if ( empty( $p->getModelServiceConfig()->getPropertyName()))
        throw new \InvalidArgumentException( 'Property name must not be empty' );
      
      /* @var $p IPropertyServiceProvider */
      $this->providers[$p->getModelServiceConfig()->getModelPropertyName()] = $p;
      
      //..Maybe try to initialize the service provider part here?
      //..This seems fine?
      $p->init( $model );
    }
  }

  
  /**
   * Generic getter.   
   * Alias of getValue()
   * @param string $p Property name
   * @return mixed Property value
   * @see DefaultModel::getValue()
   */
  public function __get( $p )
  {
    return $this->getValue( $p );
  }
  
  
  public function __set( $p, $v )
  {
    $this->setValue( $p, $v );
  }
  
  
  /**
   * Retrieve a list of property service providers
   * @return IPropertyServiceProvider[]
   */
  public function getPropertyProviders() : array
  {
    return array_values( $this->providers );
  }
  
  
  /**
   * Retrieve the value of some property
   * @param string $property Property 
   * @return mixed value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function getValue( string $property, array $context = []  )
  {
    $value = parent::getValue( $property, $context );
   
    if ( isset( $this->providers[$property] ))
    {
      $p = $this->providers[$property];
      /* @var $p IPropertyServiceProvider */
      $value = $p->getValue( $this, $value, $context );
    }    
   
    return $value;
  }
  
  
  /**
   * Sets the value of some property
   * @param string $property Property to set
   * @param mixed $value property value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function setValue( string $property, $value ) : void
  { 
    if ( isset( $this->providers[$property] ))
    {
      $p = $this->providers[$property];
      /* @var $p IPropertyServiceProvider */
      $p->setValue( $this, $value );
    }
    
    parent::setValue( $property, $value );
  }
  
  
  /**
   * Test to see if this model is valid prior to save()
   * @throws ValidationException
   */
  public function validate() : void
  {
    foreach( $this->providers as $p )
    {
      /* @var $p IPropertyServiceProvider */
      $p->validate( $this );
    }
    
    parent::validate();
  }  


  public function toObject( ?IBigSet $properties = null, bool $includeArrays = false, bool $includeModels = false ) : \stdClass
  {
    if ( $includeModels )
      $this->providerWarmup();
    
    return parent::toObject( $properties, $includeArrays, $includeModels );
  }
  
  
  /**
   * Convert this model to an array.
   * @param IPropertySet $properties Properties to include 
   */
  public function toArray( ?IBigSet $properties = null, bool $includeArrays = false, bool $includeModels = false ) : array
  {
    if ( $includeModels )
      $this->providerWarmup();
    
    return parent::toArray( $properties, $includeArrays, $includeModels );
  }  
  
  
  private function providerWarmup()
  {
    //..Warm up any providers.
    foreach( array_keys( $this->providers ) as $name )
    {
      $this->getValue( $name );
    }    
  }
  
}
