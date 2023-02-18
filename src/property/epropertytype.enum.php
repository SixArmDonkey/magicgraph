<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */



namespace buffalokiwi\magicgraph\property;

use buffalokiwi\buffalotools\types\Enum;


/**
 * Property value type definitions.
 * Used to specify a data type for IProperty instances 
 */
class EPropertyType extends Enum implements IPropertyType
{
  protected string $value = self::TSTRING;
}
