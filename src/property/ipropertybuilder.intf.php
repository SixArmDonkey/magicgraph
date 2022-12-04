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


/**
 * Used as input for all properties.
 * Make and use as many arguments as needed. 
 */
interface IPropertyBuilder
{
  /**
   * Retrieve the optional prefix that may be used by some property implementations
   * @return string
   */
  public function getPrefix() : string;
  
  
  /**
   * Some property implementations may utilize a prefix.
   * This is some arbitrary string value.
   * @param string $value The prefix 
   * @return void
   */
  public function setPrefix( string $value ) : void;


  /**
   * Retrieve an arbitrary tag value 
   * @return string
   */
  public function getTag() : string;
  
  
  /**
   * Set an arbitrary tag value 
   * @param string $tag value 
   * @return void
   */  
  public function setTag( string $tag ) : void;  
  
  /**
   * Retrieve the optionally set unique identifier for this property.
   * This may be zero if unassigned.
   * @return int optional id 
   */
  public function getId() : int;
  
  
  /**
   * Get the property caption/label
   * @return string caption
   */
  public function getCaption() : string;
  
  
  /**
   * Sets the property caption/label
   * @param string $caption caption
   * @return void
   */
  public function setCaption( string $caption ) : void;
  
  
  /**
   * Retrieve the property type
   * @return IPropertyType type
   */
  public function getType() : IPropertyType;
  
  
  /**
   * Retrieve the set of flags for this property
   * @return IPropertyFlags flags
   */
  public function getFlags() : IPropertyFlags;  
  
  
  /**
   * Overwrite the internal property flags instance with a new one 
   * @param IPropertyFlags $flags flags 
   * @return void
   */
  public function setFlags( IPropertyFlags $flags ) : void;


  /**
   * Retrieve the property name
   * @return string name 
   */
  public function getName() : string;
  
  
  /**
   * Sets the property name 
   * @param string $name
   * @return void
   */
  public function setName( string $name ) : void;
  
  
  /**
   * Sets the default property value
   * @param mixed $value Value 
   * @return PropertyBuilder This 
   */
  public function setDefaultValue( $value ) : void;
  
  
  /**
   * Retrieve the default value for some property 
   * @return mixed Default value 
   */
  public function getDefaultValue() : mixed;
  
  
  /**
   * Sets callbacks to modify the property behavior 
   * @param IPropertyBehavior $behavior callbacks
   * @return void
   */
  public function addBehavior( ?IPropertyBehavior $behavior ) : void;
    

  /**
   * Retrieve callbacks for modifying property behavior 
   * @return IPropertyBehavior[] callbacks
   */
  public function getBehavior() : array;
  
  
  /**
   * Sets some config array if needed.
   * @param array $config Arbitrary config data
   * @return PropertyBuilder this 
   */
  public function setConfig( array $config ) : void;
  
  
  /**
   * Retrieve some arbitrary config array 
   * @return array config 
   */
  public function getConfig() : array;
}
