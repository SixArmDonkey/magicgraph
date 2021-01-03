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

use buffalokiwi\magicgraph\property\IPropertySet;
use InvalidArgumentException;


/**
 * For some given input, build a query that can be run against some persistence engine.
 */
interface ISearchQueryGenerator 
{
  /**
   * Builds a MySQL query used to search an Search system.
   * 
   * Currently this is limited to where conditions like:
   * 
   * ( cond1=x and cond2=y ) or cond3=z or cond4=z1
   * 
   * The "in" conditions can do things like this
   * 
   * ( cond1=x and cond2 in (y,z))
   * 
   * It is worth noting that an attribute must be unique to the type of condition, but can be used in multiple conditions:
   * 
   * ( cond1=x and cond1 like '%foo%' and cond1 in (y,z)) or cond1=x or cond1 like '%foo%' or cond1 in (y,z)
   * 
   * The above is valid, and obviously would return nothing unless x and y (and/or z) equal 'foo'.
   * 
   * @param ISearchQueryBuider $builder input 
   * @param bool $returnCount When true, this returns a single column: "count" containing the total number of results.
   * @return IQueryBuilderOutput query as a string and bindings 
   */
  public function createQuery( ISearchQueryBuilder $builder, bool $returnCount = false ) : IQueryBuilderOutput;
  
  
  /**
   * Retrieve the property set used when searching linked types (Join Filters).
   * @param string $name Trigger property name (What the IJoinFilter was registered as )
   * @return IPropertySet Property set created by the host repo attached to the join filter.
   * @throws InvalidArgumentException If a join filter by the specified name does not exist
   */
  public function getJoinedPropertySet( string $name ) : IPropertySet;  
  
  
  /**
   * Retrieve the schema 
   * @return array schema 
   */
  public function getSchema() : array;  
}
