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

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\persist\IRepository;
use InvalidArgumentException;

/**
 * Attribute value repo 
 */
interface IAttrValueRepo extends IRepository
{
  /**
   * Retrieve a list of attribute values for some list of entity id's.
   * @param int $entityId One or more entity ids 
   * @return array [entity id => [attr id => value]]
   * @throws InvalidArgumentException 
   */
  public function getAttributeValues( int ...$entityId ) : array;  
}
