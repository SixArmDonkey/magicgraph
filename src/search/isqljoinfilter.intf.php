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

use buffalokiwi\magicgraph\persist\ISQLRepository;
use buffalokiwi\magicgraph\search\ISQLJoinType;


/**
 * This is used with MySQLEavSearch, and can be used to add table joins to the entity selection part of the 
 * search query.
 * 
 * It is CRITICAL that the ISQLJoinFilter instances used with the ISearchQueryGenerator validate each supplied 
 * property name during query generation.  Failure to validate column names will lead to SQL injection vulnerabilities.
 * ISearchQueryBuilder instances will NOT validate linked property names.
 */
interface ISQLJoinFilter extends IJoinFilter
{
  /**
   * Retrieve the backing repository that manages the linked data.
   * @return ISQLRepository|null repo
   */
  public function getHostRepo() : ?ISQLRepository;
  
  
  /**
   * 
   * @param string $parentIdColumn
   * @param string $alias
   * @return string
   * @throws InvalidArgumentException
   */
  public function getJoin( string $parentIdColumn, string $alias, ISQLJoinType $type ) : string;
  
  
  /**
   * Retrieve the where condition.
   * This does not return and/or/etc or "where".
   * @param array $values Values to include in the "in" query 
   * @return string condition sql
   * @throws InvalidArgumentException
   */
  public function getWhere( string $name, array $values, string $alias = '' ) : string;
  
  
  /**
   * Prepares a column name for use within some query.
   * This will add some sort of table or alias prefix to the property/column name.
   * @param string $name name 
   * @return string prepared name 
   */
  public function prepareColumn( string $name, string $alias = '' ) : string;
}
