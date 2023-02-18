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

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\DBException;
use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\persist\ISQLRepository;
use buffalokiwi\magicgraph\persist\RecordNotFoundException;
use Exception;
use Generator;
use InvalidArgumentException;


abstract class AttributeRepo implements IAttributeRepo
{
  /**
   * Attribute repository 
   * @var ISQLRepository
   */
  protected $attrRepo;
  
  /**
   * Attribute group respository
   * @var IAttrGroupRepo 
   */  
  protected $attrGroupRepo;
  
  /**
   * Attribute group -> attribute link respository 
   * @var ISQLRepository
   */
  protected $attrGroupLinkRepo;
  
  /**
   * Attribute value repo 
   * @var IAttrValueRepo
   */
  protected $attrValueRepo;
  
  /**
   * Attribute columns 
   * @var IAttributeCols
   */
  protected $attrCols;
  
  
  /**
   * Attribute value cols
   * @var IAttrValueCols
   */
  protected $attrValueCols;
  
  /**
   * Attribute group columns 
   * @var IAttributeGroupPropertiesCols
   */
  protected $attrGroupCols;
  
  /**
   * Attribute group link columns 
   * @var IAttrGroupLinkCols
   */
  protected $attrLinkCols;  
  
  /**
   * Database connection
   * @var IDBConnection 
   */
  private $dbc;
  
  /**
   * A cache for getAttributesForGroup
   * @var array
   */
  private $attrGroupCache = [];
  

  /**
   * Loads attributes 
   * This must return an array of arrays that contain properties and values 
   * based on the IAttribute column definitions.
   */
  abstract protected function loadAttributesForGroup( int $id ) : Generator;
  
  
  /**
   * Load attributes by a group id.
   * This expects a list of arrays containing fields matching the names contained
   * within the model column definitions.  Return a property called 'groupname' 
   * to set the group name 
   * @param array $ids
   * @return array
   */
  protected abstract function loadAttributesForGroupIdList( array $ids ) : Generator;

  
  /**
   * Takes a list of codes and returns a map of 
   * [
   * code => 'code',
   * attribute id => 'id'
   * ]
   * 
   * where code and attribute id match the supplied column name definitions.
   */
  protected abstract function loadAttributeIdsByCodeList( array $codes ) : Generator;
  
  
  /**
   * Retrieve the attribute group link records for some group.
   * @param int $groupId group id 
   * @return array IAttrGroupLink[] records. 
   * @throws Exception
   */
  protected abstract function getGroupLinks( int $groupId ) : array;
  

  /**
   * Retrieve a list of attribute codes by attribute id.
   * @param int $idList one or more attr ids.
   * @return array [id => code]
   */
  protected abstract function getAttributeCodesByIdList( int ...$idList ) : array;
  
  
  /**
   * Create a new Attribute Group Service.
   * @param IRepository $attrRepo
   * @param IRepository $attrGroupRepo
   * @param IRepository $attrGroupLinkRepo
   */
  public function __construct(
    IRepository $attrRepo, 
    IAttrGroupRepo $attrGroupRepo, 
    IRepository $attrGroupLinkRepo,
    IAttrValueRepo $attrValueRepo )
  {
    $this->attrRepo = $attrRepo;
    $this->attrGroupRepo = $attrGroupRepo;
    $this->attrGroupLinkRepo = $attrGroupLinkRepo;
    $this->attrValueRepo = $attrValueRepo;
    
    $this->attrCols = $this->attrRepo->createPropertySet()->getPropertyConfig( IAttributeCols::class );
    $this->attrGroupCols = $this->attrGroupRepo->createPropertySet()->getPropertyConfig( IAttributeGroupPropertiesCols::class );
    $this->attrLinkCols = $this->attrGroupLinkRepo->createPropertySet()->getPropertyConfig( IAttrGroupLInkCols::class );
    $this->attrValueCols = $this->attrValueRepo->createPropertySet()->getPropertyConfig( IAttrValueCols::class );
    
    $this->dbc = $this->attrRepo->getDatabaseConnection();
  }
  
  
  public abstract function getAttributeByName( string $name ) : IAttribute;
  
  
  /**
   * Retrieves the attribute group id with the lowest id value.
   * This is the default.
   * @return int
   */
  public function getDefaultAttributeGroupId() : int
  {
    return $this->attrGroupRepo->getDefaultAttributeGroupId();
  }
  
  
  /**
   * Access the attribute repo for individual attribute CRUD.
   * @return IRepository
   */
  public function getAttributeRepo() : IRepository
  {
    return $this->attrRepo;
  }
  
  
  /**
   * Retrieve the attribute value repository 
   * @return IRepository Repo
   */
  public function getValueRepo() : IRepository
  {
    return $this->attrValueRepo;
  }
  
  
  /**
   * Retrieve the attribute group repo 
   * @return IRepository repo 
   */
  public function getGroupRepo() : IRepository  
  {
    return $this->attrGroupRepo;
  }  
  
  
  /**
   * Retrieve the attribute group link repo 
   * @return IRepository repo 
   */
  public function getGroupLinkRepo() : IRepository  
  {
    return $this->attrGroupLinkRepo;
  }  
  
  
  /**
   * Create a new and empty attribute group record for creating new attribute
   * groups.
   * @return IAttributeGroup
   */
  public function createEmptyAttributeGroup() : IAttributeGroup
  {
    return $this->attrGroupRepo->create( [] );
  }
  
  
  
