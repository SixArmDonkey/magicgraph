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

namespace buffalokiwi\magicgraph\property;


class DefaultEnumProperty extends EnumProperty
{
  public function __construct( string $clazz, string $name, string $defaultValue, IPropertyBehavior $behavior = null, ...$flags )
  {
    $b = new ObjectPropertyBuilder( EPropertyType::TENUM(), new SPropertyFlags( ...$flags ));
    $b->setName( $name );
    $b->setDefaultValue( $defaultValue );
    $b->addBehavior( $behavior );
    $b->setClass( $clazz );
    parent::__construct( $b );
  }
}
