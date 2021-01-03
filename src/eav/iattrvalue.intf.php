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
use InvalidArgumentException;


/**
 * Attribute value model 
 */
interface IAttrValue extends IModel
{
  /**
   * Retrieve the entity id.  ie: product id or similar.
   * @return int id 
   */
  public function getEntityId() : int;
  
  
  /**
   * Sets the entity id 
   * @param int $id id 
   * @return void
   * @throws InvalidArgumentException
   */
  public function setEntityId( int $id ) : void;
  
  
  /**
   * Retrieve the attribute id  
   * @return int id 
   */
  public function getAttributeId() : int;
  
  
  /**
   * Sets the attribute id 
   * @param int $id id 
   * @return void
   * @throws InvalidArgumentException
   */
  public function setAttributeId( int $id );
  
  
  /**
   * Retrieve the value 
   * @return string value 
   */
  public function getAttrValue() : string;
  
  
  /**
   * Sets the value 
   * @param string $value value 
   */
  public function setAttrValue( string $value );
}
