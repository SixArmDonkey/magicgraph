<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\property;


/**
 * Adds the min/max setters/getters to the property builder
 */
interface IBoundedPropertyBuilder extends IPropertyBuilder
{
  /**
   * Sets the minimum value/length 
   * @param float $min minimum 
   * @return PropertyBuilder $this
   * @throws InvalidArgumentException
   */
  public function setMin( float $min ) : void;
  
  
  /**
   * Retrieve the minimum value or length.
   * @return float minimum 
   */
  public function getMin() : float;
  
  
  /**
   * Sets the maximum value/length 
   * @param float $max maximum
   * @return PropertyBuilder $this
   * @throws InvalidArgumentException
   */
  public function setMax( float $max ) : void;
  
  
  /**
   * Retrieve the maximum value or length
   * @return float maximum 
   */
  public function getMax() : float;  
}
