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


/**
 * A property backed by a float that can be bounded by min/max settings.
 */
interface IFloatProperty extends IBoundedProperty
{
  /**
   * Retrieve the stored value as a float 
   * @return float value 
   */
  public function getValueAsFloat() : float;
}
