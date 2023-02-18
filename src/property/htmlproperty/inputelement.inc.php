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

class InputElement extends Element
{
  public function __construct( string $type, string $name, ?string $id, string $value, array $attributes = [] )
  {
    if ( empty( $type ))
      throw new InvalidArgumentException( 'type must not be empty' );
    
    $attributes['type'] = $type;
    
    if ( $value !== '' )
      $attributes['value'] = $value;
    
    parent::__construct( 'input', $name, $id, $attributes );
  }
  
  
  /**
   * Convert this element into HTML.
   * @return string html element 
   */
  public function build() : string
  {
    return sprintf( '<%1$s %2$s />',
      $this->getElement(),
      $this->getAttributeString()
    );
  }
}

