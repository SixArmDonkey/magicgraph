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


class PropServiceBundle
{
  /**
   * A list of property service providers 
   * @var IPropertyServiceProvider[]
   */
  private $providers;
  
  
  public function __construct( \buffalokiwi\magicgraph\IPropertyServiceProvider ...$providers )
  {
    $this->providers = $providers;
  }
  
  
  
  
  
  
  
  /**
   * Saves some item.
   * @param \buffalokiwi\magicgraph\IModel $parent
   * @return void
   */
  public function save( IModel $parent ): void;  
}