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

use buffalokiwi\magicgraph\property\EPropertyType;
use buffalokiwi\magicgraph\property\ObjectPropertyBuilder;
use buffalokiwi\magicgraph\property\SPropertyFlags;
use \Attribute;


#[Attribute]
class SetProperty extends \buffalokiwi\magicgraph\property\SetProperty
{
  public function __construct( string $name, string $clazz = '', array $defaultValue = [], 
    array $flags = [], string $behaviorClass = '', string $flagsClass = SPropertyFlags::class )
  {
    $bc = ( !empty( $behaviorClass )) ? new $behaviorClass() : null;
    $fc = new $flagsClass( ...$flags );
    
    $builder = new ObjectPropertyBuilder( EPropertyType::TSET(), $fc, null );
    $builder->setName( $name );
    $builder->setDefaultValue( $defaultValue );
    $builder->addBehavior( $bc );
    $builder->setClass( $clazz );
    parent::__construct( $builder );    
  }
}

