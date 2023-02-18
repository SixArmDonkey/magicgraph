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

use buffalokiwi\buffalotools\types\IEnum;


/**
 * A property backed by an IEnum instance.
 * This accepts any valid values for the store IEnum and will throw an exception 
 * when attempting to set invalid values.
 * 
 * IProperty::getValue() and IProperty::setValue() will both work with string
 * values for the enum.
 */
interface IEnumProperty extends IProperty
{
  /**
   * Retrieve a clone of the stored enum value.
   * @return IEnum stored enum 
   */
  public function getValueAsEnum() : IEnum;
}
