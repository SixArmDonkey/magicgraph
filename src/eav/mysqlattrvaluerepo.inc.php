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
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\IModelMapper;
use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\persist\SQLRepository;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\ValidationException;
use \InvalidArgumentException;


class MySQLAttrValueRepo extends SQLRepository implements IAttrValueRepo
{
  /**
   * Columns 
   * @var IAttrValueCols
   */
  private $cols;
  
  
  /**
   * Create a new SQLRepository instance 
   * @param string $table Table name 
   * @param IModelMapper $mapper Mapper 
   * @param IDBConnection $dbc Database connection 
   * @throws InvalidArgumentException
   */
  public function __construct( string $table, IModelMapper $mapper, IDBConnection $dbc, ?IPropertySet $properties = null, string $cols = IAttrValueCols::class )
  {
    parent::__construct( $table, $mapper, $dbc, $properties );
    
    if ( $properties == null )
      $properties = $mapper->createAndMap( [] )->getPropertySet();
    
    $this->cols = $properties->getPropertyConfig( $cols );
  }
  
  
  /**
   * Retrieve a list of attribute values for some list of entity id's.
   * @param int $entityId One or more entity ids 
   * @return array [entity id => [attr id => value]]
   * @throws InvalidArgumentException 
   */
  public function getAttributeValues( int ...$entityId ) : array
  {
    $cols = [
      $this->cols->getEntityId(),
      $this->cols->getAttributeId(),
      $this->cols->getValue()
    ];
    
    $dbc = $this->getDatabaseConnection();
    
    $out = [];
    foreach( $dbc->select( sprintf(
      'select %s from %s where %s in %s',
      implode( ',', $cols ),
      $this->getTable(),
      $this->cols->getEntityId(),
      $dbc->prepareIn( $entityId )),
      $entityId ) as $row )
    {
      if ( !isset( $out[$row[$this->cols->getEntityId()]] ))
        $out[$row[$this->cols->getEntityId()]] = [];
      
      $out[$row[$this->cols->getEntityId()]][$row[$this->cols->getAttributeId()]] = $row[$this->cols->getValue()];
    }
    
    return $out;
  }  
}
