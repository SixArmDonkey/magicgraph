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

namespace buffalokiwi\magicgraph\property;


/**
 * Defines a property with minimum and maximum properties.
 */
interface IBoundedProperty extends IProperty
{
  /**
   * Sets the minimum string length of the value.
   * Set to -1 for unused.
   * Used in IProperty::validate();
   * @return int Minimum length or -1 
   */
  public function getMin() : float;
  
  
  /**
   * Sets the maximum string length of the value.
   * Set to -1 for unused.
   * Used in IProperty::validate();
   * @return int Maximum length or -1 
   */
  public function getMax() : float;  
}
