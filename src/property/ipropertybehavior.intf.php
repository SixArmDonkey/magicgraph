<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */

declare( strict_types=1 );

namespace buffalokiwi\magicgraph\property;

use Closure;


/**
 * Callbacks that can override behavior in a property.
 * @todo This interface is going to be refactored. Look at commented examples for future refactor.
 */
interface IPropertyBehavior 
{
  /**
   * Validate some property value 
   * 
   * [bool is valid] = function( IProperty, [input value] )
   * 
   * @return Closure callback
   */
  public function getValidateCallback() : ?Closure;
  
  
  /**
   * Validate some property value
   * @param IProperty $prop
   * @param mixed $value
   * @return bool
   */
  //public function validate( IProperty $prop, mixed $value ) : bool;
    

  /**
   * Callback used to set an objects value.
   * 
   * Returns the value to set 
   * 
   * f( IProperty, $value ) : mixed 
   * 
   * @return \Closure callback
   */
  public function getSetterCallback() : ?Closure;
  
  
  /**
   * Filters a property value during a property level set operation.
   * @param IProperty $prop
   * @param mixed $value
   * @return mixed
   */
  //public function propertySetter( IProperty $prop, mixed $value ) : mixed;
  
  
  /**
   * Returns value to get 
   * f( IProperty, $value, array $context ) : mixed 
   * @return Closure|null
   */
  public function getGetterCallback() : ?Closure;  
  
  
  /**
   * Filters a property value during a property-level get operation.
   * @param IProperty $prop
   * @param mixed $value
   * @param array $context
   * @return mixed
   */
  //public function propertyGetter( IProperty $prop, mixed $value, array $context = [] ) : mixed;
  
  
  
  
  
  /**
   * Callback used for initializing some value when the model is loaded.
   * f( $value ) : mixed 
   * @return \Closure callback 
   */
  public function getInitCallback() : ?Closure;    
  
  
  /**
   * Callback used to set a value.
   * This is called prior to IProperty::validate() and the return value will 
   * replace the supplied value.
   * 
   * f( IModel, IProperty, value )
   * 
   * @return Closure callback
   */
  public function getModelSetterCallback() : ?Closure;
  
  
  /**
   * Callback used when retrieving a value.
   * Called during IProperty::getValue().  The return value will be used
   * as the return value from getValue();
   * f( IModel, IProperty, value )
   * 
   * @return Closure|null
   */
  public function getModelGetterCallback() : ?Closure;  
  
  
  /**
   * Retrieve the onChange callback
   * f( IProperty, oldValue, newValue ) : void
   * @return Closure|null
   */
  public function getOnChangeCallback() : ?Closure;  
  
  
  /**
   * Retrieve the closure that can test empty state.
   * f( IProperty, value, defaultValue ) : bool
   * @return Closure|null 
   */
  public function getIsEmptyCallback() : ?Closure;
  
  
  /**
   * Retrieve a callback that converts a property into an html form input.
   * 
   * f( IModel $model, IProperty $property, string $name, string $id, $value ) : IElement
   * 
   * @return Closure|null
   */
  public function getHTMLInputCallback() : ?Closure;
  
  
  /**
   * Callback used when retrieving a value within IModel::toArray().
   * When the value used within the application differs from the persisted value, this can be used to 
   * modify the persisted value.
   * This will always be called after GETTER and MGETTER.
   * f( IModel, IProperty, mixed $value ) : mixed 
   * @return Closure|null
   */  
  public function getToArrayCallback() : ?Closure;    
}
