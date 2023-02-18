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

namespace buffalokiwi\magicgraph\junctionprovider;

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\persist\ISQLRepository;
use buffalokiwi\magicgraph\property\IPropertySvcConfig;
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
class MySQLJunctionPropertyService extends AbstractSQLJunctionPropertyService
{
  /**
   * 
   * @param IPropertySvcConfig $cfg Property service config 
   * @param ISQLRepository $junctionRepo Junction table repository 
   * @param ISQLRepository $targetRepo Target model repository 
   * @param bool $readOnlyTarget
   * @param bool $reverse Reverse parent and target when loading and saving.  Use this when the junction service is 
   * attached to the target repository.  When using reverse, the property set attached to the parent repository models 
   * MUST implement IJunctionTargetProperties.  This is not required when not using reverse.
   * Saving is temporarily disabled when using reverse.  
   * @param bool $manageDeletes Defaults to false, set to true, and this will take the difference between what's in the database and what is loaded 
   * in the model and deletes those records.
   * 
   * ie: 
   * model array property contains [1,3]
   * db contains [1,2,3]
   * 
   * This will delete 2.
   * 
   * @throws InvalidArgumentException 
   */
  public function __construct( 
    IPropertySvcConfig $cfg, 
    ISQLRepository $junctionRepo, 
    ISQLRepository $targetRepo, 
    bool $readOnlyTarget = false,
    bool $reverse = false,
    bool $manageDeletes = false )
  {    
    parent::__construct( $cfg, $junctionRepo, $targetRepo, $readOnlyTarget, $reverse, $manageDeletes );
  }
  
  
  /**
   * Returns the junction repository 
   * @return IRepository|null
   */
  public function getRepository() : ?IRepository
  {
    return $this->junctionRepo;
  }
  
  
  
  protected function create( array $data ) : IModel
  {
    return $this->getTargetRepo()->create( $data );
  }
  
  
  /**
   * Load models 
   * @param int $parentId Parent id 
   * @return IModel[] models 
   */
  protected function loadModels( int $parentId, IModel $parent ) : array
  {    
    $jCols = $this->getJunctionModelProps();
    $tCols = $this->getJunctionTargetProps();
    
    $out = [];
    
    $dbc = $this->getJunctionRepo()->getDatabaseConnection();    
    $q = 'select j.`%s` as `junction_parent_id`, t.* from `%s` j join `%s` t on (j.`%s`=t.`%s`) where j.`%s`=?';
    
    foreach( $dbc->select( 
      sprintf( $q,
        $jCols->getParentId(),
        $this->getJunctionRepo()->getTable(),
        $this->getTargetRepo()->getTable(),
        ( !$this->isReverse()) ? $jCols->getTargetId() : $jCols->getParentId(),
        $tCols->getId(),
        ( !$this->isReverse()) ? $jCols->getParentId() : $jCols->getTargetId()),
      [$parentId] ) as $row ) 
    {
      $out[] = $this->getTargetRepo()->create( $row, $this->isReadOnly());
    }
    
    return $out;
  }
}
