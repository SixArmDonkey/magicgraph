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

use DateTimeInterface;

/**
 * A property backed by a DateTime object 
 */
interface IDateProperty extends IProperty
{
  /**
   * Retrieve the stored value as a DateTime object 
   * @return DateTimeInterface value 
   */
  public function getValueAsDateTime() : DateTimeInterface;  
}
