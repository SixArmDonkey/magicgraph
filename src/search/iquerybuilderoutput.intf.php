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
 * Defines output from the query builder.
 */
interface IQueryBuilderOutput 
{
  /**
   * Retrieve the unique id property name 
   * @return string unique id
   */
  public function getUniqueId() : string;
  
  
  /**
   * Retrieve the query text
   * @return string text
   */
  public function getQuery() : string;
  
  
  /**
   * Retrieve the query values used for parameter binding.
   * This may be empty depending on the builder type.
   * @return array
   */
  public function getValues() : array;
  
  
  /**
   * Retrieve the search query builder used to create this object 
   * @return ISearchQueryBuilder builder
   */
  public function getSearchQueryBuilder() : ISearchQueryBuilder;  
}

