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
 * Used when you need to create a search results object from a finite list of data.
 */
class ArraySearchResults implements ISearchResults
{
  /**
   * Results array 
   * @var IModel[]
   */
  private array $results;
  
  
  /**
   * Array Search Results 
   * @param IModel $results Results 
   */
  public function __construct( IModel ...$results )
  {
    $this->results = $results;
  }
  
  
  /**
   * Retrieve the total number of results in the set.
   * @return int result set size 
   */
  public function getCount() : int
  {
    return sizeof( $this->results );
  }
  
  
  /**
   * Retrieves a list of results.
   * @return IModel[] results 
   */
  public function getResults() : array
  {
    return $this->results;
  }
  
  
  /**
   * Get the result set page number 
   * @return int page 
   */
  public function getPage() : int
  {
    return 1;
  }
  
  
  /**
   * Get the total number of results per page.
   * @return int page size 
   */
  public function getSize() : int
  {
    return sizeof( $this->results );
  }
}

