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
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\property\IPropertyType;
use InvalidArgumentException;



/**
 * A simple search query generator for mysql.
 * 
 * This is a very rough prototype, and will be revised in a future release.
 * 
 * @todo there is a group_concat() in here, which MUST be removed and replaced with something that is not group_concat()...  
 */
class MySQLSearchQueryGenerator implements ISearchQueryGenerator
{
  /**
   * Database connection 
   * @var IDBConnection 
   */
  private IDBConnection $dbc;
    
  /**
   * MySQL table name 
   * @var string
   */
  private string $table;
  
  /**
   * Entity properties.
   * Everything you ever wanted to know about an entity.
   * @var IPropertySet
   */
  private IPropertySet $entityProps;
    
  /**
   * Join filter list 
   * @var ISQLJoinFilter[] 
   */
  private array $joinFilterList = [];
  
  /**
   * Linked property sets
   * @var IPropertySet[] 
   */
  private array $linkedPropertySets = [];
    
  /**
   * A list of property name prefixes of embedded properties.
   * @var IProperty[] 
   */
  private array $prefixList = [];
    
  /**
   * If the prefix list has been loaded 
   * @var bool 
   */
  private bool $loadedPrefixList = false;
  
  
  /**
   * MySQLSearchQueryBuilder 
   * @param string $tableName The table name to run the query against 
   * @param IPropertySet $entityProps Properties for the table
   * @param IDBConnection $dbc Database connection to some mysql database 
   */
  public function __construct( string $tableName, IPropertySet $entityProps, IDBConnection $dbc, 
    ISQLJoinFilter ...$joinFilterList  )
  {
    $this->dbc = $dbc;
    $this->table = $tableName;
    $this->entityProps = $entityProps;
    
    
    foreach( $joinFilterList as $filter )
    {
      $this->joinFilterList[$filter->getPropertyName()] = $filter;
      $this->linkedPropertySets[$filter->getPropertyName()] = $filter->getPropertySet();
    }
  }
    
  
  public function getFilter( string $propertyName ) : ISQLJoinFilter
  {
    if ( empty( $propertyName ) || !isset( $this->joinFilterList[$propertyName] ))
      throw new \InvalidArgumentException( 'The supplied property name does not utilize a join filter' );
    
    return $this->joinFilterList[$propertyName];
  }
  
  
  /**
   * Retrieve the schema 
   * @return array schema 
   */
  public function getSchema() : array
  {
    $out = $this->entityProps->getSchema();
    
    foreach( $this->joinFilterList as $name => $filter )
    {
      $s = $filter->getPropertySet()->getSchema();
      
      foreach( $s as $k => $v )
      {
        $out[$name . '.' . $k] = $v;
      }
    }
    
    return $out;
  }
  
  
  /**
   * Retrieve the property set used when searching linked types (Join Filters).
   * @param string $name Trigger property name (What the IJoinFilter was registered as )
   * @return IPropertySet Property set created by the host repo attached to the join filter.
   * @throws InvalidArgumentException
   */
  public function getJoinedPropertySet( string $name ) : IPropertySet
  {
    if ( !isset( $this->joinFilterList[$name] ))
      throw new InvalidArgumentException( $name . ' is not a registered join filter' );
    
    return $this->joinFilterList[$name]->getPropertySet();
  }
  
  
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
   * @return IQueryBuilderOutput SQL and bindings 
   */
  public function createQuery( ISearchQueryBuilder $builder, bool $returnCount = false ) : IQueryBuilderOutput
  {
    //..Load a list of properties that contain a prefix 
    $this->loadPrefixList();

    //..The entity table alias 
    $entityAlias = 'e';
         
    //..Where condition for entity columns
    $entityAndWhere = [];
    
    //..Where condition for entity columns 
    $entityOrWhere = [];
    
    $builder->validate( $this->entityProps, ...$this->prefixList );
    
    //..Used by sql filter objects
    $filterJoin = [];
    
    $varIndex = 0;

    $values = [];
    
    $groupBy = [];
    
    //..A list of left joins keyed by alias. 
    //..This is required to prevent inner joins from overwriting left joins 
    $leftJoins = [];
    
    //..Build the columns to select 
    $select = [];
    
    //..Build the various condition blocks     
    foreach( $builder->getConditions() as $andOr => $conditionGroups )
    {
      foreach( $conditionGroups as $operator => $conditions )
      {
        foreach( $conditions as $code => $value )
        {
          $codeParts = explode( '.', $code );
          if ( sizeof( $codeParts ) > 2 )
            throw new SearchException( 'Properties may only be nested one level.  ie: prop.subprop is ok.  prop.subprop.subsubprop is not ok.  This restriction will be removed in a future release.' );
          
          $code = $codeParts[0];
          $subProp = $codeParts[1] ?? null;
          
          //..Get the property for this condition 
          if ( $subProp == null )
            $prop = $this->entityProps->getProperty( $code );
          else if ( isset( $this->linkedPropertySets[$code] ))
            $prop = $this->linkedPropertySets[$code]->getProperty( $subProp );
          else
            throw new SearchException( 'Invalid property name' );
            
          
          if ( is_bool( $value ))
            $value = ( $value ) ? '1' : '';
          
          
          if ( isset( $this->joinFilterList[$code] ))
          {
            $name = $subProp ?? $prop->getName();
            
            $filter = $this->joinFilterList[$code];
            /* @var $filter ISQLJoinFilter */
            
            
            if ( $filter->isForeign())
            {              
              if ( !isset( $leftJoins[$code] ))
              {
                $leftJoins[$code] = true;
                $jf = $filter->getJoin( $this->entityProps->getPrimaryKey()->getName(), $entityAlias, ( $value === null ) ? ESQLJoinType::LEFT() : ESQLJoinType::INNER());
                if ( !empty( $jf ))
                  $filterJoin[$code] = $jf;
              }
            }
            
            /*
             //..This will prevent additional conditions from appearing in the query.  That's bad.
            else
            {
              //..Switch any "and" conditions to "or" for left joins.
              //..Weird.
              //..Maybe continue instead.
              continue;
              //$andOr = 'or';
            }
              */          
            
            
            //..This is a search on a joined table 
            //..This is handled by the above code
            /*
            $join = $filter->getJoin( $this->entityProps->getPrimaryKey()->getName(), $entityAlias, ESQLJoinType::INNER());
            if ( !empty( $join ))
            {
              $filterJoin[$prop->getName()] = $join;
            }
            */
            
            $cond = $filter->prepareColumn( $name ) . $this->buildConditionOperand( $operator, $value, $varIndex, $values );
            
            
            //..Add to either and or or 
            switch( $andOr )
            {
              case 'and':
                $entityAndWhere[] = $cond;
              break;

              case 'or':
                $entityOrWhere[] = $cond;
              break;

              default:
                throw new SearchException( 'Operator must equal "and" or "or".' );
            }               
            
            /*
            
            if ( !is_array( $value ))
              $value = [$value];
            
            $outVals = [];
            
            $w = explode( '?', $filter->getWhere( $name, $value ));
            
            
            $params = sizeof( $w );
            
            
            for( $i = 0; $i < $params - 1; $i++ )
            {
              $k =  ':VAR' . (++$varIndex);
              $outVals[$k] = $value[$i]; //..This should work, I think.
              $w[$i] .= $k;
            }
            
            $andWhere[] = implode( ' ', $w );
            

            foreach( $outVals as $k => $v )
            {
              $values[$k] = $v;
            }          
             * 
             */     
            
          }
          else if ( !$prop->getFlags()->hasVal( IPropertyFlags::PRIMARY ))
          {            
            //..These are entity properties.  Primary key searches are disallowed.
            $cond = $entityAlias . '.' . $code . $this->buildConditionOperand( $operator, $value, $varIndex, $values );
            
            //..Add to either and or or 
            switch( $andOr )
            {
              case 'and':
                $entityAndWhere[] = $cond;
              break;

              case 'or':
                $entityOrWhere[] = $cond;
              break;

              default:
                throw new SearchException( 'Operator must equal "and" or "or".' );
            }            
          }
        }
      }
    }
    
    
    //..Attributes to select 
    $attributes = $this->getAttributes( $builder );
    
    //..Add any entity attribute to select 
    array_walk( $attributes, function( $unused, $code ) use (&$select,$entityAlias,&$groupBy,&$filterJoin,&$leftJoins) {
      
      $codeParts = explode( '.', $code );
      if ( sizeof( $codeParts ) > 2 )
        throw new SearchException( 'Properties may only be nested one level.  ie: prop.subprop is ok.  prop.subprop.subsubprop is not ok.  This restriction will be removed in a future release.' );

      $code = $codeParts[0];
      $subProp = $codeParts[1] ?? null;

      
      if ( isset( $this->joinFilterList[$code] ) && $subProp != null )
      {
        $filter = $this->joinFilterList[$code];          
        /* @var $filter ISQLJoinFilter */
        if ( $filter->isForeign())
        {
          if ( !isset( $leftJoins[$code] ))
          {
            $leftJoins[$code] = true;
            $filterJoin[$code] = $filter->getJoin( $this->entityProps->getPrimaryKey()->getName(), $entityAlias, ( isset( $filterJoin[$code] )) ? ESQLJoinType::INNER() : ESQLJoinType::LEFT());
          }

          $select[] = $filter->getHostRepo()->getTable() . '.' . $subProp . ' as `' . $code . '.' . $subProp . '` ';
        }
      }      
      
      
      //..Get the property for this condition 
      $isEntity = true;
      /* @var $prop IProperty */
      $prop = null;
      $skipModelCheck = false;
      
      if ( $subProp == null && $this->entityProps->isMember( $code ))
      {
        $prop = $this->entityProps->getProperty( $code );
      }
      else 
      {
        $isEntity = false;
        
        //..Need to go through the prefix list
        foreach( $this->prefixList as $pre )
        {
          /* @var $pre IProperty */
          if ( substr( $code . '_', 0, strlen( $pre->getPrefix())) == $pre->getPrefix())
          {
            $prop = $pre;
            $skipModelCheck = true;
            break;
          }
        }
        
        if ( $prop == null && isset( $this->linkedPropertySets[$code] ))
        {
          $prop = $this->linkedPropertySets[$code]->getProperty( $subProp );
        }
      }
      
      if ( $prop == null )
        throw new SearchException( 'Invalid property name' );      
        
      
      if ( !$skipModelCheck && $prop->getType()->is( IPropertyType::TARRAY, IPropertyType::TMODEL, IPropertyType::TOBJECT ))
      {
        return;
      }
      
      if ( $isEntity )
      {
        $select[] = $entityAlias . '.' . $code;
        $groupBy[] = $entityAlias . '.' . $code;
      }
      else if ( empty( $prop->getPrefix()))
      {        
        //..Used for one -> many.
        //..This is so incredibly wrong.  
        if ( isset( $this->joinFilterList[$code] ))
          $pfx = $this->joinFilterList[$code]->getHostRepo()->getTable();
        else
          $pfx = $code;
        
        $select[] = $pfx . '.' . $subProp . ' as `' . $code . '.' . $subProp . '`';
        //$select[] = 'group_concat( distinct ' . $pfx . '.' . $subProp . ') as `' . $code . '.' . $subProp . '`';
        $groupBy[] = $pfx . '.' . $subProp;
      }
      else 
      {
        $select[] = $prop->getPrefix() . $subProp . ' as `' . $code . '.' . $subProp . '`';
        $groupBy[] = $prop->getPrefix() . $subProp; //$code . '.' . $subProp;
      }
    });    
    
    
    //..Get the primary key names 
    $priKeys = [];    
    foreach( $this->entityProps->getPrimaryKeyNames() as $name )
    {      
      $priKeys[] = $entityAlias . '.' . $name;
      $groupBy[] = $entityAlias . '.' . $name;
    }
    
    if ( empty( $priKeys ))
      throw new SearchException( 'At least one primary key must be defined on the entity property set' );
    
            
    

    if ( !$returnCount && $builder->isLimitEnabled())
    {
      $page = $builder->getPage();
      $size = $builder->getResultSize();
      $offset = ' limit ' . $this->getOffset( $page, $size ) . ',' . $builder->getResultSize();
    }
    else
      $offset = '';
    
    
    $entityWhere = [];
    
    
    //..Build the and section 
    if ( !empty( $entityAndWhere ))
      $entityAnd = ' (' . implode( ' and ', $entityAndWhere ) . ') ';
    else
      $entityAnd = '';
    
    //..Build the or section 
    $entityOr = implode( ' or ', $entityOrWhere );
    
    ///..If both and and or exist, then add "or" as a prefix to the or string 
    if ( !empty( $entityAndWhere ) && !empty( $entityOrWhere ))
      $entityOr = ' or ' . $entityOr;    
    
    
   
    if ( !empty( $entityAnd ))
    {
      $entityWhere[] = $entityAnd;
    }
    
    if ( !empty( $entityOr ))
      $entityWhere[] = $entityOr;
    
    
    if ( !empty( $entityWhere ))
      $entityWhere = ' where ' . implode( ' ', $entityWhere );
    else
      $entityWhere = '';
    
    
    if ( $returnCount )
      $select = 'count(*) as `count`';
    else if ( empty( $select ) || $builder->isWild())
      $select = $entityAlias . '.*,';
    else 
      $select = implode( ',', $select ) . (( !empty( $select )) ? ',' : '' );
    
    if ( !empty( $filterJoin ))
    {
      
      $filterJoin = ' ' . implode( ' ', $filterJoin );
    }
    else
      $filterJoin = '';
    
    if ( !empty( $builder->getOrder()))
    {
      $orderBy = [];
      foreach( explode( ',', $builder->getOrder()) as $o )
      {
        $orderBy[] = $entityAlias . '.' . $o;
      }
      
      $orderBy = ' order by ' . implode( ',', $orderBy ) . ' ';
    }
    else
      $orderBy = '';
    
    if ( !empty( $groupBy ))
      $groupBy = ' group by ' . implode( ',', $groupBy ) . ' ';
    else
      $groupBy = '';
    
   
    
    $sql = sprintf( 'select %1$s %2$s from %3$s %4$s %5$s %6$s %7$s %8$s %9$s',
      $select,
      ( $returnCount ) ? '' : implode( ',', $priKeys ),
      $this->table,
      $entityAlias,
      $filterJoin,
      $entityWhere,
      '', //( $returnCount ) ? '' : $groupBy,
      $orderBy,
      $offset,
    );

    /*
    var_dump( $values );
    echo $sql;
    die;
    */
    
    
    return $this->createQueryBuilderOutput( $builder, $this->entityProps->getPrimaryKey()->getName(), $sql, $values );
  }
  
  
  
  
  /**
   * Creates the query builder output object
   * @param string $sql sql statement 
   * @param array $values binding values 
   * @return IQueryBuilderOutput output 
   */
  protected function createQueryBuilderOutput( ISearchQueryBuilder $builder, string $uniqueId, string $sql, array $values ) : IQueryBuilderOutput
  {
    return new MySQLQueryBuilderOutput( $builder, $uniqueId, $sql, $values );
  }
  
  
  
