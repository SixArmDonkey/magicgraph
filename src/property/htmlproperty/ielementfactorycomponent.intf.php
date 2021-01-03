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

namespace buffalokiwi\magicgraph\property\htmlproperty;

use buffalokiwi\magicgraph\property\IProperty;


/**
 * Element factory component.
 * Used to create instances of IElement when an IProperty instance implements an interface returned by getInterface().
 */
interface IElementFactoryComponent
{
  /**
   * Retrieve the property interface name.
   * This is matched against instances of IProperty.  If they match, then createElement() is called.
   * @return string
   */
  public function getInterface() : string;
  
  
  /**
   * Create an element based on the defined property type.
   * @param string $name Input name property value 
   * @param string $id Element id value 
   * @return IElement Element 
   * @throws HTMLPropertyException 
   */
  public function createElement( IProperty $prop, string $name, ?string $id, string $value ) : IElement;
}
