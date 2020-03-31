<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\property;


interface IPropertyFactory
{
  /**
   * Retrieve a list of properties 
   * @param IPropertyConfig $config One or more configuration instances.
   * @return IProperty[] properties
   */
  public function getProperties( IPropertyConfig ...$config ) : array;
}
