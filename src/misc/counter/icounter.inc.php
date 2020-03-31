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


namespace buffalokiwi\magicgraph\misc\counter;

/**
 * Can increment a number by some offset 
 */
interface ICounter
{
  /**
   * Increment a stored number by some offset and return the new value.
   * @param string $key Counter key 
   * @return int value 
   */
  public function increment( string $key ) : int;
}
