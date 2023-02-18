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

class TextAreaElement extends Element
{
  private string $value;
  
  public function __construct( string $name, ?string $id, string $value, array $attributes = [] )
  {
    if ( !empty( $value ))
      $attributes['value'] = $value;
    $this->value = $value;
    
    parent::__construct( 'textarea', $name, $id, $attributes );
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
      $this->value
    );
  }
}

