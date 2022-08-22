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
 * Adds the class/interface name property to the property builder
 */
class ObjectPropertyBuilder extends PropertyBuilder implements IObjectPropertyBuilder
{
  /**
   * Class name 
   * @var string 
   */
  private string $clazz = '';
  
  /**
   * A function that will create instances of the defined class.
   * Use this when the backing object requires constructor arguments, or if you 
   * need to pull something in from a ioc container.
   * f( string $clazz ) : instance of $clazz 
   * @var ?Closure
   */
  private ?Closure $createClass = null;
  

  
    
  /**
   * @param IPropertyType $type Property type
   * @param IPropertyFlags|null $flags
   * @param string $name
   * @param type $defaultValue
   * @param IPropertyBehavior $behavior one or more behavior objects 
   * @todo Give serious consideration to removing IPropertyType.  I think this is only used to create the correct property object instances in DefaultConfigMapper 
   */
  public function __construct( IPropertyType $type, ?IPropertyFlags $flags = null, string $name = '', 
    $defaultValue = null, IPropertyBehavior ...$behavior )
  {
    parent::__construct( $type, $flags, $name, $defaultValue, ...$behavior );
  }
  

  /**
   * Sets the class name used for certain data types
   * @param string $clazz class name 
   * @return PropertyBuilder this 
   */
  public function setClass( string $clazz ) : void
  {
    $this->clazz = $clazz;
  }
  
  
  /**
   * When the property type is ENUM or SET, a class name must be declared.
   * The defined class manages the data within the column.
   * @return string class name 
   */
  public function getClass() : string
  {
    return $this->clazz;
  }    
  
  
    
  /**
   * Create a new object property builder
   * @param Callable $createClass A function that returns an instance defined by the class property.
   * The closure must accept a string representing the class or interface name to create an instance of.
   * 
   * f( string $clazz ) : instance of $clazz 
   */
  public function setCreateObjectFactory( \Closure $createClass ) : void 
  {
    $this->createClass = $createClass;
  }  
  
  
  /**
   * Retrieve an optional closure that returns a new instance of the object 
   * defined by the class property.
   * The closure MUST return the defined type.
   * f( string $clazz ) : instance of $clazz 
   * @return Closure|null Callback
   */
  public function getCreateClassClosure() : ?Closure
  {
    return $this->createClass;
  }
}
