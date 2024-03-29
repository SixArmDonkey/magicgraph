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


class DefaultFloatProperty extends FloatProperty
{
  public function __construct( string $name, float $defaultValue = 0, IPropertyBehavior $behavior = null, ...$flags )
  {
    parent::__construct( new BoundedPropertyBuilder( EPropertyType::TFLOAT(), new SPropertyFlags( ...$flags ), $name, $defaultValue, $behavior ));
  }
}
