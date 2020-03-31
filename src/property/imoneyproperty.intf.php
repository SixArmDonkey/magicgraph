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

use buffalokiwi\magicgraph\money\IMoney;




/**
 * A property backed by an IMoney instance 
 */
interface IMoneyProperty extends IProperty
{
  /**
   * Get the value as a money.
   */
  public function getValueAsMoney() : IMoney;
}
