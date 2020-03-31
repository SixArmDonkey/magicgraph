<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */

namespace buffalokiwi\magicgraph\property;



/**
 * Quick properties class used for creating a property set on the fly.
 */
class QProperties extends BasePropertyConfig implements IPropertyConfig
{
  /**
   * Config array 
   * @var array
   */
  private $config;
  
  
  /**
   * Create some QProperties 
   * @param array $config Model config array 
   */
  public function __construct( array $config, \buffalokiwi\magicgraph\property\INamedPropertyBehavior ...$behavior )
  {
    parent::__construct( ...$behavior );
    $this->config = $config;
  }
  
  protected function createConfig() : array
  {
    return $this->config;
  }
}