  protected function buildConditionOperand( string $type, $value, int &$varIndex, array &$values ) : string
  {
    if ( is_array( $value ) && $type != 'in' )
      throw new InvalidArgumentException( 'Cannot bind array value when operator is not "in".' );
    
    switch( $type )
    {
      case '=':
        $values[':VAR' . (++$varIndex)] = $value;
        return ' = :VAR' . ( $varIndex );
        
      case '!=':
      case '<>':
        $values[':VAR' . (++$varIndex)] = $value;
        return ' != :VAR' . ( $varIndex );
    
      case 'in':
        if ( !is_array( $value ))
          throw new SearchException( 'Value must be an array when using "in" conditions' );
        
        $parts = [];
        foreach( $value as $v )
        {
          $values[':VAR' . (++$varIndex)] = $v;
          $parts[] = ':VAR' . ( $varIndex );
        }
        
        return ' in (' . implode( ',', $parts ) . ') ';
        
        //return ' in ' . $this->dbc->prepareIn( $value, false );
      
    
      case 'like':
        $values[':VAR' . (++$varIndex)] = $value;
        return ' like :VAR' . ( $varIndex );
        
      case '>':
        $values[':VAR' . (++$varIndex)] = $value;
        return ' > :VAR' . ( $varIndex );
        
      case '>=':
        $values[':VAR' . (++$varIndex)] = $value;
        return ' >= :VAR' . ( $varIndex );
        
      case '<':
        $values[':VAR' . (++$varIndex)] = $value;
        return ' < :VAR' . ( $varIndex );
        
      case '<=':
        $values[':VAR' . (++$varIndex)] = $value;
        return ' <= :VAR' . ( $varIndex );

      default:
        throw new SearchException( 'Invalid operator' );
    }
  }
  
  
  /**
   * This will retrieve the starting offset for a query.
   * If page or size is less than one, this will set $page and/or $size equal to 1.
   * @param int &$page Page variable
   * @param int &$size Size variable 
   * @return int starting offset
   */
  protected function getOffset( int &$page, int &$size ) : int
  {    
    if ( $page < 1 )
      $page = 1;
    
    if ( $size < 1 )
      $size = 1;
    
    return ( $page - 1 ) * $size;    
  }
  
  
  /**
   * Retrieve a map of attributes to select
   * @param ISearchQueryBuilder $builder builder
   * @return array attribute => true
   */
  protected function getAttributes( ISearchQueryBuilder $builder ) : array
  {
    $attributes = [];
    
    if ( !$builder->isWild())
    {
      foreach( $builder->getAttributes() as $code )
      {
        $attributes[$code] = true;
      }
    }    
    
    return $attributes;
  }
  
  
  /**
   * Loads a list of properties that contain a prefix.
   * @return void
   */
  private function loadPrefixList() : void
  {
    if ( !$this->loadedPrefixList )
    {
      //..I didn't want to load this in the constructor for every repo.  Need to reduce constructor bloat.
      foreach( $this->entityProps->getProperties() as $prop )
      {
        /* @var $prop IProperty */
        if ( !empty( $prop->getPrefix()))
        {
          $this->prefixList[] = $prop;
        }
      }
      
      $this->loadedPrefixList = true;
    }    
  }
  
  
  
}
