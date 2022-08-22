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
 * Adds min/max properties to the builder
 * min defaults to zero and max defaults to PHP_MAX_INT.
 */
class BoundedPropertyBuilder extends PropertyBuilder implements IBoundedPropertyBuilder
{  
  /**
   * min length/value
   * @var float
   */
  private $min = PHP_FLOAT_MIN;
  
  
  /**
   * Max length / value 
   * @var float
   */
  private $max = PHP_FLOAT_MAX; 

  
  /**
   * Create a new BoundedPropertyBuilder instance 
   * @param string $name
   * @param IPropertyType $type
   * @param IPropertyFlags $flags
   * @param type $defaultValue
   */
  public function __construct( IPropertyType $type, IPropertyFlags $flags = null, string $name = '', 
    $defaultValue = null, IPropertyBehavior ...$behavior )
  {
    parent::__construct( $type, $flags, $name, $defaultValue, ...$behavior );
  }
  

  /**
   * Sets the minimum value/length 
   * @param float $min minimum 
   * @return PropertyBuilder $this
   */
  public function setMin( float $min ) : void
  {
    $this->min = $min;
  }
  
  
  /**
   * Retrieve the minimum value or length 
   * @return float minimum 
   */
  public function getMin() : float
  {
    return (float)$this->min;
  }
  
  
  /**
   * Sets the maximum value/length 
   * @param float $max maximum
   * @return PropertyBuilder $this
   */
  public function setMax( float $max ) : void
  {
    $this->max = $max;
  }
  

  /**
   * Retrieve the maximum value or length
   * @return float maximum 
   */
  public function getMax() : float
  {
    return (float)$this->max;
  }  
}
