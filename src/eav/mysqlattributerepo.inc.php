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
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;
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
   * Retrieve a list of unique values for some list of attributes.
   * This must only return string attribute values.
   * Non-string attributes should still return, but have an empty value array
   * @param string $codes List of codes 
   * @return array [code => [value,list,...]]
   * @return array
   * @throws \InvalidArgumentException if codes are empty   
   */
  public function getDistinctValues( string ...$codes ) : array
  {

    
    $out = [];
    
    foreach( $codes as $code )
    {
      if ( empty( trim( $code )))
        continue;
      
      $out[$code] = [];
    }
    
    if ( empty( $out ))
      throw new \InvalidArgumentException( 'codes must not be empty' );
        
    $sql = <<<SQL
  select distinct
  a.%1\$s ,v.%2\$s
from %3\$s a
join %4\$s v  on (v.link_attribute=a.id)
where 
  a.code in %5\$s
  and v.value != ''
group by v.%2\$s
order by v.%2\$s;
SQL;
    
    
    foreach( $this->dbc->select( 
      sprintf( $sql,
        $this->attrCols->getCode(),
        $this->attrValueCols->getValue(),
        $this->attrRepo->getTable(),
        $this->attrValueRepo->getTable(),
        $this->dbc->prepareIn( $codes, false )),
      $codes ) as $row )
    {
      $out[$row[$this->attrCols->getCode()]][] = $row[$this->attrValueCols->getValue()];
    }
    
    return $out;
  }
  
  
  protected function getIPropertyFlags() : IPropertyFlags
  {
    $flagsCol = $this->attrCols->getFlags();
    $flags = $this->attrRepo->create()->getValue( $flagsCol );
    if ( !( $flags instanceof IPropertyFlags ))
      throw new \Exception( 'Failed to retrieve IPropertyFlags' );
    
    return $flags;
  }
  
  
  /**
   * Retrieve a list of attribute codes by flag 
   * @param array $in required IPropertyFlag values 
   * @param array $notIn forbidden IPropertyFlag values 
   * @param IPropertyType|null $type
   * @return array [Attribute codes => caption]
   * @throws InvalidArgumentException
   */
  public function getAttributesByFlag( array $in = [], array $notIn = [], ?IPropertyType $type = null ) : array
  {
    if ( empty( $in ) && empty( $notIn ))
      throw new \InvalidArgumentException( 'at least one flag must be specified' );      
    
    $cond = [];
    
    $values = [];
    
    if ( $type != null )
      $values[] = $type->value();
    
    $flagsCol = $this->attrCols->getFlags();
    $flags = $this->getIPropertyFlags();
    
    
    array_walk( $in, function( string &$v ) use (&$values,&$cond,$flagsCol,$flags) : void {
      if ( !$flags->isMember( $v ))
        throw new \Exception( 'Invalid property flag' );
      
      $values[] = $v;
      $cond[] = ' find_in_set(?,' . $flagsCol . ') > 0 ';
    });
    
    
    array_walk( $notIn, function( string &$v ) use (&$values,&$cond,$flagsCol,$flags) : void {
      if ( !$flags->isMember( $v ))
        throw new \Exception( 'Invalid property flag' );
      
      $values[] = $v;
      $cond[] = ' find_in_set(?,' . $flagsCol . ') = 0 ';
    });
           
    
    if ( $type != null )
    {
      $sql = <<<SQL
      select %1\$s, %5\$s
      from %2\$s
      where %3\$s = ?
      and %4\$s
SQL;
    }    
    else
    {
      $sql = <<<SQL
      select %1\$s, %5\$s
      from %2\$s
      where 
        %4\$s
SQL;      
    }

    
    $out = [];
    foreach( $this->dbc->select( sprintf( $sql,
      $this->attrCols->getCode(),
      $this->attrRepo->getTable(),
      $this->attrCols->getType(),
      implode( ' and ', $cond ),
      $this->attrCols->getCaption()
    ), $values ) as $row )
    {
      $out[$row[$this->attrCols->getCode()]] = $row[$this->attrCols->getCaption()];
    }
    
    return $out;
  }
  
  
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
