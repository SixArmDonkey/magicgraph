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

use buffalokiwi\magicgraph\property\IPropertyConfig;


/**
 * Defines methods for accessing column names for the attribute group
 */
interface IAttributeGroupPropertiesCols extends IPropertyConfig
{
  /**
   * Retrieve the id property name 
   * @return string name 
   */
  public function getIdColumn() : string;
  
  
  /**
   * Retrieve the name property name 
   * @return string name 
   */
  public function getNameColumn() : string;
  
  
  /**
   * Get the attributes column name
   * @return array name 
   */
  public function getAttrColumn() : string;  
}

