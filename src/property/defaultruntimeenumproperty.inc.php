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


class DefaultRuntimeEnumProperty extends RuntimeEnumProperty
{
  public function __construct( array $config, string $name, string $defaultValue, IPropertyBehavior $behavior = null, ...$flags )
  {
    $b = new PropertyBuilder( EPropertyType::TRTENUM(), new SPropertyFlags( ...$flags ), $name, $defaultValue, $behavior );
    $b->setConfig( $config );
    parent::__construct( $b );
  }
}
