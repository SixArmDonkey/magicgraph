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

interface IConfigMapper
{
  /**
   * Take a config array and convert it to a list of IProperty instances.
   * If anything is wrong, exceptions get thrown.
   * @param array $config
   * @return array IProperty[] list of properties defined in $config 
   * @throws \InvalidArgumentException
   * @throws \Exception 
   */
  public function map( array $config ) : array;  
}
