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

use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertySet;


/**
 * This doesn't build the query.  This is a container, which holds information about how to build a query.
 * This is passed to some ISearchQuery instance.
 * 
 * 
 * The builder needs to accept plugins for joins.
 * 
 * The idea is to have an attribute name, the linking data and a property set maybe.
 * 
 * getName() : string - get property name
 * getLinkTableName() : string 
 * getTargetTableName() : string
 * getLinkEntityColumnName()
 * getLinkTargetColumnName()
 * getTargetPropertySet()
 * getTargetPropertyConfigInterfaceName()
 * 
 * 
 */
interface ISearchQueryBuilder
{
  /**
   * Adds an "and" equals condition.
   * @param string $attribute Attribute code
   * @param string $value Attribute value 
   * @return ISearchQueryBuilder this 
   */
  public function and( string $attribute, ?string $value, string $operator = self::EQUALS ) : ISearchQueryBuilder;
  
  
  /**
   * Adds a map of column => value used as "and" conditions.
   * @param array $map column => value
   * @return ISearchQueryBuilder this 
   */
  public function andAll( array $map ) : ISearchQueryBuilder;
  
  
  /**
   * Adds an "and" "in" condition.
   * @param string $attribute Attribute code 
   * @param array $value List of possible values 
   * @return ISearchQueryBuilder this 
   */
  public function andIn( string $attribute, array $value ) : ISearchQueryBuilder;
  
  
  /**
   * Adds an "or" equals condition.
   * @param string $attribute Attribute code
   * @param string $value Attribute value 
   * @return ISearchQueryBuilder this 
   */
  public function or( string $attribute, ?string $value, string $operator = self::EQUALS ) : ISearchQueryBuilder;
  
  
  /**
   * Adds an "or" "in" condition.
   * @param string $attribute Attribute code 
   * @param array $value List of possible values 
   * @return ISearchQueryBuilder this 
   */
  public function orIn( string $attribute, array $value ) : ISearchQueryBuilder;
  
  
  /**
   * 
   * @param IPropertySet $entityProperties
   * @throws SearchException 
   */
  public function validate( IPropertySet $entityProperties, IProperty ...$prefixProps ) : void;
  
  
  /**
   * If this query is a wildcard search 
   * @return bool is wild 
   */
  public function isWild() : bool;
  
  
  /**
   * Retrieve the attribute names in this query 
   * @return array names 
   */
  public function getAttributes() : array;
  
  
  /**
   * Adds one or more attributes to the select statement 
   * @param string $name property name 
   * @return void
   */
  public function addAttribute( string ...$name ) : void;
  
  
  /**
   * Returns an array with everything.
   * @return array
   * [
   *   'and' => [
   *     'equals' => [equals conditions],
   *     'in' => [in conditions],
   *     'like' => [like conditions] 
   *   ],
   *   
   *   'or' => [
   *     'equals' => [equals conditions],
   *     'in' => [in conditions],
   *     'like' => [like conditions] 
   *   ]
   * ]
   * 
   * 
   */
  public function getConditions() : array;  
  
  
  /**
   * Retrieve a list of attributes used within the condition lists
   * @return array attribute codes 
   */
  public function getConditionAttributes() : array;  
  
  
  /**
   * Sets the page number 
   * @param int $page page number 
   * @return void
   */
  public function setPage( int $page = 1 ) : ISearchQueryBuilder;
  
  
  /**
   * Sets the result set size 
   * @param int $size size 
   * @return void
   */
  public function setResultSize( int $size = 50 ) : ISearchQueryBuilder;
  
  
  /**
   * Retrieve the page number 
   * @return int page 
   */
  public function getPage() : int;
  
  
  /**
   * Retrieve the result set size 
   * @return int size 
   */
  public function getResultSize() : int;
  
  
  /**
   * Toggle limiting the result set.
   * If this is false, then page and size should not be used.
   * @param bool $enabled Enabled 
   * @return ISearchQueryBuilder this 
   */
  public function setLimitEnabled( bool $enabled ) : ISearchQueryBuilder;
    
  
  /**
   * If limit is enabled 
   * @return bool enabled 
   */
  public function isLimitEnabled() : bool;  
  
  
  /**
   * Sort order 
   * @return string order by column name  
   */
  public function getOrder() : string;


  /**
   * Sets the sort order 
   * @param string $order column name 
   * @return void
   */
  public function setOrder( string $order ) : ISearchQueryBuilder;
  
  
  /**
   * When building entities based on columns returned by a query, this can be used to
   * group those columns together based on column values.
   * 
   * Entity groups will be a list of column names (with linked model prefixes as necessary).
   * 
   * ie:
   * 
   * Say the query returns columns A, B, and Model2.B where A is the entity id, B is some other property, and Model2.B is the joined table.
   * We can add "Model2.B" to the entity groups list, and the columns returned by the query will be grouped by 
   * the value of Model2.B and the entity id.
   * 
   * So, say the rows returned by the query are:
   * 
   * A = 1
   * B = 'foo'
   * Model2.B = 1
   * A = 1
   * B = 'bar'
   * Model2.B = 2
   * A = 2
   * B = 'baz'
   * Model2.B = 2
   * 
   * The resulting objects would be:
   * 
   * A = 1
   * B = 'foo'
   * Model2.B = 1
   * 
   * A = 1
   * B = 'bar'
   * Model2.B = 2
   * 
   * A = 2
   * B = 'baz'
   * Model2.B = 2
   * 
   * Without the grouping, the resulting objects would be:
   * 
   * A = 1
   * B = 'bar'
   * Model2.B = 2
   * 
   * A = 2
   * B = 'baz'
   * Model2.B = 2
   * 
   * 
   * @return array column names for grouping
   */
  public function getEntityGroups() : array;
  
  
  /**
   * Adds a list of column names to the entity groups list 
   * @param string $columnNames column names 
   * @return void
   */
  public function addEntityGroups( string ...$columnNames ) : void;  
  
  
  /**
   * Return the character used as a wildcard.
   * This may change depending on the persistence layer
   * @return string character
   */
  public function getWildcardChar() : string;
}
