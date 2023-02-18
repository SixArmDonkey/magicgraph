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
 * An integer property that can store an integer.
 * Can be bounded by min/max configuration.
 */
interface IIntegerProperty extends IBoundedProperty
{
  /**
   * Retrieve the stored value as an integer
   * @return int value 
   */
  public function getValueAsInt() : int;
}
