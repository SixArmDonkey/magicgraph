<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


class ValuedEnum extends \buffalokiwi\buffalotools\types\Enum
{
  const KEY1 = 'key1';
  const KEY2 = 'key2';
  
  const VALUE1 = 'value1';
  const VALUE2 = 'value2';
  
  protected array $enum = [
    self::KEY1 => self::VALUE1, 
    self::KEY2 => self::VALUE2
  ];
}
