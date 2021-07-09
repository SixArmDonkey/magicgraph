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

namespace buffalokiwi\magicgraph\search;

use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\persist\ISQLRepository;
use buffalokiwi\magicgraph\property\IPropertySet;


interface IJoinFilter
{
  /**
   * Retrieve the property name that triggers this condition 
   * @return string property name 
   */
  public function getPropertyName() : string;

  
  /**
   * Retrieve the backing repository that manages the linked data.
   * @return ISQLRepository|null repo
   */
  public function getHostRepo() : ?IRepository;
  
  
  /**
   * Should return something like ( getHostRepo() == null );
   * @return bool is foreign
   */
  public function isForeign() : bool;
  
  
  /**
   * Retrieve the property set used for the join 
   * @return IPropertySet prop set 
   */
  public function getPropertySet() : IPropertySet;  
}

