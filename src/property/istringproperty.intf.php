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


/**
 * A property that holds a string as a value.
 * This can be validated using regular expressions or a simple min/max length
 * setting.
 */
interface IStringProperty extends IBoundedProperty
{
  /**
   * Retrieve a regular expression used to validate the string property value
   * during calls to IProperty::validate().
   * @return string regex
   */
  public function getPattern() : string;
  
  /**
   * Retrieve the property value as a string 
   * @return string
   */
  public function getValueAsString() : string;
}
