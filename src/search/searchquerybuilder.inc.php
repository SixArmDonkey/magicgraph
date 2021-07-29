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
use InvalidArgumentException;


/**
 * Used to build the argument map for Search style queries.
 * @todo THIS DOES NOT WORK WITH SET COLUMN TYPES  add find_in_set() support
 */
class SearchQueryBuilder implements ISearchQueryBuilder 
{  
  /**
   * Max result set size.
   * If this is exceeded, a cursor should be used.
   */
  const MAX_RESULT_SIZE = 1000;
  
  const EQUALS = '=';
  const NOT_EQUALS = '!=';
  const GREATER_THAN = '>';
  const GREATER_THAN_EQUAL = '>=';
  const LESS_THAN = '<';
  const LESS_THAN_EQUAL = '<=';
  const LIKE = 'like';
  const IN = 'in';
  const WILDCARD = '%';
  
  const VALID_OPERATORS = [
    self::EQUALS,
    self::NOT_EQUALS,
    self::GREATER_THAN,
    self::GREATER_THAN_EQUAL,
    self::LESS_THAN,
    self::LESS_THAN_EQUAL,
    self::LIKE
  ];
         
  
  /**
   * A list of attribute names to select.
   * These can be attributes or entity columns.
   * Anything that is listed in the property set instance is valid.
   * @var string[] 
   */
  private array $attributes = [];
  
  
  /**
   * A list of column names for grouping 
   * @var string[]
   */
  private array $entityGroups = [];
  
  /**
   * A map of attribute to value 
   * @var array
   */
  private array $and = [
    self::EQUALS => [],
    self::NOT_EQUALS => [],
    self::GREATER_THAN => [],
    self::GREATER_THAN_EQUAL => [],
    self::LESS_THAN => [],
    self::LESS_THAN_EQUAL => [],
    self::LIKE => [],
    self::IN => []
  ];
  
  
  /**
   * A map of attribute to value 
   * @var array
   */
  private array $or = [
    self::EQUALS => [],
    self::NOT_EQUALS => [],
    self::GREATER_THAN => [],
    self::GREATER_THAN_EQUAL => [],
    self::LESS_THAN => [],
    self::LESS_THAN_EQUAL => [],
    self::LIKE => [],
    self::IN => []
  ];
  
  
  /**
   * Page number 
   * @var int
   */
  private int $page = 1;
  
  
  /**
   * Result set size 
   * @var int size 
   */
  private int $size = 25;
  
  
  /**
   * If $page and $size are to be used by the query generator.
   * @var bool
   */
  private bool $useLimit = true;
  
