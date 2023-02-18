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
class USDollarProperty extends \buffalokiwi\magicgraph\property\MoneyProperty
{
  public function __construct( string $name, ?string $defaultValue = '', array $flags = [], string $behaviorClass = '', string $flagsClass = SPropertyFlags::class )
  {
    $bc = ( !empty( $behaviorClass )) ? new $behaviorClass() : null;
    $fc = new $flagsClass( ...$flags );
    
    parent::__construct( new \buffalokiwi\magicgraph\property\BoundedPropertyBuilder( EPropertyType::TMONEY(), $fc, $name, $defaultValue, $bc ), \buffalokiwi\magicgraph\money\USDollarFactory::getInstance());
  }  
}

