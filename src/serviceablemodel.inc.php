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
use buffalokiwi\magicgraph\property\IPropertySet;
use InvalidArgumentException;
use stdClass;


/**
 * Adds ability to attach model service providers to models.
 * Providers are for expressing relationships in the object graph.
 * ie: You want to include an array of items linked to some object.  Pass the OneManyServiceProvider configured
 * for the linked models.
 * 
 * Pretty much everything should descend from this model.
 */
class ServiceableModel extends DefaultModel implements IServiceableModel
{
 /**
   * Model-backed Service providers.
   * @var IModelPropertyProvider[] 
   */
  private $providers = [];

  
  /**
   * Create a model wrapper that can interact with service providers.
   * 
   * WARNING: $this is leaked in the constructor.  Descending classes MUST ensure that the object is fully constructed prior to calling this constructor.  
   * 
   * @param IPropertySet $model Model properties 
   * @param IModelPropertyProvider $providers A list of service providers for this model
   */
  public function __construct( IPropertySet $properties, IModelPropertyProvider ...$providers )
  {
    parent::__construct( $properties );
    
    foreach( $providers as $p )
    {
      if ( empty( $p->getModelServiceConfig()->getModelPropertyName()))
        throw new InvalidArgumentException( 'Model property name must not be empty' );
      else if ( empty( $p->getModelServiceConfig()->getPropertyName()))
        throw new InvalidArgumentException( 'Property name must not be empty' );
      
      /* @var $p IPropertyServiceProvider */
      $this->providers[$p->getModelServiceConfig()->getModelPropertyName()] = $p;
      
      //..Leaking $this in constructor.
      $p->init( $this );
    }
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
  public function & getValue( string $property, array $context = []  )
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
      /* @var $p IModelPropertyProvider  */      
      $value = $p->setValue( $this, $value );
    }
    parent::setValue( $property, $value );
  }
  
  
  /**
   * Test to see if this model is valid prior to save()
   * @throws ValidationException
   */
  public function validate() : void
  {
    if ( !$this->isValidationEnabled())
      return;
    
    foreach( $this->providers as $p )
    {
      /* @var $p IPropertyServiceProvider */
      $p->validate( $this );
    }
    
    parent::validate();
  }  


  public function toObject( ?IBigSet $properties = null, bool $includeArrays = false, bool $includeModels = false, bool $includeExtra = false ) : stdClass
  {
    if ( $includeModels )
      $this->providerWarmup( $includeArrays, $includeModels );
    
    return parent::toObject( $properties, $includeArrays, $includeModels, $includeExtra );
  }
  
  
  /**
   * Convert this model to an array.
   * @param IPropertySet $properties Properties to include 
   */
  public function toArray( ?IBigSet $properties = null, bool $includeArrays = false, bool $includeModels = false, bool $includeExtra = false, int $_depth = 0  ) : array
  {
    if ( $includeModels )
      $this->providerWarmup( $includeArrays, $includeModels );
    
    return parent::toArray( $properties, $includeArrays, $includeModels, $includeExtra, $_depth );
  }  
  
  
  
  
  private function providerWarmup( bool $includeArrays, bool $includeModels )
  {
    //..Warm up any providers.
    foreach( array_keys( $this->providers ) as $name )
    {
      $p = $this->getPropertySet()->getProperty( $name );
      if ( !$includeArrays && $p->getType()->is( property\IPropertyType::TARRAY ))
        continue;
      else if ( !$includeModels && $p->getType()->is( property\IPropertyType::TMODEL ))
        continue;
      
      $this->getValue( $name );
    }    
  }    
}
