<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\property;


/**
 * A mapped property factory is used to convert a model property configuration array 
 * into a set of IProperty instances.  This should utilize the IConfigMapper 
 */
interface IMappedPropertyFactory
{
  /**
   * Retrieve a list of properties 
   * @param IPropertyConfig $config One or more configuration instances.
   * @return IProperty[] properties
   */
  public function getProperties( IPropertyConfig ...$config ) : array;
  
  
  /**
   * Retrieve the config mapper used to create IProperty instances from 
   * config arrays.
   * @return IConfigMapper Mapper 
   */
  public function getMapper() : IConfigMapper;  
}
