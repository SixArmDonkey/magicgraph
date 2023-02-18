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

namespace buffalokiwi\magicgraph\eav;


interface IAttrGroupRepo extends \buffalokiwi\magicgraph\persist\IRepository
{
  /**
   * Retrieves the attribute group id with the lowest id value.
   * This is the default.
   * @return int
   */
  public function getDefaultAttributeGroupId() : int;
  
  
  /**
   * Retrieve a list of attribute group names keyed by group id.
   * @return array [id => name]
   */
  public function getGroupNameList() : array;
}