  /**
   * Sort order 
   * @var string
   */
  private string $order = '';

  
  /**
   * 
   * @param string $attributeNameList
   * @throws InvalidArgumentException
   */
  public function __construct( string ...$attributeNameList )
  {
    $this->setAttributeList( ...$attributeNameList );
  }
  
  
  /**
   * Adds one or more attributes to the select statement 
   * @param string $name property name 
   * @return void
   */
  public function addAttribute( string ...$name ) : void
  {
    $attrList = $this->attributes;
    
    foreach( $name as $n )
    {
      if ( empty( trim( $n )))
        throw new \InvalidArgumentException( 'Property names must not be null or empty' );
      else if ( !in_array( $n, $attrList ))
        $attrList[] = $n;
    }
    
    $this->setAttributeList( ...$attrList );
  }
  
  
  private function setAttributeList( string ...$attributeNameList ) : void
  {
    $this->attributes = [];
    
    foreach( $attributeNameList as $a )
    {
      $attr = trim( $a );
      if ( empty( $attr ))
        continue; //..Just skip it.
      else if ( $attr == '*' )
      {
        //..If this is a wildcard, then only allow the asterisk as an attribute name.
        $this->attributes = [];
        $this->attributes[] = '*';
        break;
      }
      
      $this->attributes[] = $attr;
    }
    
    if ( empty( $this->attributes ))
      $this->attributes[] = '*';
    
  }
  
  
  /**
   * Adds an "and" equals condition.
   * @param string $attribute Attribute code
   * @param string $value Attribute value 
   * @return ISearchQueryBuilder this 
   */
  public function and( string $attribute, ?string $value, string $operator = self::EQUALS ) : ISearchQueryBuilder
  {
    if ( empty( $attribute ))
      throw new InvalidArgumentException( 'attribute name must not be empty' );
    else if ( !in_array( $operator, self::VALID_OPERATORS ))
      throw new InvalidArgumentException( 'Invalid operator' );
    
    $this->and[$operator][$attribute] = $value;
    
    return $this;
  }
  
  
  /**
   * Adds a map of column => value used as "and" conditions.
   * @param array $map column => value
   * @return ISearchQueryBuilder this 
   */
  public function andAll( array $map ) : ISearchQueryBuilder
  {
    foreach( $map as $k => $v )
    {
      $this->and( $k, $v );
    }
    
    return $this;
  }
  
  
  /**
   * Adds an "and" "in" condition.
   * @param string $attribute Attribute code 
   * @param array $value List of possible values 
   * @return ISearchQueryBuilder this 
   */
  public function andIn( string $attribute, array $value ) : ISearchQueryBuilder
  {
    if ( empty( $attribute ))
      throw new InvalidArgumentException( 'attribute name must not be empty' );
    else if ( empty( $value ))
      throw new InvalidArgumentException( 'value list must not be empty' );
    
    foreach( $value as &$v )
    {
      if ( $v === null || empty( trim((string)$v )))
        throw new InvalidArgumentException( 'Attribute values must not be empty when using "in" conditions' );      
    }
    
    $this->and[self::IN][$attribute] = array_values( $value );
    return $this;
  }
  
  
  /**
   * Adds an "or" equals condition.
   * @param string $attribute Attribute code
   * @param string $value Attribute value 
   * @return ISearchQueryBuilder this 
   */
  public function or( string $attribute, ?string $value, string $operator = self::EQUALS ) : ISearchQueryBuilder
  {
    if ( empty( $attribute ))
      throw new InvalidArgumentException( 'attribute name must not be empty' );
    else if ( !in_array( $operator, self::VALID_OPERATORS ))
      throw new InvalidArgumentException( 'Invalid operator' );
    
    $this->or[$operator][$attribute] = $value;
    return $this;
  }
  
  
  /**
   * Adds an "or" "in" condition.
   * @param string $attribute Attribute code 
   * @param array $value List of possible values 
   * @return ISearchQueryBuilder this 
   */
  public function orIn( string $attribute, array $value ) : ISearchQueryBuilder
  {
    if ( empty( $attribute ))
      throw new InvalidArgumentException( 'attribute name must not be empty' );
    else if ( empty( $value ))
      throw new InvalidArgumentException( 'value list must not be empty' );
    
    foreach( $value as &$v )
    {
      if ( $v === null || empty( trim( $v )))
        throw new InvalidArgumentException( 'Attribute values must not be empty when using "in" conditions' );      
    }    
    
    $this->orIn[self::IN][$attribute] = array_values( $value );
    return $this;
  }
  
  
  /**
   * This will check to see if the supplied properties are valid against the supplied property set.
   * 
   * WARNING: This will purposely NOT check any properties containing a dot ".".  It is CRITICAL that the ISQLJoinFilter 
   * instances used in the ISearchQueryGenerator validate each supplied property name during query generation.  
   * Failure to validate column names will lead to SQL injection vulnerabilities.
   * @param IPropertySet $entityProperties
   * @param IProperty $prefixProps A list of properties that contain a prefix.  Without this, validation errors get thrown.  Starting to feel hacky...
   * @throws SearchException 
   */
  public function validate( IPropertySet $entityProperties, IProperty ...$prefixProps ) : void
  {
    if ( $this->isWild())
      return;
    
    
    $pri = [];
    foreach( $this->getAttributeCodes() as $code )
    {
      $isPre = false;
      foreach( $prefixProps as $pre )
      {
        if ( substr( $code . '_', 0, strlen( $pre->getPrefix())) == $pre->getPrefix())
        {
          $isPre = true;
          break;
        }
      }
      
      if ( !$isPre && strpos( $code, '.' ) === false )
        $pri[] = $code;
    }
    
    if ( !$entityProperties->isMember( ...$pri ))
      throw new InvalidArgumentException( 'Search query contains invalid attribute names.' );      
  }
  
  
  /**
   * If this query is a wildcard search 
   * @return bool is wild 
   */
  public function isWild() : bool
  {
    return sizeof( $this->attributes ) == 1 && $this->attributes[0] == '*';
  }
  
  
  /**
   * Retrieve the attribute names in this query 
   * @return array names 
   */
  public function getAttributes() : array
  {
    return $this->attributes;
  }
  
  
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
  public function getConditions() : array
  {
    return [
      'and' => $this->and,
      'or' => $this->or
    ];
  }
  
  
  /**
   * Retrieve a list of attributes used within the condition lists
   * @return array attribute codes 
   */
  public function getConditionAttributes() : array
  {
    return $this->getAttributeCodes( false );
  }
  
  
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
  public function getEntityGroups() : array
  {
    return $this->entityGroups;
  }
  
  
  /**
   * Adds a list of column names to the entity groups list 
   * @param string $columnNames column names 
   * @return void
   */
  public function addEntityGroups( string ...$columnNames ) : void
  {
    foreach( $columnNames as $col )
    {
      if ( $col !== null && !empty( trim( $col )))
        $this->entityGroups[] = $col;
    }
  }
  
  
  /**
   * Retrieves a list of attribute codes.
   * This may contain duplicates 
   * @return array codes 
   */
  private function getAttributeCodes( bool $withAttributes = true ) : array
  {
    if ( $withAttributes )
      $codes = $this->attributes;
    else
      $codes = [];
    
    foreach( $this->and as $op => $data )
    {
      foreach( array_keys( $data ) as $attr )
      {
        $codes[$attr] = $attr;
      }
    }
    
    foreach( $this->or as $op => $data )
    {
      foreach( array_keys( $data ) as $attr )
      {
        $codes[$attr] = $attr;
      }
    }
    
    if ( !empty( $this->order ))
      $codes[$this->order] = $this->order;
    
    if ( isset( $codes['*'] ))
      unset( $codes['*'] );
    
    return array_values( $codes );
  }
  
  
  /**
   * Toggle limiting the result set.
   * If this is false, then page and size should not be used.
   * @param bool $enabled Enabled 
   * @return ISearchQueryBuilder this 
   */
  public function setLimitEnabled( bool $enabled ) : ISearchQueryBuilder
  {
    $this->useLimit = $enabled;
    return $this;
  }
  
  
  
