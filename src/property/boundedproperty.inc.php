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
 * Adds minimum and maximum properties for some property 
 */
abstract class BoundedProperty extends AbstractProperty implements IBoundedProperty
{
  /**
   * Minimum value 
   * @var float
   */
  private $min;
  
  /**
   * Maximum value 
   * @var float
   */
  private $max;
  
  
  /**
   * Create a new BoundedProperty instance.
   * @param IBoundedPropertyBuilder $builder
   */
  public function __construct( IBoundedPropertyBuilder $builder )
  {
    parent::__construct( $builder );
    $this->min = $builder->getMin();
    $this->max = $builder->getMax();
  }
  
  
  /**
   * Retrieve the maximum value of the stored integer.
   * Defaults to PHP_FLOAT_MIN
   * @return float min 
   */
  public function getMin() : float
  {
    return $this->min;
  }
  
  
  /**
   * Retrieve the minimum value of the stored integer.
   * Defaults to PHP_FLOAT_MAX 
   * @return float max
   */
  public function getMax() : float
  {
    return $this->max;
  }
    
}
