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


/**
 * Defines an object used to convert a list of property names. 
 */
interface IModelMap
{
  /**
   * Retrieve the class or interface name of some model 
   * @return string
   */
  public function getClassName() : string;
  
  
  /**
   * Retrieve a map of model property names to persisted property names 
   * @return array [model property name => persisted property name]
   */
  public function getMap() : array;
}

