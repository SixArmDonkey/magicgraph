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
 * Property configuration constants.
 * These are used as array keys within the property configuration array.
 */
interface IPropertyConst 
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
   * Minimum value/length
   */
  const MIN = "min";
  
  /**
   * Maximum value/length
   */
  const MAX = "max";
  
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
   * An optional tag for the attribute.
   * This can be any string, and can be used for whatever.
   */
  const TAG = 'tag';
}
