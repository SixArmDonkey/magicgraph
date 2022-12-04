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
use Closure;


/**
 * MySQL search results wrapper 
 * This isn't mysql specific.  It can be renamed DefaultSearchResults
 * @todo Rename this DefaultSearchResults or FunctionalSearchResults or something.
 */
class MySQLSearchResults implements ISearchResults
{
  /**
   * Page number 
   * @var int
   */
  private int $page;
  
  /**
   * Results per page 
   * @var int 
   */
  private int $size;
  
  /**
   * Retrieve the total number of results across all pages.
   * f() : int 
   * 
   * @var Closure
   */
  private Closure $getCount;
    
  /**
   * Retrieve the results in the current page 
   * @var IModel[] 
   */
  private array $results;
  
  
  /**
   * 
   * @param int $page Page number 
   * @param int $size Page size 
   * @param Closure $getCount A function returning an integer representing the total number of possible results across all pages.  f() : int 
   * @param IModel $results Results in the current page 
   */
  public function __construct( int $page, int $size, Closure $getCount, IModel ...$results )
  {
    $this->page = $page;
    $this->size = $size;
    $this->getCount = $getCount;
    $this->results = $results;
  }
  
  
  /**
   * Retrieve the total number of results in the set.
   * @return int result set size 
   */
  public function getCount() : int
  {
    $f = $this->getCount;
    return $f();
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
    return $this->page;
  }
  
  
  /**
   * Get the total number of results per page.
   * @return int page size 
   */
  public function getSize() : int
  {
    return $this->size;
  }
}
