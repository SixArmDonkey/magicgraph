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

use Closure;


/**
 * Adds the class/interface property to the builder used for properties that
 * hold object values.
 */
interface IObjectPropertyBuilder extends IPropertyBuilder
{  
  /**
   * Sets the class name used for certain data types
   * @param string $clazz class name 
   * @return PropertyBuilder this 
   */
  public function setClass( string $clazz ) : void;
  
  
  /**
   * When the property type is ENUM or SET, a class name must be declared.
   * The defined class manages the data within the column.
   * @return string class name 
   */
  public function getClass() : string;      
  
  
  /**
   * Create a new object property builder
   * @param Callable $createClass A function that returns an instance defined by the class property.
   * The closure must accept a string representing the class or interface name to create an instance of.
   * 
   * f( string $clazz ) : instance of $clazz 
   */
  public function setCreateObjectFactory( \Closure $createClass ) : void;  
  
  
  /**
   * Retrieve an optional closure that returns a new instance of the object 
   * defined by the class property.
   * The closure MUST return the defined type.
   * @return Closure|null Callback
   */
  public function getCreateClassClosure() : ?Closure;  
}
