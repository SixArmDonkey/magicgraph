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


/**
 * Attribute group link column names
 */
interface IAttrGroupLinkCols
{
  /**
   * Get the group id column name
   * @return string name 
   */
  public function getGroupId() : string;
  
  
  /**
   * Get the attribute id column name 
   * @return string name 
   */
  public function getAttributeId() : string;
  
  /**
   * Retrieve the link id column name 
   * @return string id column
   */
  public function getId() : string;  
}
