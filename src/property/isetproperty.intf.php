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

use buffalokiwi\buffalotools\types\ISet;


/**
 * Defines a property backed by an ISet instance.
 * 
 * This accepts any valid member value string OR an integer value for the stored 
 * ISet, and will throw an exception when attempting to set invalid values.
 * 
 * IProperty::getValue() and IProperty::setValue() will both work with string
 * values or integer values for the set.
 */
interface ISetProperty extends IProperty
{
  /**
   * Retrieve a clone of the stored ISet instance.
   * @return ISet The set 
   */
  public function getValueAsSet() : ISet;
}
