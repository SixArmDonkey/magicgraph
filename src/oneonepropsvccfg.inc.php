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

use buffalokiwi\magicgraph\property\IPropertySvcConfig;
use Closure;
use Exception;
use InvalidArgumentException;
use Ups\Entity\Exception as Exception2;


class OneOnePropSvcCfg extends property\BasePropertyConfig implements IPropertySvcConfig
{
  /**
   * id prop name 
   * @var string
   */
  private $propertyName;
  
  /**
   * Model property name
   * @var string
   */
  private $modelPropertyName;
  
  /**
   * Optional save function closure 
   * @var ?\Closure
   */
  private $saveFunction;
  
  
  /**
   * 
   * @param string $propertyName Parent property containing the primary key value of the model property
   * @param string $modelPropertyName parent property containing the model loaded by the value of $propertyName
   * @param Closure|null $getSaveFunction An OPTIONAL save function.  This does not override the save function in the OneOnePropertyService
   * @param array $config A config array for a property set 
   * f( IModel $parent ) : array 
   */
  public function __construct( string $propertyName, string $modelPropertyName, ?Closure $getSaveFunction = null, array $config = [] )
  {
    if ( empty( $propertyName ))
      throw new InvalidArgumentException( 'propertyName must not be empty' );
    else if ( empty( $modelPropertyName ))
      throw new InvalidArgumentException( 'modelPropertyName must not be empty' );
    
    $this->propertyName = $propertyName;
    $this->modelPropertyName = $modelPropertyName;
    $this->getSaveFunction = $getSaveFunction;
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
   * @throws Exception2
   */
  public function getSaveFunction( IModel $parent ) : array
  {
    $f = $this->saveFunction;
    
    if ( $f instanceof Closure )
    {
      $res = $f( $parent );
      if ( !is_array( $res ))
        throw new Exception( 'getSaveFunction callback for ' . static::class . ' did not return an array' );
      return $res;
    }
    
    return [];
  }
  
  
  protected function createConfig() : array
  {
    return $this->config;
  }
}
