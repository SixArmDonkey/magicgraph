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
 * Can be used to define groups of conditions:
 * 
 * (`column` = value and (`column2` = 'value' or `column3` = 'value' ))
 * @deprecated Stupid
 */
interface ISQLConditionGroup extends ICondition
{
  /**
   * Add some condition to the group 
   * @param ICondition $condition
   * @param bool $or
   */
  public function addCondition( ICondition $condition, bool $or = false );
}
