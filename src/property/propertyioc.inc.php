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

use Exception;


class PropertyIoC extends PropertyTypeIoC implements IPropertyIoC
{
  public function create( IPropertyBuilder $builder ) : IProperty
  {
    $f = $this->getFactoryFunction( $builder->getType()->value());
    $prop = $f( $builder );
    if ( !( $prop instanceof IProperty ))
      throw new Exception( sprintf( 'Property factory function for property type %s does not return an instance of %s.', $type, IProperty::class ));
    
    return $prop;
  }
}
