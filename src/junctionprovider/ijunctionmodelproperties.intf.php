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

namespace buffalokiwi\magicgraph\junctionprovider;

use buffalokiwi\magicgraph\property\IPropertyConfig;


/**
 * Defines a model that contains a link to a parent and a link to a target.
 * 
 */
interface IJunctionModelProperties extends IPropertyConfig
{
  /**
   * Retrieve the primary key property name 
   * @return string name 
   */
  public function getId() : string;
  
  
  /**
   * Retrieve the parent id property name 
   * @return string name 
   */
  public function getParentId() : string;
  
  
  /**
   * Retrieve the target id property name 
   * @return string name 
   */
  public function getTargetId() : string;
}
