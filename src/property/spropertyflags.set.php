<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */



namespace buffalokiwi\magicgraph\property;

use buffalokiwi\buffalotools\types\Set;


/**
 * A BitSet implementation containing valid flags for IProperty instances 
 */
class SPropertyFlags extends Set implements IPropertyFlags
{
  const MEMBERS = [
    self::PRIMARY,
    self::USE_NULL,
    self::REQUIRED,
    self::NO_UPDATE,
    self::NO_INSERT,
    self::SUBCONFIG,
    self::WRITE_EMPTY,
    self::NO_ARRAY_OUTPUT
  ];
}
