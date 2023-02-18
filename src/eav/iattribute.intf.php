<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */



namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;
use InvalidArgumentException;


/**
 * Defines a model that contains properties, validation information and behavior
 * of some attribute.
 */
interface IAttribute extends IModel
{
  /**
   * Convert this to a config array entry.
   * Merge this with other attributes 
   * @return array array entry 
   */
  public function toConfigArray() : array;
  
  /**
   * Retrieve the attribute id 
   * @return int id
   */
  public function getId() : int;
  

  /**
   * Retrieve the attribute type 
   * @return IPropertyType type data
   */
  public function getPropertyType() : IPropertyType;
  
  
  /**
   * Sets the attribute type 
   * @param IPropertyType $value type 
   * @throws InvalidArgumentException
   */
  public function setPropertyType( IPropertyType $value ) : void;
  
  /**
   * Retrieve the default value for some attribute
   * @return mixed default value 
   */
  public function getDefaultValue();
  
  
  /**
   * Sets the default value for some attribute
   * @param mixed $value Value 
   */
  public function setDefaultValue( $value ) : void;
  
  
  /**
   * Retrieve the class name used with object property types
   * @return string class name 
   */
  public function getPropertyClass() : string;
  
  
  /**
   * Sets the class name used with object property types
   * @param string $value value 
   */
  public function setPropertyClass( string $value ) : void;
  
  
  /**
   * Retrieve the flags associated with the property
   * @return IPropertyFlags flags 
   */
  public function getFlags() : IPropertyFlags;
  
  
  /**
   * Sets flags/options for some property
   * @param IPropertyFlags $value property flags 
   */
  public function setFlags( IPropertyFlags $value ) : void;
  
  
  /**
   * Gets the minimum value or length for some property value 
   * @return int minimum value or length
   */
  public function getMin() : int;
  
  
  /**
   * Sets the minimum value or length for some property value 
   * @param int $value Value 
   * @throws \InvalidArgumentException if min is less than -1 
   */
  public function setMin( int $value ) : void;
  
  
  /**
   * Retrieves the maximum length or value for some property value 
   * @return int maximum length or value 
   */
  public function getMax() : int;
  
  
  /**
   * Sets the maximum length or value for some property
   * @param int $value maximum length or value 
   * @throws \InvalidArgumentException if max is less than -1 
   */
  public function setMax( int $value ) : void;
  
  
  /**
   * Retrieve some regular expression used for validating a property value
   * @return string pattern 
   */
  public function getPattern() : string;
  
  
  /**
   * Set a regular expression used to validate some property value.
   * @param string $value pattern 
   */
  public function setPattern( string $value ) : void;
  
  
  /**
   * Retrieve additional runtime behavior for this attribute
   * @return IAttributeBehavior behavior 
   */
  public function getBehavior() : ?IAttributeBehavior;
  
  
  /**
   * Sets additional runtime behavior for this attribute
   * @param IAttributeBehavior $value behavior 
   */
  public function setBehavior( IAttributeBehavior $value = null ) : void;
  
  
  /**
   * Retrieve the attribute caption 
   * @return string caption 
   */
  public function getCaption() : string;
  
  
  /**
   * Sets the attribute caption 
   * @param string $value caption 
   * @throws InvalidArgumentException
   */
  public function setCaption( string $value ) : void;
  
  
  /**
   * Retrieve the internal attribute code/name
   * @return string code
   */
  public function getCode() : string;
  
  
  /**
   * Sets the internal attribute code/name
   * @param string $value code 
   * @throws InvalidArgumentException
   */
  public function setCode( string $value ) : void;
  
  
  /**
   * Gets some arbitrary config data
   * @return array data 
   */
  public function getConfig() : array;
  
  
  /**
   * Sets some arbitrary config data
   * @param array $config data
   * @return void
   */
  public function setConfig( array $config ) : void;
  
  
  /**
   * Get the tag value 
   * @return string tag 
   */
  public function getTag() : string;
  
  
  /**
   * Set the tag value 
   * @param string $tag tag
   * @return void
   */
  public function setTag( string $tag ) : void;  
}
