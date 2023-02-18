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

use buffalokiwi\magicgraph\property\BasePropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyConfig;


/**
 * A generic property configuration.
 */
class GenericPropertyConfig extends BasePropertyConfig implements IPropertyConfig
{
  /**
   * Configuration array 
   * @var array 
   */
  private $config;
  
  
  /**
   * Property names 
   * @var array 
   */
  private $propNames = [];
  
  
  /**
   * Create a new GenericPropertyConfig instance 
   * @param array $config Config array 
   */
  public function __construct( array $config, \buffalokiwi\magicgraph\property\INamedPropertyBehavior ...$behavior )
  {
    parent::__construct( ...$behavior );
    $this->config = $config;
  }
  
  
  /**
   * Retrieve the config array 
   * @return array config 
   */
  protected function createConfig() : array
  {
    return $this->config;
  }
  
  
  /**
   * Retrieve a list of property names defined via this config 
   * @return array names 
   */
  public function getPropertyNames() : array
  {
    if ( empty( $this->propNames ))
      $this->propNames = array_keys( $this->config );
    
    return $this->propNames;
  }
}
