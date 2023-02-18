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
 * Used to define a sort order and limit for some query.
 * @deprecated Stupid
 */
interface IRows
{
  /**
   * Retrieve the attribute used to sort
   * @return string attribute name
   */
  public function getOrderBy() : string;
  
  
  /**
   * Retrieve the start offset 
   * @return int offset
   */
  public function getStart() : int;
  
  
  /**
   * Retrieve the result set size
   * @return int size
   */
  public function getRows() : int;
  
  
  /**
   * Retrieve this part as a string
   * @return string statement 
   */
  public function getStatement() : string;
}
