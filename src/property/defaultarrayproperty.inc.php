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


class DefaultArrayProperty extends ArrayProperty
{
  public function __construct( string $name, array $defaultValue, IPropertyBehavior $behavior = null, ...$flags )
  {
    $builder = new ObjectPropertyBuilder( EPropertyType::TARRAY(), new SPropertyFlags( ...$flags ), null );
    $builder->setName( $name );
    $builder->setDefaultValue( $defaultValue );
    $builder->addBehavior( $behavior );
    
    parent::__construct( $builder );
  }
}
