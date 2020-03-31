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


class DefaultStringProperty extends StringProperty
{
  public function __construct( string $name, ?string $defaultValue = '', IPropertyBehavior $behavior = null, ...$flags )
  {
    parent::__construct( new StringPropertyBuilder( EPropertyType::TSTRING(), new SPropertyFlags( ...$flags ), $name, $defaultValue, $behavior ));
  }
}
