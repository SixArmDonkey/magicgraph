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
 * Callbacks that can override behavior in a property.
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
   * Returns value to get 
   * f( IProperty, $value, array $context ) : mixed 
   * @return Closure|null
   */
  public function getGetterCallback() : ?Closure;  
  
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
