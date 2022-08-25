<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2022 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */

declare( strict_types=1 );

namespace buffalokiwi\magicgraph\property;


/**
 * Property Behavior configuration constants.  
 * These are used as array keys within a property configuration array 
 */
interface IBehaviorConst
{
  /**
   * Callback used for setting a properties value 
   * f( IProperty, mixed $value ) : mixed 
   */
  const SETTER = 'setter';
  
  /**
   * Callback used for setting a properties value 
   * f( IProperty, mixed $value ) : mixed 
   */
  const GETTER = 'getter';
  
  /**
   * Callback used for setting a properties value when a model reference is required
   * f( IModel, IProperty, mixed $value ) : mixed 
   */
  const MSETTER = 'msetter';
  
  /**
   * Callback used for getting a properties value when a model reference is required
   * f( IModel, IProperty, mixed $value ) : mixed 
   */
  const MGETTER = 'mgetter';
  
  /**
   * Callback used when retrieving a value within IModel::toArray().
   * When the value used within the application differs from the persisted value, this can be used to 
   * modify the persisted value.
   * This will always be called after GETTER and MGETTER.
   * f( IModel, IProperty, mixed $value ) : mixed 
   */
  const TOARRAY = 'toarray';
 
  /**
   * A callback used to initialize the default value within the model.
   * This is called only once, when the model is first loaded.
   * 
   * Default value is supplied, and the returned value is used as the new
   * default value.
   * 
   * f( mixed $defaultValue ) : mixed
   */
  const INIT = "initialize";
  
  /**
   * Closure for validating prior to save
   * [bool is valid] = function( IProperty, [input value] )
   */
  const VALIDATE = "validate";
  
  /**
   * On Change event
   * f( IProperty, oldValue, newValue ) : void
   */
  const CHANGE = 'onchange';
  
  /**
   * For a give property, create an htmlproperty\IElement instance used as an html form input.
   * 
   * f( IModel $model, IProperty $property, string $name, string $id, string $value ) : IElement
   */
  const HTMLINPUT = 'htmlinput';
  
  /**
   * Is empty check event.
   * Tests that the property value is empty
   * f( IProperty, value ) : bool
   */
  const IS_EMPTY = 'isempty';
  
  
  /**
   * This is here for convenience.  
   * I guess it's good to know which constants are behavior related.
   * @todo This violates SRP 
   */
  const BEHAVIOR_LIST = [
    self::VALIDATE,
    self::INIT,
    self::SETTER,
    self::GETTER,
    self::MSETTER,
    self::MGETTER,
    self::CHANGE,
    self::IS_EMPTY,
    self::HTMLINPUT,
    self::TOARRAY
  ];
  
}