  /**
   * Retrieve some attribute group by id.  
   * @param int $id group id 
   * @return \buffalokiwi\magicgraph\eav\IAttributeGroup 
   * @throws RecordNotFoundException if the group does not exist 
   */
  public function getAttributeGroup( int $id ) : IAttributeGroup
  {
    $g = $this->attrGroupRepo->get((string)$id );
    /* @var $g IAttributeGroup */
    $g->setAttributes( $this->getAttributeByName( $id ));
    return $g;
  }
  
  
  
  
  /**
   * Retrieve the attributes for some group by group id.
   * This returns common instances for returned attributes.
   * @param int $id Attribute group id 
   * @return array IAttribute[] attributes belonging to that group
   * @throws RecordNotFoundException if the group does not exist 
   */
  public function getAttributesForGroup( int $id ) : array
  {
    if ( isset( $this->attrGroupCache[$id] ))
      return $this->attrGroupCache[$id];
    
    $out = [];
    foreach( $this->loadAttributesForGroup( $id ) as $row )
    {
      $out[] = $this->attrRepo->create( $row );
    }
    
    
    if ( empty( $out ))
      throw new RecordNotFoundException( "Attribute Group " . $id . " does not exist, or has no attributes." );
    
    
    $this->attrGroupCache[$id] = $out;
    
    return $out;
  }
  
  
  /**
   * Retrieve a map of attribute group id => attributes for any supplied
   * attribute group ids 
   * @param array $ids Attribute group id list 
   * @return array [group id] => IAttributeGroup 
   * The returned attribute group model will have a list of IAttribute models
   * attached to it via IAttributeGroup::attachAttributes().
   * @throws InvalidArgumentException if any elements in $ids is not an integer 
   */
  public function getAttributesForGroupIdList( array $ids ) : array
  {
    $tmp = [];
    foreach( $this->loadAttributesForGroupIdList( $ids ) as $row )
    {
      if ( !isset( $tmp[$row[$this->attrLinkCols->getGroupId()]] ))
      {
        $tmp[$row[$this->attrLinkCols->getGroupId()]] = [];
        $tmp[$row[$this->attrLinkCols->getGroupId()]][0] = ( isset( $row['groupname'] )) ? $row['groupname'] : 'groupname';
        $tmp[$row[$this->attrLinkCols->getGroupId()]][1] = [];
      }
      
      $tmp[$row[$this->attrLinkCols->getGroupId()]][1][] = $this->attrRepo->create( $row );
    }
    
    $out = [];
    foreach( $tmp as $groupId => $data )
    {
      list( $groupName, $attrs ) = $data;
      
      $model = $this->attrGroupRepo->create( [
        $this->attrGroupCols->getIdColumn() => $groupId
      ]);
      /* @var $model IAttributeGroup */
      $model->setName( $groupName );
      $model->attachAttributes( $attrs );
      
      $out[$groupId] = $model;
    }    
    
    if ( empty( $out ))
      throw new RecordNotFoundException( "Attribute Group does not exist or has no attributes." );
    
    return $out;    
  }
  
  
  /**
   * Saves some attribute group and associated links.
   * @param IAttributeGroup $group
   * @return void
   * @throws Exception
   */
  public function saveAttributeGroup( IAttributeGroup $group ) : void
  {
    $this->attrGroupRepo->save( $group );

    if ( $group->getId() < 1 )
      throw new DBException( 'Failed to retrieve attribute group id after save' );

    //..Existing links 
    $links = $this->convertLinkKeysToAttributeIds( $this->getGroupLinks( $group->getId()));

    $map = $group->getAttributeMap();

    //..Valid attribute id's         
    $validIds = [];

    //..Codes that need id's 
    $newCodes = [];

    //..Split up the id's for new and existing 
    foreach( $map as $code => $attrId )
    {
      if ( $attr > 0 )
        $validIds[] = $attrId; //..Store any existing ids 
      else
        $newCodes[] = $code;
    }

    //..Save new id links after converting the codes to id's 
    foreach( $this->getAttributeIdsByCodeList( $newCodes ) as $code => $attrId )
    {
      //..Add the new link 
      //..Create and save a new link for this entry 
      $model = $this->attrGroupLinkRepo->create([]);
      /* @var $model IAttrGroupLink */
      $model->setAttributeId( $attrId );
      $model->setGroupId( $group->getId());
      $this->attrGroupLinkRepo->save( $model );          
    }

    //..Delete links 
    foreach( array_diff( array_keys( $links ), $validIds ) as $attrId )
    {
      $this->attrGroupLinkRepo->remove( $links[$attrId] );
    }
  }
  
  
  /**
   * Retrieve a list of attribute values for some list of entity id's.
   * @param int $entityId One or more entity ids 
   * @return array [entity id => [attr code => value]]
   * @throws InvalidArgumentException 
   */
  public function getAttributeValues( int ...$entityId ) : array
  {
    $values = $this->attrValueRepo->getAttributeValues( ...$entityId ) ;
    
    $attrIdList = [];
    
    foreach( $values as $entityId => $attrs )
    {
      foreach( $attrs as $attrId => $value )
      {
        $attrIdList[$attrId] = true;
      }
    }
    
    $codes = $this->getAttributeCodes( ...array_keys( $attrIdList ));
    
    $out = [];
    foreach( $values as $entityId => $attrs )
    {
      $out[$entityId] = [];
      
      foreach( $attrs as $attrId => $value )
      {
        if ( isset( $codes[$attrId] ))
          $out[$entityId][$codes[$attrId]] = $value;
      }
    }
    
    return $out;    
  }
  
  
  
  
  
  /**
   * Takes a list of attribute link models and turns the array keys into attribute id's.
   * @param array $links group links 
   * @return array remapped group links 
   */
  private function convertLinkKeysToAttributeIds( array $links )
  {
    $out = [];
    foreach( $links as $link )
    {
      /* @var $link IAttrGroupLink */
      $out[$link->getAttributeId()] = $link;
    }
    
    return $out;
  }
  
  
  /**
   * Retrieve a list of attribute id's by attribute code.
   * @param array $codes List of codes for which to retrieve id's
   * @return array map of [attribute code => attribute id]
   */
  private function getAttributeIdsByCodeList( array $codes )
  {
    $out = [];
    foreach( $this->loadAttributeIdsByCodeList( $codes ) as $row )
    {
      $out[$row[$this->attrCols->getCode()]] = $row[$this->attrCols->getId()];
    }
    
    return $out;
  }  
}
