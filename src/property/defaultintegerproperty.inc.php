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


class DefaultIntegerProperty extends IntegerProperty
{
  public function __construct( string $name, float $defaultValue = 0, IPropertyBehavior $behavior = null, ...$flags )
  {
    parent::__construct( new BoundedPropertyBuilder( EPropertyType::TINTEGER(), new SPropertyFlags( ...$flags ), $name, $defaultValue, $behavior ));
  }
}
