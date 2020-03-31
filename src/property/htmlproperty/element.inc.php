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

namespace buffalokiwi\magicgraph\property\htmlproperty;

use InvalidArgumentException;


/**
 * Represents an HTML element 
 */
abstract class Element implements IElement
{
  /**
   * Element tag 
   * @var string
   */
  private string $element;
  
  /**
   * Element name attribute value 
   * @var string
   */
  private string $name;
  
  /**
   * Element id attribute value 
   * @var string
   */
  private string $id;
  
  /**
   * Map of attributes
   * key => value
   * @var array 
   */
  private array $attributes = [];
  
  /**
   * Convert this element into HTML.
   * @return string html element 
   * @abstract 
   */
  abstract public function build() : string;

  
  /**
   * Create a new element
   * @param string $element element tag name 
   * @param string $name element name attribute value 
   * @param string $id element id attribute value 
   * @param array $attributes map of additional element attributes.  key => value.
   */
  public function __construct( string $element, string $name, string $id, array $attributes = [] )
  {
    $this->element = $element;
    $this->name = $name;
    $this->id = $id;
    
    if ( empty( $element ))
      throw new InvalidArgumentException( 'element must not be empty' );
    else if ( empty( $name ))
      throw new InvalidArgumentException( 'name must not be empty' );
    
    foreach( $attributes as $k => $v )
    {
      if ( !is_string( $k ))
        throw new InvalidArgumentException( 'All attribute names must be strings' );
      else if ( !is_scalar( $v ))
        throw new InvalidArgumentException( 'All attribute values must be scalar' );
      
      $k = strtolower( trim( $k ));
      
      if ( $k == 'id' || $k == 'name' )
        continue;
      
      $this->attributes[$k] = $v;
    }
    
    $this->attributes['name'] = $name;
    if ( empty( $id ))
      $this->attributes['id'] = $name;
    else
      $this->attributes['id'] = $id;
  }
  
  
  /**
   * Get the element id property value
   * @return string id 
   */
  public function getId() : string
  {
    return $this->id;
  }
  
  
  /**
   * Get the input "name" property value 
   * @return string name 
   */
  public function getName() : string
  {
    return $this->name;
  }
  
  
  /**
   * Get the element name 
   * @return string name 
   */
  public function getElement() : string
  {
    return $this->element;
  }
  
  
  /** 
   * Get the element attributes list.
   * Key = value.
   * setting id or name here will have no effect.
   * @return array attributes map 
   */
  public function getAttributes() : array
  {
    return $this->attributes;
  }
  
  
  /**
   * Retrieve the attributes as an html string.
   * @return string attributes 
   */
  public function getAttributeString() : string
  {
    $a = $this->attributes;
    array_walk( $a, function( &$value, $key ) {
      $value = $key . '="' . htmlspecialchars((string)$value, ENT_COMPAT | ENT_HTML5 ) . '"';
    });

    return implode( ' ', $a );
  }
}
