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

use buffalokiwi\magicgraph\IModelPropertyProvider;
use buffalokiwi\magicgraph\persist\ISQLRepository;
use buffalokiwi\magicgraph\persist\ITransactionFactory;
use buffalokiwi\magicgraph\persist\SQLServiceableRepository;
use Exception;

class MySQLAttrGroupRepo extends SQLServiceableRepository implements IAttrGroupRepo
{
  /**
   * Column defs 
   * @var IAttributeGroupPropertiesCols
   */
  private $config;
  
  
  public function __construct( ISQLRepository $repo, ITransactionFactory $tfact, IModelPropertyProvider ...$providers )
  {
    parent::__construct( $repo, $tfact, ...$providers );
    
    $this->config = $repo->createPropertySet()->getPropertyConfig( IAttributeGroupPropertiesCols::class );
  }
  
  
  /**
   * Retrieves the attribute group id with the lowest id value.
   * This is the default.
   * @return int
   * @todo Determine if this is really want we want to do here...  min(id) might not be the best approach.
   */
  public function getDefaultAttributeGroupId() : int
  {
    foreach( $this->getDatabaseConnection()->select( sprintf( 'select min(%s) as `m` from %s', $this->config->getIdColumn(), $this->getRepo()->getTable())) as $row )
    {
      return (int)$row['m'];
    }
    
    throw new Exception( 'No attribute groups have been defined.  Please define one to continue' );
  }
  
  
  /**
   * Retrieve a list of attribute group names keyed by group id.
   * @return array [id => name]
   */
  public function getGroupNameList() : array
  {
    $out = [];
    $dbc = $this->getRepo()->getDatabaseConnection();
    $sql = sprintf( 'select %1$s, %2$s from %3$s order by %2$s',
      $this->config->getIdColumn(), 
      $this->config->getNameColumn(), 
      $this->getRepo()->getTable());
    
    foreach( $dbc->select( $sql ) as $row )
    {
      $out[$row[$this->config->getIdColumn()]] = $row[$this->config->getNameColumn()];
    }
    
    return $out;    
  }
}
