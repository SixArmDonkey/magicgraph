<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\persist;


/**
 * Filters used for SQL repositories 
 * @deprecated Stupid
 */
interface ISQLFilter extends IFilter
{
  /**
   * Adds a where condition 
   * @param ICondition $condition Condition to add
   * @param bool $or Separate by "or" instead of "and"
   */
  public function addWhere( ICondition $condition, bool $or = false ) : void;

}
