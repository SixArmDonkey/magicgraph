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

use buffalokiwi\magicgraph\persist\ISQLRepository;


/**
 * Responsible for searching the main and attribute repositories in a single operation, and returning 
 * raw data payloads to the attribute model service, where the data is built into an IModel instance.
 * 
 * This class should not be accessed directly, and instead passed to an instance of IAttributeModelService.
 */
class AttributeModelSearchAdapter 
{
  /**
   * Main repository.
   * @var ISQLRepository 
   */
  private ISQLRepository $mainRepo;
  
  /**
   * Attribute repo 
   * @var ISQLRepository 
   */
  private ISQLRepository $attrRepo;
  
  
  /**
   * MySQLAttributeModelSearchAdapter
   * @param ISQLRepository $mainRepo
   * @param ISQLRepository $attrRepo
   */
  public function __construct( ISQLRepository $mainRepo, ISQLRepository $attrRepo )
  {
    $this->mainRepo = $mainRepo;
    $this->attrRepo = $attrRepo;
  }
  
  
  /**
   * Search by core model properties and/or attributes.
   * @param array $map Map of [property => value] and used as search criteria.
   * @return array A map of property => value.  Used to build IModel instances.
   */
  public function search( array $map ) : array
  {
    //..This is a modified version of the code contained in MySQLAttributeRepo::getEntityIdsByAttributeValue()
    
    $values = [];
    $where = [];
    $joins = [];
    
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
}

