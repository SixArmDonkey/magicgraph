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

namespace buffalokiwi\magicgraph\junctionprovider;

use buffalokiwi\magicgraph\AbstractOneManyPropertyService;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\junctionprovider\IJunctionModelProperties;
use buffalokiwi\magicgraph\junctionprovider\IJunctionTargetProperties;
use buffalokiwi\magicgraph\property\IPropertySvcConfig;
use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\persist\IRunnable;
use buffalokiwi\magicgraph\persist\ISQLRepository;
use buffalokiwi\magicgraph\ValidationException;
use InvalidArgumentException;


/**
 * Contains some of the base programming for a property service backed by a single 
 * repository and an array property full of IModel instances.
 * 
 * Links to the target models are based on a junction table.
 * 
 * ie:
 * 
 * Parent model table
 * id - primary key 
 * 
 * Target model table 
 * id - Primary key 
 * 
 * junction model table
 * id          - primary key 
 * link_parent - id of parent model
 * link_target - id of target model
 * 
 * 
 * id is added to the junction table simply to make things easier.  The primary 
 * key should be compound key (link_parent,link_target) with an additional index 
 * on id.
 * 
 * ===================================================================
 * 
 * Target model edits are saved unless in read only mode.
 */
class MySQLJunctionPropertyService extends AbstractOneManyPropertyService
{
  /**
   * Junction model repository 
   * @var ISQLRepository 
   */
  private $junctionRepo;
  
  
  /**
   * Target model repository 
   * @var type 
   */
  private $targetRepo;
  
  
  /**
   * Junction model properties 
   * @var IJunctionModelProperties 
   */
  private $jCols;
  
  
  /**
   * Target model properties 
   * @var IJunctionTargetProperties 
   */
  private $tCols;
  
  /**
   * If the target is read only 
   * @var bool 
   */
  private $readOnly;
  
  
  /**
   * 
   * @param IPropertySvcConfig $cfg Property service config 
   * @param ISQLRepository $junctionRepo Junction table repository 
   * @param ISQLRepository $targetRepo Target model repository 
   * @param string $junctionIntf Junction model property definitions interface
   * @param string $targetIntf Target model property definitions interface
   * @throws InvalidArgumentException 
   */
  public function __construct( 
    IPropertySvcConfig $cfg, 
    ISQLRepository $junctionRepo, 
    ISQLRepository $targetRepo, 
    string $junctionIntf = IJunctionModelProperties::class, 
    string $targetIntf = IJunctionTargetProperties::class,
    bool $readOnlyTarget = false )
  {
    parent::__construct( $cfg );
    $this->junctionRepo = $junctionRepo;
    $this->targetRepo = $targetRepo;
    $this->jCols = $junctionRepo->createPropertySet()->getPropertyConfig( $junctionIntf );
    $this->tCols = $targetRepo->createPropertySet()->getPropertyConfig( $targetIntf );
    $this->readOnly = $readOnlyTarget;
    
    if ( $this->junctionRepo->getDatabaseConnection() !== $this->targetRepo->getDatabaseConnection())
      throw new InvalidArgumentException( 'Junction and target repositories must share the same database connection.' );
  }
  
  
 
