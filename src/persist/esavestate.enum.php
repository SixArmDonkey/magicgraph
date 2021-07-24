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

namespace buffalokiwi\magicgraph\persist;

use buffalokiwi\buffalotools\types\Enum;


class ESaveState extends Enum
{
  const NONE = 'none';
  const VALIDATE = 'validate';
  const BEFORE_SAVE = 'before_save';
  const SAVE = 'save';
  const AFTER_SAVE = 'after_save';  
  
  protected string $value = self::NONE;
}

