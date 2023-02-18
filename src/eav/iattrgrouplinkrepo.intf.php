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

use buffalokiwi\magicgraph\persist\IRepository;


/**
 * Defines the attribute group repository 
 */
interface IAttrGroupLinkRepo extends IRepository
{
  /**
   * Retrieve a list of IAttributeGroupLink models belonging to an 
   * attribute group
   * @param int $id
   * @return array
   */
  public function getLinksByGroupId( int $id ) : array;
}
