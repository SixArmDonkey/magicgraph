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


/**
 * A factory that produces property builder instances used to build IProperty instances.
 * 
 * When creating IProperty instances of a specific data type, pass a IPropertyType enum value 
 * to create #arg2, and a builder of the appropriate data type will be returned.  
 * 
 */
interface IPropertyBuilderFactory
{
  /**
   * Retrieve a list of available property types  
   * @return array string[] type list 
   */
  public function getTypes() : array;

    
  /**
   * Create a property builder used to create IProperty instances of the supplied type 
   * @param string $name Property name
   * @param string $type Property data type 
   * @return IPropertyBuilder The builder 
   */
  public function create( string $name, string $type ) : IPropertyBuilder;
}
