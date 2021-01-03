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


namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\DBException;
use buffalokiwi\magicgraph\eav\search\MySQLSearchQueryGenerator;
use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\persist\ISQLRepository;
use buffalokiwi\magicgraph\persist\SQLSelect;
use Generator;
use InvalidArgumentException;
use TypeError;


/**
 * A MySQL based attribute repo.
 */
class MySQLAttributeRepo extends AttributeRepo
{
  /**
   * Database connection 
   * @var IDBConnection
   */
  private $dbc;
  
  /**
   * [attr id => code]
   * @var string
   */
  private $attrCodeCache = [];
  
  /**
   * Search search query generator
   * @var MySQLSearchQueryGenerator
   */
  private MySQLSearchQueryGenerator $searchQuery;

  
  /**
   * Create a new Attribute Group Service.
   * The repositories MUST all use the same database connection.
   * 
   * @param ISQLRepository $repo The primary model repo.  ie: What repo/model does this attribute repo augment?
   * @param ISQLRepository $attrRepo
   * @param ISQLRepository $attrGroupRepo This must also implement IAttrGroupRepo 
   * @param ISQLRepository $attrGroupLinkRepo
   */
  public function __construct(
    ISQLRepository $attrRepo, 
    ISQLRepository $attrGroupRepo, 
    ISQLRepository $attrGroupLinkRepo,
    IAttrValueRepo $attrValueRepo )
  {
    parent::__construct( $attrRepo, $attrGroupRepo, $attrGroupLinkRepo, $attrValueRepo );
    
    if ( !( $attrValueRepo instanceof ISQLRepository ))
      throw new InvalidArgumentException( 'attrValueRepo must be an instance of ' . ISQLRepository::class );
    
    $this->dbc = $this->attrRepo->getDatabaseConnection();
//    $this->attrCols = $attrRepo->createPropertySet()->getPropertyConfig( IAttributeCols::class );

  }
  
  
  public function getAttributeByName( string $name ) : IAttribute
  {
    $props = $this->attrRepo->createPropertyNameSet();
    $props->setAll();
    $cfg = $this->attrCols;
    foreach( $this->attrRepo->findByProperty( $cfg->getCode(), $name ) as $attr )
    {
      return $attr;
    }
    
    throw new AttributeNotFoundException( $name . ' is not a valid attribute name' );
  }
  
  
  /**
   * Retrieve a list of attributes by a name list.
   * @param string $nameList name list 
   * @return array attributes
   */    
  public function getAttributesByNameList( string ...$nameList ) : array
  {
    if ( empty( $nameList ))
      return [];
    
    $props = $this->attrRepo->createPropertyNameSet();
    $props->setAll();
    $cfg = $this->attrCols;
    /* @var $cfg IAttributeCols */
    
    $dbc = $this->attrRepo->getDatabaseConnection();
    $select = new SQLSelect( $props, $this->attrRepo->getTable());
    foreach( $dbc->select( $select->getSelect() . sprintf( ' where %1$s in %2$s', $cfg->getCode(), $dbc->prepareIn( $nameList, false )), $nameList ) as $row )
    {
      $out[$row[$cfg->getCode()]] = $this->attrRepo->create( $row );
    }
    
    return $out;
  }  
  
  
  /**
   * Retrieve a list of all enabled attribute codes keyed by attribute id.
   * @return array [id => code]
   */
  public function getAttributeCodes() : array
  {
    $cfg = $this->attrCols;
    /* @var $cfg IAttributeCols */
    $dbc = $this->attrRepo->getDatabaseConnection();
    
    $sql = sprintf( 'select %s,%s from %s order by %s',
      $cfg->getId(),
      $cfg->getCode(),
      $this->attrRepo->getTable(),
      $cfg->getCode());
    
    $out = [];
    foreach( $dbc->select( $sql ) as $row )
    {
      $out[$row[$cfg->getId()]] = $row[$cfg->getCode()];
    }
    
    return $out;    
  }
  
  
  /**
   * Saves some attribute group and associated links.
   * @param IAttributeGroup $group
   * @return void
   * @throws DBException
   */
  public function saveAttributeGroup( IAttributeGroup $group ) : void
  {
    $this->dbc->beginTransaction();
    try {
      parent::saveAttributeGroup( $group );
      
      $this->dbc->commit();
      
    } catch( \Exception | TypeError $e ) {
      $this->dbc->rollBack();
      throw $e;
    }
  }
  
  
  /**
   * Test to see if some attribute exists.
   * @param string $code Attribute code
   * @return bool exists
   */
  public function attributeExists( string $code ) : bool
  {
    return $this->existsReport( $code )[$code];
  }
  
  
  /**
   * Given some list of codes find out of each exists or not.
   * @param string $codes List of codes
   * @return array [code => bool exists] results
   */
  public function existsReport( string ...$codes ) : array
  {
    if ( empty( $codes ))
      return [];
    
    $q = 'select `%1$s` from `%2$s` where `%1$s` in %3$s';
    
    $existing = [];
    
    foreach( $codes as $c )
    {
      $existing[$c] = false;
    }
    
    
    foreach( $this->dbc->select( sprintf( $q, 
        $this->attrCols->getCode(), 
        $this->attrRepo->getTable(), 
        $this->dbc->prepareIn( $codes, false )),
      $codes ) as $row )
    {
      $existing[$row[$this->attrCols->getCode()]] = true;
    }
    
    return $existing;
  }
  
  


  
  
