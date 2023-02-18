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


/**
 * Defines a model that represents some junction table.
 */
interface IJunctionModel extends \buffalokiwi\magicgraph\IModel
{
  /**
   * Retrieve the id of this entry 
   * @return int id 
   */
  public function getId() : int;
  
  
  /**
   * Retrieve the id of the parent model
   * @return int parent id 
   */
  public function getParentId() : int;
  
  
  /**
   * Retrieve the id of the target model 
   * @return int id 
   */
  public function getTargetId() : int;
  
  
  /**
   * Sets the primary key 
   * @param int $value id 
   * @return void
   */
  public function setId( int $value ) : void;
  
  
  /**
   * Sets the parent id 
   * @param int $value id 
   * @return void
   */
  public function setParentId( int $value ) : void;
  
  
  /**
   * Sets the target id 
   * @param int $value id 
   * @return void
   */
  public function setTargetId( int $value ) : void;
}
