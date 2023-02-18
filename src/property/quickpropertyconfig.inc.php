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

namespace buffalokiwi\magicgraph\property;

use Closure;


/**
 * A config object that accepts the config as an array and makes it into an object.
 */
class QuickPropertyConfig extends BasePropertyConfig implements IPropertyConfig
{
  /**
   * Config data
   * @var array 
   */
  private $config;
  
  
  
  /**
   * Create a new IPropertyConfig instance 
   * @param array $config Config data array 
   * @param \Closure $onValidate f( IModel ) throws ValidationException
   */
  public function __construct( array $config, ?Closure $onValidate = null, INamedPropertyBehavior ...$behavior )
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
}