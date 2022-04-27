<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


class SampleEnum extends \buffalokiwi\buffalotools\types\Enum
{
  const KEY1 = 'value1';
  const KEY2 = 'value2';
  
  protected array $enum = [self::KEY1, self::KEY2];
}