  /**
   * Junction provider save function 
   * 
   * STEPS:
   * =============================
   * 
   * Save relationships to the junction repo.
   * Requires:
   *   parent id 
   *   target id 
   * 
   * pull all relationships from the junction repo matching parent id.
   * Create a list containing the records that exist in the db, but not in the supplied list.  Remove them.
   * Save the junction repo models.
   * 
   * =============================
   * Save target edits.
   * Requires:
   *   Full target model 
   *  
   * 
   * @param IModel $parent
   * @return IRunnable
   */
  public function getSaveFunction( IModel $parent ) : array
  {
    //..Get the primary key of the parnet model 
    $priKeys = $parent->getPropertySet()->getPrimaryKeys();
    if ( empty( $priKeys ))
      throw new Exception( 'Parent model (' . get_class( $this->parent ) . ')must contain at least one primary key definition' );
    else if ( sizeof( $priKeys ) > 1 )
      throw new Exception( 'Address Service cannot be natively linked to models with compound primary keys.  You must override and create your own save function.' );
    
    //..Get the parent id 
    $parentId = (string)$parent->getValue( $priKeys[0]->getName());    
    
    
    //..Get the existing junction table records
    $existingJunction = $this->junctionRepo->getForProperty( $this->jCols->getParentId(), $parentId );
    
    //..Target ids from the existig records
    $existingIds = [];
    
    //..A map of target id => junction table record id (id property)
    $idMap = [];
    
    foreach( $existingJunction as $rec )
    {
      //..Add target id to the list
      $existingIds[] = $rec->getValue( $this->jCols->getTargetId());
      
      //..Add the id map entry 
      $idMap[$rec->getValue( $this->jCols->getTargetId())] = $rec->getValue( $this->jCols->getId());
    }
    
    //..Get the list of supplied target models 
    $suppliedJunction = $parent->getValue( $this->getModelServiceConfig()->getModelPropertyName());    
    
    
    //..A list of target model ids 
    $suppliedIds = [];
    
    
    //..Iterate over the list of supplied ids that do not have junction records yet
    $newModels = [];
    
    foreach( $suppliedJunction as $rec )
    {
      //..Get the target model id 
      $newId = $rec->getValue( $this->tCols->getId());
      
      //..If not empty, then add to the supplied ids list
      //..Models with empty ids are new and will be added 
      if ( !empty( $newId ))
        $suppliedIds[] = $newId;      
      else
        throw new ValidationException( 'Cannot save linked record into (' . $this->junctionRepo->getTable() . ') because target model (' . $this->targetRepo->getTable() . ') has not been committed.  Please save prior to attaching the linked models.' );
    }
    
    
    //..List junction table id's to remove 
    $removeIds = [];
    
    //..Iterate over a list of existing ids that do not exist in the list of supplied ids 
    foreach( array_diff( $existingIds, $suppliedIds ) as $targetId )
    {
      //..Add the id 
      $removeIds[] = $idMap[$targetId];
    }
    
    
    
    foreach( array_diff( $suppliedIds, $existingIds ) as $newId )
    {
      //..Create the new junction table model 
      $newModels[] = $this->junctionRepo->create([
        $this->jCols->getParentId() => $parentId,
        $this->jCols->getTargetId() => $newId
      ]);
    }        
    
    //..Save the target models if not read only
    $targetSave = ( $this->readOnly ) ? [] : $this->targetRepo->getSaveFunction( null, null, ...$suppliedJunction );
    
    //..Return the save functions 
    $toSave = $this->junctionRepo->getSaveFunction( 
      function( IRepository $repo, IModel ...$models ) use ($parent,$priKeys) {
        $parentId = (string)$parent->getValue( $priKeys[0]->getName());    
        foreach( $models as $model )
        {
          $model->setValue( $this->jCols->getParentId(), $parentId );
        }
      }, 
              
      function( IRepository $repo, IModel ...$models ) use ($removeIds) {      
        foreach( $removeIds as $id )
        {
          $this->junctionRepo->removeById((string)$id );
        }
      },      
      ...$newModels
    );
      
      
    foreach( $targetSave as $t )
    {
      $toSave[] = $t;
    }
    
    return $toSave;
  }
    
  
  /**
   * Load models 
   * @param int $parentId Parent id 
   * @return IModel[] models 
   */
  protected function loadModels( int $parentId ) : array
  {
    $out = [];
    
    $dbc = $this->junctionRepo->getDatabaseConnection();    
    $q = 'select j.`%s` as `junction_parent_id`, t.* from `%s` j join `%s` t on (j.`%s`=t.`%s`) where j.`%s`=?';
    
    foreach( $dbc->select( 
      sprintf( $q,
        $this->jCols->getParentId(),
        $this->junctionRepo->getTable(),
        $this->targetRepo->getTable(),
        $this->jCols->getTargetId(),
        $this->tCols->getId(),
        $this->jCols->getParentId()),
      [$parentId] ) as $row ) 
    {
      /*
      if ( !isset( $out[$row['junction_parent_id']] ))
        $out[$row['junction_parent_id']] = [];
      
      $out[$row['junction_parent_id']][] = $this->targetRepo->create( $out );
      */
      
      $out[] = $this->targetRepo->create( $row, $this->readOnly );
    }
    
    return $out;
  }
}
