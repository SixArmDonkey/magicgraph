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

use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\persist\ISQLRepository;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\search\ISQLJoinFilter;
use InvalidArgumentException;


/**
 * This can search child model properties.
 * ie: when a property value is IModel or IModel[].
 * Property names are formatted as: "{parent property name}.{child property name}"
 */
class SQLJoinSearchFilter implements ISQLJoinFilter 
{
  /**
   * Junction table name 
   * @var string
   */
  private string $table;
  
  /**
   * Entity id column name 
   * @var string
   */
  private string $entityIdColumn;
    
  /**
   * Target id column name 
   * @var string 
   */
  private string $targetIdColumn;
  
  /**
   * database connection 
   * @var IDBConnection 
   */
  private IDBConnection $dbc;
  
  /** 
   * Property name 
   * @var string
   */
  private string $propertyName;
  
  /**
   * Host repo 
   * @var ISQLRepository 
   */
  private ISQLRepository $repo;
  
  /**
   * Properties 
   * @var IPropertySet
   */
  private IPropertySet $props;
  
  private string $parentIdColumn;

  
  /**
   * 
   * @param ISQLRepository $hostRepo The repository that manages the linked data
   * @param string $propertyName Trigger property name
   * @param string $entityIdColumn Entity id column name (ie: a category linked to a product.)
   * @param string $targetIdColumn Target id column name (for example product.  This is the parent id).
   * @throws InvalidArgumentException
   */
  public function __construct( ISQLRepository $hostRepo, string $propertyName, string $entityIdColumn, string $targetIdColumn, string $parentIdColumn = '' )
  {
    $pattern = '/^[A-Za-z0-9_]+/';
    if ( empty( $propertyName ))
      throw new InvalidArgumentException( 'propertyName must not be empty' );
    else if ( empty( $entityIdColumn ) || !preg_match( $pattern, $entityIdColumn ))
      throw new InvalidArgumentException( 'entityIdColumn must not be empty and must be alphanumeric' );
    else if ( empty( $targetIdColumn ) || !preg_match( $pattern, $targetIdColumn ))
      throw new InvalidArgumentException( 'targetIdColumn must not be empty and must be alphanumeric' );
    else if ( !empty( $parentIdColumn ) && !preg_match( $pattern, $parentIdColumn ))
      throw new InvalidArgumentException( 'parentIdColumn must be alphanumeric' );
    
    $this->repo= $hostRepo;
    $this->dbc = $hostRepo->getDatabaseConnection();
    $this->table = $hostRepo->getTable();
    $this->entityIdColumn = $entityIdColumn;
    $this->targetIdColumn = $targetIdColumn;
    $this->propertyName = $propertyName;
    $this->props = $hostRepo->createPropertySet();
    $this->parentIdColumn = $parentIdColumn;
  }
  
  
  /**
   * Retrieve the backing repository that manages the linked data.
   * @return ISQLRepository|null repo
   */
  public function getHostRepo() : ?ISQLRepository
  {
    return $this->repo;
  }
  
  
  /**
   * Should return something like ( getHostRepo() == null );
   * @return bool is foreign
   */
  public function isForeign() : bool
  {
    return true;
  }
  
  
  /**
   * Retrieve the property set used for the join 
   * @return IPropertySet prop set 
   */
  public function getPropertySet() : IPropertySet
  {
    return $this->props;
  }
    
  
  /**
   * Retrieve the property name that triggers this condition 
   * @return string property name 
   */
  public function getPropertyName() : string
  {
    return $this->propertyName;
  }  
  
  
  /**
   * 
   * @param string $parentIdColumn
   * @param string $alias
   * @return string
   * @throws InvalidArgumentException
   */
  public function getJoin( string $parentIdColumn, string $alias, ISQLJoinType $type ) : string
  {
    if ( !empty( $this->parentIdColumn ))
    {
      $parentIdColumn = $this->parentIdColumn;
    }
    
    if ( empty( $parentIdColumn ) || !preg_match( '/^[A-Za-z0-9_]+/', $parentIdColumn ))
      throw new InvalidArgumentException( 'parentIdColumn must not be empty and be alphanumeric' );
    else if ( !empty( $alias ) && !preg_match( '/^[A-Za-z0-9_]+/', $alias ))
      throw new InvalidArgumentException( 'alias must be alphanumeric' );
    
    if ( !empty( $alias ))
      $alias .= '.';
    
    return ' ' . $type->value() . ' join ' . $this->table . ' on (' . $this->table . '.' . $this->entityIdColumn . '=' . $alias . $parentIdColumn . ') ';
  }
  
  
  /**
   * Prepares a column name for use within some query.
   * This will add some sort of table or alias prefix to the property/column name.
   * @param string $name name 
   * @return string prepared name 
   */
  public function prepareColumn( string $name, string $alias = '' ) : string 
  {
    if ( empty( $alias ))
      $alias = $this->table;
    
    if ( $name == $this->propertyName )
      return $alias . '.' . $this->entityIdColumn;
    else if ( $this->props->isMember( $name ))
      return $alias . '.' . $name;
    else //..This is vulnerable to XSS if the name is printed back to the screen.
      throw new \InvalidArgumentException( 'Property "' . htmlspecialchars( $name, ENT_COMPAT | ENT_HTML5 ) . '" name is not a valid property of ' . $this->repo->getTable());
  }
  
  
  /**
   * Retrieve the where condition.
   * This does not return and/or/etc or "where".
   * @param string $name Unused
   * @param array $values Values to include in the "in" query 
   * @return string condition sql
   * @throws InvalidArgumentException
   * @deprecated
   */
  public function getWhere( string $name, array $values, string $alias = '' ) : string 
  {
    if ( empty( $values ))
      return '';
    
    if ( empty( $alias ))
      $alias = $this->table;
    
    return ' ' . $alias . '.' . $this->entityIdColumn . ' in ' . $this->dbc->prepareIn( $values, false );
  }
}

