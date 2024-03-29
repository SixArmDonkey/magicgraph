<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */

namespace buffalokiwi\magicgraph\persist;

/**
 * Defines some condition used within an IFilter 
 * @deprecated Stupid
 */
interface ICondition 
{
  /**
   * Retrieve the condition as a string 
   */
  public function getCondition() : string;
  
  /**
   * Retrieve a list of values 
   * @return array values 
   */
  public function getValues() : array;
}
