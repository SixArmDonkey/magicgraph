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

use InvalidArgumentException;



/**
 * The built query from a mysql eav query builder.
 * This is potentially dangerous.
 * The object will contain a sql statement to be executed elsewhere.  
 * Be very careful about where elsewhere actually is.
 * 
 * This will return entity data attached to each row, and each row will contain information for a single 
 * subconfig attribute.
 * 
 * These results will need to be merged and built.
 * 
 * @todo This class doesn't do anything other than pass data.  Figure out how to remove this.
 */
class MySQLQueryBuilderOutput implements IQueryBuilderOutput
{
  /**
   * The sql statement 
   * @var string
   */
  private string $sql;
  
  /**
   * Bindings 
   * @var array
   */
  private array $values;
  
  /**
   * A unique id property name 
   * @var string
   */
  private string $uniqueId;
  
  
  /**
   * The search query builder used to create this output object 
   * @var ISearchQueryBuilder
   */
  private ISearchQueryBuilder $builder;
  
  
  /**
   * MySQLQueryBuilderOutput
   * @param string $uniqueId The unique id property name used to identify the entity.
   * @param string $sql SQL statement 
   * @param array $values Binding values 
   * @throws \InvalidArgumentExcption 
   */
  public function __construct( ISearchQueryBuilder $builder, string $uniqueId, string $sql, array $values )
  {
    if ( empty( trim( $sql )))
      throw new InvalidArgumentException( 'sql must not be empty' );
    else if ( empty( trim( $uniqueId )))
      throw new InvalidArgumentException( 'uniqueId must not be empty' );
    
    $this->builder = $builder;
    $this->uniqueId = $uniqueId;
    $this->sql = $sql;
    $this->values = $values;
  }
  
  
  /**
   * Retrieve the unique id property name 
   * @return string unique id
   */
  public function getUniqueId() : string
  {
    return $this->uniqueId;
  }
  
  
  
  /**
   * Retrieve the query text
   * @return string text
   */
  public function getQuery() : string
  {
    return $this->sql;
  }
  
  
  /**
   * Retrieve the query values used for parameter binding.
   * This may be empty depending on the builder type.
   * @return array
   */
  public function getValues() : array
  {
    return $this->values;
  } 
  
  
  /**
   * Retrieve the search query builder used to create this object 
   * @return ISearchQueryBuilder builder
   */
  public function getSearchQueryBuilder() : ISearchQueryBuilder
  {
    return $this->builder;
  }
}
