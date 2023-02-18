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

namespace buffalokiwi\magicgraph\property\htmlproperty;

use buffalokiwi\magicgraph\property\IProperty;
use Closure;


/**
 * A configuration option for the ElementFactory, which provides a mapping from property type to element.
 * Essentially, for a specific type of IProperty, this will create an Element instance.
 */
class ElementFactoryComponent implements IElementFactoryComponent
{
  /**
   * Property interface 
   * @var string
   */
  private string $interface;
  
  /**
   * Supplier for creating instances of Element.
   * f( IProperty $prop, string $name, string $id, string $value ) : IElement 
   * @var Closure
   */
  private Closure $elementSupplier;
  
  
  /**
   * 
   * @param string $interface
   * @param Closure $elementSupplier Creates instances of IElement based on property interface.
   * f() : IElement 
   */
  public function __construct( string $interface, Closure $elementSupplier )
  {
    $this->interface = $interface;
    $this->elementSupplier = $elementSupplier;
  }
  
  
  /**
   * Retrieve the property interface name.
   * This is matched against instances of IProperty.  If they match, then createElement() is called.
   * @return string
   */
  public function getInterface() : string
  {
    return $this->interface;
  }
  
  
  /**
   * Create an element based on the defined property type.
   * @param string $name Input name property value 
   * @param string $id Element id value 
   * @return IElement Element 
   * @throws HTMLPropertyException 
   */
  public function createElement( IProperty $prop, string $name, ?string $id, string $value ) : IElement
  {
    $f = $this->elementSupplier;
    $res = $f( $prop, $name, $id, $value );
    
    if ( !( $res instanceof IElement ))
    {
      $type = ( is_object( $res )) ? get_class( $res ) : gettype( $res );
      throw new HTMLPropertyException( 'Element factory component supplier did not return an instance of IElement.  got ' . $type );
    }
    
    return $res;
  }
}
