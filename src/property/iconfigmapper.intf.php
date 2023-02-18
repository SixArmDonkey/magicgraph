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

use Exception;
use InvalidArgumentException;


/**
 * Technically, this is a factory.
 * This will convert the standard model/property configuration array into a map of name => IProperty 
 */
interface IConfigMapper
{
  /**
   * Create an IProperty instance 
   * @param IPropertyType $type Type to create
   * @param string $name Name 
   * @param mixed $value Value to set
   * @param array $more Extra configuration options 
   * @return IProperty The new property
   * @throws InvalidArgumentException 
   */
  public function createPropertyByType( IPropertyType $type, string $name, mixed $value, array $more = [] ) : IProperty;
  
  
  /**
   * Take a config array and convert it to a list of IProperty instances.
   * If anything is wrong, exceptions get thrown.
   * @param array $config
   * @return array IProperty[] list of properties defined in $config 
   * @throws InvalidArgumentException
   * @throws Exception 
   */
  public function map( array $config ) : array;  
 
}
