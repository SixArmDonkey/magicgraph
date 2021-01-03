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

namespace buffalokiwi\magicgraph\search;

use buffalokiwi\magicgraph\IModel;


/**
 * Defines search results when using the IRepository::search() method.
 */
interface ISearchResults 
{
  /**
   * Retrieve the total number of results in the set.
   * @return int result set size 
   */
  public function getCount() : int;
  
  
  /**
   * Retrieves a list of results.
   * @return IModel[] results 
   */
  public function getResults() : array;
  
  
  /**
   * Get the result set page number 
   * @return int page 
   */
  public function getPage() : int;
  
  
  /**
   * Get the total number of results per page.
   * @return int page size 
   */
  public function getSize() : int;
}
