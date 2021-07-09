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

namespace buffalokiwi\magicgraph\property\annotation;

use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\SPropertyFlags;


#[Attribute(Attribute::TARGET_PROPERTY)]
class ReqStringProperty extends StringProperty
{
  public function __construct( string $name, ?string $defaultValue = '', array $flags = [], string $behaviorClass = '', string $flagsClass = SPropertyFlags::class )
  {
    $flags[] = IPropertyFlags::REQUIRED;
    parent::__construct( $name, $defaultValue, $flags, $behaviorClass, $flagsClass );
  }  
}

