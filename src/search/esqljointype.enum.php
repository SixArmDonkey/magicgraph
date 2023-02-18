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

namespace buffalokiwi\magicgraph\search;

use buffalokiwi\buffalotools\types\Enum;


/**
 * A type for SQL joins.
 * Eh. I guess.
 */
class ESQLJoinType extends Enum implements ISQLJoinType
{
  protected array $enum = [
    self::INNER,
    self::LEFT
  ];

  protected string $value = self::INNER;  
}
