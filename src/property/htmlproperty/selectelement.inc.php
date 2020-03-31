<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

declare( strict_types=1 );

namespace buffalokiwi\magicgraph\property\htmlproperty;

use InvalidArgumentException;

class SelectElement extends Element
{
  /**
   * Options array 
   * value => text 
   * @var string
   */
  private array $options;
  
  /**
   * Selected option value 
   * @var string[]
   */
  private array $selectedValue;
  
  
  public function __construct( string $name, string $id, string $value, array $options, array $attributes = [] )
  {
    if ( empty( $options ))
      throw new InvalidArgumentException( 'Options must not be empty' );
    
    
    foreach( $options as $k => $v )
    {
      
      if ( !is_scalar( $k ) || is_bool( $k ))
        throw new InvalidArgumentException( 'Option value (options array key) must be a scalar and non-boolean' );
      else if ( !is_string( $v ) && !( is_array( $v ) && sizeof( $v ) == 2 ))
        throw new InvalidArgumentException( 'Option text (options array value) must be a string or array with 2 elements' );
    }
    
    $this->options = $options;
    $this->selectedValue = explode( ',', $value );
    
    parent::__construct( 'select', $name, $id, $attributes );
  }
  
  
  /**
   * Convert this element into HTML.
   * @return string html element 
   */
  public function build() : string
  {
    return sprintf( '<%1$s %2$s >%3$s</%1$s>',
      $this->getElement(),
      $this->getAttributeString(),
      $this->buildOptionsString()
    );
  }  
  
  
  private function buildOptionsString() : string
  {
    $fmt = '<option value="%1$s" %3$s>%2$s</option>';
    $a = $this->options;
    
    array_walk( $a, function( &$item, $key ) use($fmt) {
      if ( is_array( $item ))
      {
        $attrs = $item[1];
        $item = $item[0];
      }
      else
        $attrs = '';
      
      $item = sprintf( 
        $fmt, 
        htmlspecialchars((string)$key, ENT_COMPAT | ENT_HTML5 ), 
        htmlspecialchars((string)$item, ENT_COMPAT | ENT_HTML5 ),
        ( in_array( $key, $this->selectedValue )) ? 'selected="selected" ' . $attrs : $attrs );        
    });
    
    return implode( '', $a );
  }
}