  /**
   * Retrieve a list of entity id's that have the supplied attributes and values.
   * WARNING: Do not use any type of user input as keys.  Attribute names are not checked prior to creating the query.
   * @param array $map [attribute code => attribute value] value map 
   * @param int $page Page number
   * @param int $size Page size 
   * @return array int[] entity id list 
   */
  /*
  public function getEntityIdsByAttributeValue( array $map, int $page = 1, int $size = 25 ) : array
  {
    $values = [];
    $where = [];
    $joins = [];
    
    //..This is dumb.
    $aidList = $this->getAttributeIdListByCodes( array_keys( $map ));
    
    
    $first = '';
    foreach( $map as $code => $value )
    {
      if ( !preg_match( '/^[a-zA-Z0-9_]+$/', $code ))
      {
        throw new \InvalidArgumentException( 'Column names must be alphanumeric' );
      }
      
      
      $key = ' v' . $code;
      if ( empty( $first ))
        $first = $key;
      
      $joins[] = $this->attrValueRepo->getTable() . $key;
      $where[] = $key . '.' . $this->attrValueCols->getAttributeId() . '=? and ' . $key . '.' . $this->attrValueCols->getValue() . '=?';
      $values[] = $aidList[$code];
      $values[] = $value;
    }
    
    if ( $page < 1 )
      $page = 1;
    
    if ( $size < 1 )
      $size = 1;
    
    $offset = ( $page - 1 ) * $size;
    
    $sql = sprintf( 'select %1$s.%2$s from %3$s where %4$s group by %1$s.%2$s limit ' . $offset . ',' . $size,
      $first,
      $this->attrValueCols->getEntityId(),
      implode( ',', $joins ),
      implode( ' and ', $where ));
    
    //..Should be something like this:
//      select
//        v1.link_entity
//        from 
//        product_attribute_value v1,
//        product_attribute_value v2 
//        where v1.link_attribute=37 and v1.value='1'
//        and v2.link_attribute=39 and v2.value='enabled'
            
            
    $out = [];
    foreach( $this->dbc->select( $sql, $values ) as $row )
    {
      $out[] = $row[$this->attrValueCols->getEntityId()];
    }
    
    return $out;
  }
  
  */
  

  
  
  /**
   * Retrieve a list of attribute codes by attribute id.
   * @param int $idList one or more attr ids.
   * @return array [id => code]
   */
  protected function getAttributeCodesByIdList( int ...$idList ) : array
  {
    $out = [];
    $query = [];
    foreach( $idList as $id )
    {
      if ( isset( $this->attrCodeCache[$id] ))
        $out[$id] = $this->attrCodeCache[$id];
      else
        $query[] = $id;
    }
    
    
    if ( !empty( $query ))
    {
      foreach( $this->dbc->select( sprintf( 
        'select `%s` from `%s` where `%s` in %s',
        implode( '`,`', [$this->attrCols->getId(), $this->attrCols->getCode()] ),
        $this->attrRepo->getTable(),
        $this->attrCols->getId(),
        $this->dbc->prepareIn( $query )),
        $query ) as $row )
      {
        $this->attrCodeCache[$row[$this->attrCols->getId()]] = $row[$this->attrCols->getCode()];
        $out[$row[$this->attrCols->getId()]] = $row[$this->attrCols->getCode()];
      }
    }
    
    return $out;
  }
  
  
  protected function loadAttributesForGroup( int $id ) : Generator
  {
    return $this->dbc->select( sprintf(
      'select a.* from `%s` l join `%s` a on (a.`%s`=l.`%s`) where l.`%s`=?',
        $this->attrGroupLinkRepo->getTable(),
        $this->attrRepo->getTable(),
        $this->attrCols->getId(),
        $this->attrLinkCols->getAttributeId(),
        $this->attrLinkCols->getGroupId()), 
      [$id]);
  }  
  

  /**
   * Load attributes by a group id.
   * This expects a list of arrays containing fields matching the names contained
   * within the model column definitions.  Return a property called 'groupname' 
   * to set the group name 
   * @param array $ids
   * @return array
   */
  protected function loadAttributesForGroupIdList( array $ids ) : Generator
  {
    return $this->dbc->select( sprintf(
      'select a.*,g.name as `groupname` from `%s` g left join `%s` l (g.`%s`=l.`%s`) left join `%s` a on (a.`%s`=l.`%s`) where l.`%s` in %s',
        $this->attrGroupRepo->getTable(),
        $this->attrGroupLinkRepo()->getTable(),
        $this->attrGroupCols->getIdColumn(),
        $this->attrLinkCols->getGroupId(),
        $this->attrRepo->getTable(),
        $this->attrCols->getId(),
        $this->attrLinkCols->getAttributeId(),
        $this->attrLinkCols->getGroupId(), 
        $this->dbc->prepareIn( $ids, true )),
      $ids );
  }  
  
  
  protected function loadAttributeIdsByCodeList( array $codes ) : Generator
  {
    if ( empty( $codes ))
    {
      yield from [];
    }
    else
    {
      return $this->dbc->select( sprintf( 'select `%s`,`%s` from `%s` where `%s` in %s',
          $this->attrCols->getId(), 
          $this->attrCols->getCode(), 
          $this->attrRepo->getTable(), 
          $this->attrCols->getCode(), 
          $this->dbc->prepareIn( $codes, false )), 
        $codes );
    }
  }
  
  
  /**
   * Retrieve the attribute group link records for some group.
   * @param int $groupId group id 
   * @return array IAttrGroupLink[] records. 
   * @throws DBException
   */
  protected function getGroupLinks( int $groupId ) : array
  {
    return $this->attrGroupLinkRepo->findByProperty( $this->attrLinkCols->getGroupId(), $groupId );
  }  
}
