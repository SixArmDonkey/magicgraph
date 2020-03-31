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

use \Exception;



class PropertyBuilderIoC extends PropertyTypeIoC implements IPropertyBuilderIoC
{
  public function create( string $name, string $type ) : IPropertyBuilder
  {
    
    $f = $this->getFactoryFunction( $type );
    $prop = $f();
    
    
    if ( !( $prop instanceof IPropertyBuilder ))
      throw new Exception( sprintf( 'Property factory function for property type %s does not return an instance of %s.', $type, IPropertyBuilder::class ));
    else if ( $prop->getType()->value() != $type )
    {
      debug_print_backtrace();
      throw new Exception( sprintf( 'Property factory function for property type %s (%s) does not return an instance of %s with a type set to %s. got %s', $type, $name, IPropertyBuilder::class , $type, get_class( $prop )));
    }
    
    return $prop;
  }    
}
