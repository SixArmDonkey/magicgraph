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


/**
 * Columns for the xxx_attribute_value tables.
 */
interface IAttrValueCols 
{
  /**
   * Get entity id column name 
   * @return string name 
   */
  public function getEntityId() : string;
  
  
  /**
   * Get attribute id column name 
   * @return string name 
   */
  public function getAttributeId() : string;
  
  
  /**
   * Get value column name 
   * @return string name 
   */
  public function getValue() : string;
  
  
  /**
   * Retrieve the text value 
   * @return string
   */
  public function getTextValue() : string;  
}
