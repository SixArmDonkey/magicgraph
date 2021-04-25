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

use buffalokiwi\magicgraph\property\BoundedPropertyBuilder;
use buffalokiwi\magicgraph\property\EPropertyType;
use buffalokiwi\magicgraph\property\SPropertyFlags;
use \Attribute;


#[Attribute(Attribute::TARGET_PROPERTY)]
class StringProperty extends \buffalokiwi\magicgraph\property\StringProperty
{
  public function __construct( string $name, ?string $defaultValue = '', array $flags = [], string $behaviorClass = '', string $flagsClass = SPropertyFlags::class )
  {
    $bc = ( !empty( $behaviorClass )) ? new $behaviorClass() : null;
    $fc = new $flagsClass( ...$flags );
    
    parent::__construct( new \buffalokiwi\magicgraph\property\StringPropertyBuilder( EPropertyType::TSTRING(), $fc, $name, $defaultValue, $bc ));
  }  
}

