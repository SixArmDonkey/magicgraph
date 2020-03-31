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
 * Used for PropertyFactory config 
 */
class DefaultPropertyConfig
{
  /**
   * Property caption/label for users to see.
   */
  const CAPTION = 'caption';
  
  /**
   * An optional unique identifier for some property 
   */
  const ID = 'id';
    
  /**
   * Default value 
   */
  const VALUE = 'value';
  
  /**
   * Callback used for setting a properties value when it is an object.
   * f( IProperty, mixed $value ) : mixed 
   */
  const SETTER = 'setter';
  
  /**
   * Callback used for setting a properties value when it is an object.
   * f( IProperty, mixed $value ) : mixed 
   */
  const GETTER = 'getter';
  
  
  /**
   * Callback used for setting a properties value when it is an object.
   * f( IModel, IProperty, mixed $value ) : mixed 
   */
  const MSETTER = 'msetter';
  
  
  /**
   * Callback used for setting a properties value when it is an object.
   * f( IModel, IProperty, mixed $value ) : mixed 
   */
  const MGETTER = 'mgetter';
  
  /**
   * Data type.
   * This must map to a valid value of EPropertyType
   */
  const TYPE = "type";
  
  /**
   * Property flags. 
   * This must map to a comma-delimited list of valid SPropertyFlags values 
   */
  const FLAGS = "flags";
  
  /**
   * Class name used with object properties
   */
  const CLAZZ = "clazz";
  
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
   * Minimum value/length
   */
  const MIN = "min";
  
  /**
   * Maximum value/length
   */
  const MAX = "max";
  
  /**
   * Closure to prepare data during setProperty()
   * [new value] = function( [input value], IModel )
   */
  //const PREPARE = "prepare";
  
  /**
   * Closure for validating prior to save
   * [bool is valid] = function( IProperty, [input value] )
   */
  const VALIDATE = "validate";
  
  /**
   * Validation regex 
   */
  const PATTERN = "pattern";  
  
  /**
   * A config array 
   */
  const CONFIG = "config";  
  
  /**
   * A prefix used by the default property set, which can proxy a get/set value
   * call to a nested IModel instance.
   */
  const PREFIX = 'prefix';
  
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
   * An optional tag for the attribute.
   * This can be any string, and can be used for whatever.
   */
  const TAG = 'tag';
}
