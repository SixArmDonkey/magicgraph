<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\DefaultModel;
use buffalokiwi\magicgraph\property\DefaultPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\property\IPropertyType;
use \InvalidArgumentException;



class AttributeModel extends DefaultModel implements IAttribute
{
  /**
   * Attribute columns 
   * @var IAttributeCols
   */
  private $cols;
  

  
  /**
   * There is no default property set included with this object as the properties 
   * require a factory instance.
   * @param IPropertySet $props
   */
  public function __construct( IPropertySet $props )
  {
    parent::__construct( $props );
    $this->cols = $this->getPropertySet()->getPropertyConfig( IAttributeCols::class );
  }
  

  /**
   * Retrieve the attribute id 
   * @return int id
   */
  public function getId() : int
  {
    return $this->getValue( $this->cols->getId());
  }
  

  /**
   * Retrieve the attribute type 
   * @return IPropertyType type data
   */
  public function getPropertyType() : IPropertyType
  {
    return $this->getValue( $this->cols->getType());
  }
  
  
  /**
   * Sets the attribute type 
   * @param IPropertyType $value type 
   * @throws InvalidArgumentException
   */
  public function setPropertyType( IPropertyType $value ) : void
  {
    $this->setValue( $this->cols->getType(), $value );
  }
  
  
  /**
   * Retrieve the default value for some attribute
   * @return mixed default value 
   */
  public function getDefaultValue()
  {
    return $this->getValue( $this->cols->getDefault());
  }
  
  
  /**
   * Sets the default value for some attribute
   * @param mixed $value Value 
   */
  public function setDefaultValue( $value ) : void
  {
    $this->setValue( $this->cols->getDefault(), $value );
  }
  
  
  /**
   * Retrieve the class name used with object property types
   * @return string class name 
   */
  public function getPropertyClass() : string
  {
    return $this->getValue( $this->cols->getClass());
  }
  
  
  /**
   * Sets the class name used with object property types
   * @param string $value value 
   */
  public function setPropertyClass( string $value ) : void
  {
    $this->setValue( $this->cols->getClass(), $value );
  }
  
  
  /**
   * Retrieve the flags associated with the property
   * @return IPropertyFlags flags 
   */
  public function getFlags() : IPropertyFlags
  {    
    return $this->getValue( $this->cols->getFlags());
  }
  
  
  /**
   * Sets flags/options for some property
   * @param IPropertyFlags $value property flags 
   */
  public function setFlags( IPropertyFlags $value ) : void
  {
    $this->setValue( $this->cols->getFlags(), $value );
  }
  
  
  /**
   * Gets the minimum value or length for some property value 
   * @return int minimum value or length
   */
  public function getMin() : int
  {
    return $this->getValue( $this->cols->getMin());
  }


  /**
   * Sets the minimum value or length for some property value 
   * @param int $value Value 
   */
  public function setMin( int $value ) : void
  {
    $this->setValue( $this->cols->getMin(), $value );
  }


