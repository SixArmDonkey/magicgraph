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

use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\SPropertyFlags;


#[Attribute(Attribute::TARGET_PROPERTY)]
class PrimaryIntegerProperty extends IntegerProperty
{
  public function __construct( string $name, int $defaultValue = 0, array $flags = [], string $behaviorClass = '', string $flagsClass = SPropertyFlags::class )
  {
    $flags[] = IPropertyFlags::PRIMARY;
    parent::__construct( $name, $defaultValue, $flags, $behaviorClass, $flagsClass );
  }  
}

