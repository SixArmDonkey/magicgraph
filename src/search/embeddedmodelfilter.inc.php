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

use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\persist\ISQLRepository;
use buffalokiwi\magicgraph\property\IPropertySet;
use Exception;
use InvalidArgumentException;


/**
 * When using embedded models, this can be used to search those properties.
 * 
 * Embedded means:
 * 
 * Property set contains a TModel property with a non-empty prefix value.
 * A single database table is used to store the properties contained in the TModel property 
 * 
 * In RetailRack, the orders table with ship_to and bill_to properties are an example of this configuration.
 * 
 */
class EmbeddedModelFilter implements ISQLJoinFilter 
{
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
   * Properties 
   * @var IPropertySet
   */
  private IPropertySet $props;
  
  /**
   * Property prefix
   * @var string 
   */
  private string $prefix;

  
  
  public function __construct( IDBConnection $dbc, IPropertySet $props, string $propertyName, string $prefix )
  {
    if ( empty( $propertyName ))
      throw new InvalidArgumentException( 'propertyName must not be empty' );
    else if ( empty( $prefix ))
      throw new InvalidArgumentException( 'prefix must not be empty' );
    
    $this->props = $props;
    $this->dbc = $dbc;
    $this->propertyName = $propertyName;
    $this->prefix = $prefix;
  }
  
  
  /**
   * Retrieve the backing repository that manages the linked data.
   * @return ISQLRepository repo
   */
  public function getHostRepo() : ?ISQLRepository
  {
    return null;
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
   * Should return something like ( getHostRepo() == null );
   * @return bool is foreign
   */
  public function isForeign() : bool
  {
    return false;
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
    return '';
  }
  
  
  /**
   * Prepares a column name for use within some query.
   * This will add some sort of table or alias prefix to the property/column name.
   * @param string $name name 
   * @return string prepared name 
   */
  public function prepareColumn( string $name, string $alias = '' ) : string
  {
    if ( !empty( $alias ))
      $alias .= '.';
    
    if ( $this->props->isMember( $name ))
      return $alias . $this->prefix . $name;    
    else //..This is vulnerable to XSS if the name is printed back to the screen.
      throw new InvalidArgumentException( 'Property "' . htmlspecialchars( $name, ENT_COMPAT | ENT_HTML5 ) . '" name is not a valid embedded property.' );
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
    throw new Exception( 'Not implemented' );
  }
}