  /**
   * Retrieves the maximum length or value for some property value 
   * @return int maximum length or value 
   */
  public function getMax() : int
  {
    return $this->getValue( $this->cols->getMax());
  }
  
  
  /**
   * Sets the maximum length or value for some property
   * @param int $value maximum length or value 
   */
  public function setMax( int $value ) : void
  {
    $this->setValue( $this->cols->getMax(), $value );
  }
  
  
  /**
   * Retrieve some regular expression used for validating a property value
   * @return string pattern 
   */
  public function getPattern() : string
  {
    return $this->getValue( $this->cols->getPattern());
  }
  
  
  /**
   * Set a regular expression used to validate some property value.
   * @param string $value pattern 
   */
  public function setPattern( string $value ) : void
  {
    $this->setValue( $this->cols->getPattern(), $value );
  }
  
  
  /**
   * Retrieve additional runtime behavior for this attribute
   * @return IAttributeBehavior behavior 
   */
  public function getBehavior() : ?IAttributeBehavior
  {
    $b = $this->getValue( $this->cols->getBehavior());
    if ( is_string( $b ) && !empty( $b ))
    {
      try {
        $c = new $b();
        
        if ( !( $c instanceof IAttributeBehavior ))
          throw new \Exception();
        
        return $c;
      } catch ( \Throwable $e ) {
        throw new \Exception( 'failed to create instance of IAttributeBehavior from class name: "' . $b . '"' );
      }
    }
    
    return null;
  }
  
  
  /**
   * Sets additional runtime behavior for this attribute
   * @param IAttributeBehavior $value behavior 
   */
  public function setBehavior( IAttributeBehavior $value = null ) : void
  {
    $this->setValue( $this->cols->getBehavior(), $value );
  }
  
  
  /**
   * Retrieve the attribute caption 
   * @return string caption 
   */
  public function getCaption() : string
  {
    return $this->getValue( $this->cols->getCaption());
  }
  
  
  /**
   * Sets the attribute caption 
   * @param string $value caption 
   * @throws InvalidArgumentException
   */
  public function setCaption( string $value ) : void
  {
    $this->setValue( $this->cols->getCaption(), $value );
  }
  
  
  /**
   * Retrieve the internal attribute code/name
   * @return string code
   */
  public function getCode() : string
  {
    return $this->getValue( $this->cols->getCode());
  }
  
  
  /**
   * Sets the internal attribute code/name
   * @param string $value code 
   * @throws InvalidArgumentException
   */
  public function setCode( string $value ) : void
  {
    $this->setValue( $this->cols->getCode(), $value );
  }
  
  
  /**
   * Gets some arbitrary config data
   * @return array data 
   */
  public function getConfig() : array
  {
    $res = $this->getValue( $this->cols->getConfigColumn());
    
    //..Because this can be null
    if ( !is_array( $res ))
      return [];
    
    return $res;
    
  }
  
  
  /**
   * Sets some arbitrary config data
   * @param array $config data
   * @return void
   */
  public function setConfig( array $config ) : void
  {
    $this->setValue( $this->cols->getConfigColumn(), $config );
  }
  
  
  /**
   * Get the tag value 
   * @return string tag 
   */
  public function getTag() : string
  {
    return $this->getValue( $this->cols->getTagColumn());
  }
  
  
  /**
   * Set the tag value 
   * @param string $tag tag
   * @return void
   */
  public function setTag( string $tag ) : void
  {
    $this->setValue( $this->cols->getTagColumn(), $tag );
  }
  
  
  /**
   * Convert this to a config array entry.
   * Merge this with other attributes 
   * @return array array entry 
   */
  public function toConfigArray() : array
  {
    $out = [
      DefaultPropertyConfig::TYPE => $this->getPropertyType()->value(),
      DefaultPropertyConfig::VALUE => $this->getDefaultValue(),
      DefaultPropertyConfig::FLAGS => $this->getFlags()->getActiveMembers()
    ];
    
    $id = $this->getId();
    $clazz = $this->getPropertyClass();
    $min = $this->getMin();
    $max = $this->getMax();
    $pattern = $this->getPattern();
    $b = $this->getBehavior();
    if ( $b != null )
    {
      $getter = $b->getGetter();
      $setter = $b->getSetter();
      $bVal = $b->getValidate();
      $prepare = $b->getPrepare();
    }
    else
    {
      $setter = '';
      $getter = '';
      $bVal = '';
      $prepare = '';
    }
    
    $caption = $this->getCaption();
    $config = $this->getConfig();
    
    
    if ( !empty( $id ))
      $out[DefaultPropertyConfig::ID] = $id;
    
    if ( !empty( $clazz ))
      $out[DefaultPropertyConfig::CLAZZ] = $clazz;
    
    if ( !empty( $min ))
      $out[DefaultPropertyConfig::MIN] = $min;
    
    if ( !empty( $max ))
      $out[DefaultPropertyConfig::MAX] = $max;
    
    if ( !empty( $pattern ))
      $out[DefaultPropertyConfig::PATTERN] = $pattern;
    
    if ( $getter instanceof \Closure )
      $out[DefaultPropertyConfig::GETTER] = $getter;
    
    if ( !empty( $setter ))
      $out[DefaultPropertyConfig::SETTER] = $setter;
    
    if ( !empty( $bVal ))
      $out[DefaultPropertyConfig::VALIDATE] = $bVal;
    
    if ( !empty( $prepare ))
      $out[DefaultPropertyConfig::INIT] = $prepare;
    
    if ( !empty( $caption ))
      $out[DefaultPropertyConfig::CAPTION] = $caption;

    
    if ( !empty( $config ))
      $out[DefaultPropertyConfig::CONFIG] = $config;
    
    return [$this->getCode() => $out];
  }
}
