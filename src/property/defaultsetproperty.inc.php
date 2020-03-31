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

namespace buffalokiwi\magicgraph\property;


class DefaultSetProperty extends SetProperty
{
  public function __construct( string $clazz, string $name, array $defaultValue = [], IPropertyBehavior $behavior = null, ...$flags )
  {
    $b = new ObjectPropertyBuilder( EPropertyType::TSET(), new SPropertyFlags( ...$flags ));
    $b->setName( $name );
    $b->setDefaultValue( $defaultValue );
    $b->addBehavior( $behavior );
    $b->setClass( $clazz );
    parent::__construct( $b );
  }
}
