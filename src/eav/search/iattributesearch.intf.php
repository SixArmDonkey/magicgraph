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

namespace buffalokiwi\magicgraph\eav\search;

use buffalokiwi\magicgraph\search\ISearchQueryBuilder;
use buffalokiwi\magicgraph\search\ISearchQueryGenerator;
use buffalokiwi\magicgraph\search\ISearchResults;


/**
 * An attribute search will search for entity or attribute values, and return a map of attribute code => value.
 * These results can be used to build models.
 */
interface IAttributeSearch
{
  /**
   * Retrieve the search query generator 
   * @return ISearchQueryGenerator generator 
   */
  public function getSearchQueryGenerator() : ISearchQueryGenerator;
  
  
  /**
   * Search for things.
   * An attribute search will search for entity or attribute values, and return a map of attribute code => value.
   * These results can be used to build models.
   * @param ISearchQueryBuilder $builder Search builder
   * @return ISearchResults results 
   */
  public function search( ISearchQueryBuilder $builder ) : ISearchResults;
}

