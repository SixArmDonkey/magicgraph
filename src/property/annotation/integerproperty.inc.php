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

namespace buffalokiwi\magicgraph\property\annotation;

use buffalokiwi\magicgraph\property\BoundedPropertyBuilder;
use buffalokiwi\magicgraph\property\EPropertyType;
use buffalokiwi\magicgraph\property\SPropertyFlags;
use \Attribute;


#[Attribute(Attribute::TARGET_PROPERTY)]
class IntegerProperty extends \buffalokiwi\magicgraph\property\IntegerProperty
{
  public function __construct( string $name, int $defaultValue = 0, array $flags = [], string $behaviorClass = '', string $flagsClass = SPropertyFlags::class )
  {
    $bc = ( !empty( $behaviorClass )) ? new $behaviorClass() : null;
    $fc = new $flagsClass( ...$flags );
    
    parent::__construct( new BoundedPropertyBuilder( EPropertyType::TINTEGER(), $fc, $name, $defaultValue, $bc ));
  }  
}

