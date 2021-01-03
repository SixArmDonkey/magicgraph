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

namespace buffalokiwi\magicgraph;


/**
 * An IModel decorator that makes the model read only.
 * Ensure this is the outer-most wrapper on any call to new IModel().
 */
class ReadOnlyModelWrapper extends ProxyModel
{
  public function __construct( IModel $model )
  {
    parent::__construct( $model );
  }
  
  
  /**
   * Sets some property value.
   * Just in case the supplied model instance doesn't simply call setValue().
   * Alias of setValue()
   * @param string $p Property name
   * @param mixed $v Property value 
   * @see DefaultModel::setValue()
   */
  public function __set( $p, $v )
  {
    $this->setValue( $p, $v );
  }
  
  
  /**
   * Sets the value of some property
   * @param string $property Property to set
   * @param mixed $value property value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function setValue( string $property, $value ) : void
  {
    throw new \Exception( get_class( $this->getModel()) . ' is read only.' );
  }  
}
