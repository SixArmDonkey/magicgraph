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

use buffalokiwi\buffalotools\date\DateFactory;
use buffalokiwi\magicgraph\property\EPropertyType;
use buffalokiwi\magicgraph\property\PropertyBuilder;
use buffalokiwi\magicgraph\property\SPropertyFlags;
use \Attribute;


#[Attribute]
class DateProperty extends \buffalokiwi\magicgraph\property\DateProperty
{
  public function __construct( string $name, string $defaultValue = '', string $toStringFormat = 'Y-m-d H:i:s', 
    array $flags = [], string $behaviorClass = '', string $flagsClass = SPropertyFlags::class )
  {
    $bc = ( !empty( $behaviorClass )) ? new $behaviorClass() : null;
    $fc = new $flagsClass( ...$flags );
    
    $df = DateFactory::getInstance();
    $def = ( empty( $defaultValue )) ? $df->now() : $defaultValue;
    
    parent::__construct( new PropertyBuilder( EPropertyType::TDATE(), $fc, $name, $def, $bc ), $df );
  }
}
