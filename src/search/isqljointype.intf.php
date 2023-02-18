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


/**
 * A SQL join type.  
 * Whatever.
 */
interface ISQLJoinType extends \buffalokiwi\buffalotools\types\IEnum
{
  /**
   * Inner join 
   */
  const INNER = 'INNER';
  
  /**
   * Left join 
   */
  const LEFT = 'LEFT';
}
