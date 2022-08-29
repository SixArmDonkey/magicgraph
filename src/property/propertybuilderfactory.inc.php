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


/**
 * A factory that produces property builder instances used to build IProperty instances.
 * 
 * When creating IProperty instances of a specific data type, pass a IPropertyType enum value 
 * to create #arg2, and a builder of the appropriate data type will be returned.  
 */
class PropertyBuilderFactory extends PropertyTypeFactory implements IPropertyBuilderFactory
{
  /**
   * Create a property builder used to create IProperty instances of the supplied type 
   * @param string $name Property name
   * @param string $type Property data type 
   * @return IPropertyBuilder The builder 
   */  
  public function create( string $name, string $type ) : IPropertyBuilder
  {
    $f = $this->getFactoryFunction( $type );
    $prop = $f();
    
    
    if ( !( $prop instanceof IPropertyBuilder ))
    {
      throw new Exception( sprintf( 'Property factory function for property type %s does not return an instance of %s.', 
        $type, IPropertyBuilder::class ));      
    }
    else if ( $prop->getType()->value() != $type )
    {
      /**
       * @todo getType() may be deprecated.  
       */
      //debug_print_backtrace();
      throw new Exception( sprintf( 'Property factory function for property type %s (%s) does not return an instance '
        . 'of %s with a type set to %s. got %s', $type, $name, IPropertyBuilder::class , $type, get_class( $prop )));
    }
    
    return $prop;
  }    
}
