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
use InvalidArgumentException;


/**
 * Create IPropertyInstances
 */
interface IPropertyFactory
{
  /**
   * Retrieve a list of available property types  
   * @return array string[] type list 
   */
  public function getTypes() : array;

    
  /**
   * Create an IProperty instance using the supplied property builder.  The type of property is determined
   * by the type returned by IPropertyBuilder::getType()
   * @param IPropertyBuilder $builder Buillder
   * @return IProperty New property 
   * @throws InvalidArgumentException
   * @throws Exception
   */
  public function createProperty( IPropertyBuilder $builder ) : IProperty;
}