  /**
   * If limit is enabled 
   * @return bool enabled 
   */
  public function isLimitEnabled() : bool
  {
    return $this->useLimit;
  }
  
  
  /**
   * Sets the page number.
   * @param int $page page number 
   * @return void
   */
  public function setPage( int $page = 1 ) : ISearchQueryBuilder
  {
    if ( $page < 1 )
      throw new InvalidArgumentException( 'page must be greater than zero' );
    
    $this->page = $page;
    return $this;
  }
  
  
  /**
   * Sets the result set size 
   * @param int $size size 
   * @return void
   */
  public function setResultSize( int $size = 50 ) : ISearchQueryBuilder
  {
    if ( $size < 1 || $size > self::MAX_RESULT_SIZE )
      throw new InvalidArgumentException( 'Invalid result set size.  If you want to return a large result set, consider using a cursor via the stream() method.' );
    
    $this->size = $size;
    return $this;
  }
  
  
  /**
   * Retrieve the page number 
   * @return int page 
   */
  public function getPage() : int
  {
    return $this->page;
  }
   
  
  
  /**
   * Retrieve the result set size 
   * @return int size 
   */
  public function getResultSize() : int
  {
    return $this->size;
  }  
  
  
  /**
   * Sort order 
   * @return string order by column name  
   */
  public function getOrder() : string
  {
    return $this->order;
  }
  
  
  /**
   * Sets the sort order 
   * @param string $order column name 
   * @return this
   */
  public function setOrder( string $order ) : ISearchQueryBuilder
  {
    if ( !preg_match( '/^[a-zA-Z0-9\-+]+$/', $order ))
      throw new \InvalidArgumentException( 'Invalid order by' );
    
    $this->order = $order;
    return $this;
  }
  
  
  /**
   * Return the character used as a wildcard.
   * This may change depending on the persistence layer
   * @return string character
   */
  public function getWildcardChar() : string
  {
    return self::WILDCARD;
  }
}
