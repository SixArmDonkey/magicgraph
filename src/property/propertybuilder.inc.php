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
 * Builder used for creating Property instances.
 */
class PropertyBuilder implements IPropertyBuilder
{
  /**
   * Property caption/label
   * @var string
   */
  private $caption = '';
  
  /**
   * Default property value 
   * @var mixed
   */
  private $defaultValue;

  /**
   * Flags 
   * @var SPropertyFlags
   */
  private $flags;
    
  /**
   * Property Type 
   * @var EPropertyType
   */
  private $type;
  
  /**
   * Property name 
   * @var string 
   */
  private $name;
  
  /**
   * Property behavior 
   * @var IPropertyBehavior[]
   */
  private $behavior = [];
  
  /**
   * Some config array if needed.
   * @var array
   */
  private $config = [];
  
  /**
   * Optional unique id 
   * @var int
   */
  private $id = 0;
  
  /**
   * A prefix that can be used to map something to an IModel instance 
   * @var string 
   */
  private $prefix = '';
  
  /**
   * An optional tag 
   * @var string
   */
  private $tag = '';
  
  
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
    $this->type = $type;
    $this->flags = ( $flags == null ) ? new SPropertyFlags() : $flags;
    $this->name = $name;
    $this->defaultValue = $defaultValue;
    
    foreach( $behavior as $b )
    {
      $this->behavior[] = $b;
    }
  }
  
  
  /**
   * Retrieve the optionally set unique identifier for this property.
   * This may be zero if unassigned.
   * @return int optional id 
   */
  public function getId() : int
  {
    return $this->id;
  }
  
  
  /**
   * Sets the optional unique identifier for this property
   * @param int $id id 
   * @return void
   */
  public function setId( int $id ) : void
  {
    $this->id = $id;
  }
  
  
  /**
   * Retrieve an arbitrary tag value 
   * @return string
   */
  public function getTag() : string
  {
    return $this->tag;
  }
    
  
  /**
   * Set an arbitrary tag value 
   * @param string $tag value 
   * @return void
   */
  public function setTag( string $tag ) : void 
  {
    $this->tag = $tag;
  }
    
  
  
  /**
   * Get the property caption/label
   * @return string caption
   */
  public function getCaption() : string
  {
    return $this->caption;
  }
  
  
  /**
   * Sets the property caption/label
   * @param string $caption caption
   * @return void
   */
  public function setCaption( string $caption ) : void
  {
    $this->caption = $caption;    
  }
  
  
  /**
   * Retrieve the property type
   * @return IPropertyType type
   */
  public function getType() : IPropertyType
  {
    return $this->type;
  }
  
  
  /**
   * Retrieve the set of flags for this property
   * @return IPropertyFlags flags
   * @throws \Exception if flags have not been set 
   */
  public function getFlags() : IPropertyFlags
  {
    //..It is impossible for this exception to be thrown as long as $flags is private 
    //if ( !( $this->flags instanceof IPropertyFlags ))
    //  throw new \Exception( 'No IPropertyFlags instance has been set.  Please set one' );
    
    return $this->flags;
  }


  /**
   * Overwrite the internal property flags instance with a new one 
   * @param IPropertyFlags $flags flags 
   * @return void
   */
  public function setFlags( IPropertyFlags $flags ) : void
  {
    $this->flags = $flags;
  }
  
  
  /**
   * Retrieve the property name
   * @return string name 
   */
  public function getName() : string
  {
    return $this->name;
  }    
  
  
  /**
   * Sets the property name 
   * @param string $name name 
   * @return void
   * @throws \InvalidArgumentException
   */
  public function setName( string $name ) : void
  {
    $tn = trim( $name );
    if ( strlen( $tn ) == 0 )
      throw new \InvalidArgumentException( 'property name must not be empty' );
    
    $this->name = $tn;
  }
  
  
  /**
   * Sets the default property value
   * @param mixed $value Value 
   */
  public function setDefaultValue( $value ) : void
  {
    $this->defaultValue = $value;
  }
  
  
  /**
   * Retrieve the default value for some property 
   * @return mixed Default value 
   */
  public function getDefaultValue() : mixed 
  {
    return $this->defaultValue;
  }
  
  
  /**
   * Sets callbacks to modify the property behavior 
   * @param IPropertyBehavior $behavior callbacks
   * @return void
   */
  public function addBehavior( ?IPropertyBehavior $behavior ) : void
  {
    if ( $behavior instanceof IPropertyBehavior )
      $this->behavior[] = $behavior;
  }
    

  /**
   * Retrieve callbacks for modifying property behavior 
   * @return IPropertyBehavior[] callbacks 
   */
  public function getBehavior() : array
  {
    return $this->behavior;
  }
  
  
  
  /**
   * Sets some config array if needed.
   * @param array $config Arbitrary config data
   * @return PropertyBuilder this 
   */
  public function setConfig( array $config ) : void
  {
    $this->config = $config;
  }
  
  
  /**
   * Retrieve some arbitrary config array 
   * @return array config 
   */
  public function getConfig() : array 
  {
    return $this->config;
  }      


  /**
   * Retrieve the optional prefix that may be used by some property implementations
   * @return string
   */
  public function getPrefix() : string
  {
    return $this->prefix;
  }
  
  
  /**
   * Some property implementations may utilize a prefix.
   * This is some arbitrary string value.
   * @param string $value The prefix 
   * @return void
   */
  public function setPrefix( string $value ) : void
  {
    $this->prefix = $value;
  }
}
