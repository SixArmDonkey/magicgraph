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

namespace buffalokiwi\magicgraph\property\htmlproperty;

use InvalidArgumentException;

class FancyCheckboxElement extends Element
{
  public function __construct( string $name, string $id, string $value, array $attributes = [] )
  {
    $attributes['type'] = 'checkbox';
    
    if ( $value !== '' )
      $attributes['value'] = $value;
    
    if ( !isset( $attributes['class'] ))
      $attributes['class'] = '';
    
    $attributes['class'] .= ' checkbox';
    
    parent::__construct( 'input', $name, $id, $attributes );
  }
  
  
  /**
   * Convert this element into HTML.
   * @return string html element 
   */
  public function build() : string
  {
    return sprintf( '<label><%1$s %2$s /><span></span></label>',
      $this->getElement(),
      $this->getAttributeString()
    );
  }
}

