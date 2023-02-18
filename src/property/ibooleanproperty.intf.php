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
 * A boolean property 
 */
interface IBooleanProperty extends IProperty
{
  /**
   * Retrieve the stored value as a boolean.
   * @return bool boolean 
   */
  public function getValueAsBoolean() : bool;
}
