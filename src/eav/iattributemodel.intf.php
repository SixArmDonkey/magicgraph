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

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\IModel;


/**
 * A model that can attach to an attribute group.
 * Attribute groups are composed of attributes that can be attached to 
 * the model at runtime.
 * 
 * @todo evaluate the usefulness of this interface. Seems like it also needs a getAttributes() method.
 */
interface IAttributeModel extends IModel
{
  /**
   * Retrieve the entity id 
   * @return int
   */
  public function getId() : int;
  
  /**
   * Retrieve the attribute group id 
   * @return int id id 
   */
  public function getAttrGroupId() : int;
  
  
  /**
   * Sets the attribute group id
   * @param int $id id 
   * @return void
   */
  public function setAttrGroupId( int $id ) : void;    
} 