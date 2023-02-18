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

use buffalokiwi\magicgraph\IModel;


/**
 * Defines a link between an attribute group and attribute.
 * (Attribute group members)
 */
interface IAttrGroupLink extends IModel
{
  /**
   * Get the attribute group id
   * @return int id 
   */
  public function getGroupId() : int;
  
  
  /**
   * Get the attribute id 
   * @return int id 
   */
  public function getAttributeId() : int;
  
  
  /**
   * Sets the attribute group id
   * @param int $id id 
   * @return void
   */
  public function setGroupId( int $id ) : void;
  
  
  /**
   * Sets the attribute id  
   * @param int $id id 
   * @return void
   */
  public function setAttributeId( int $id ) : void;
  
  
  /**
   * Retrieve the link id 
   * @return int id 
   */
  public function getId() : int;  
}
