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

namespace buffalokiwi\magicgraph\eav\search;

use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\persist\ISQLRepository;
use buffalokiwi\magicgraph\search\ISearchQueryBuilder;
use buffalokiwi\magicgraph\search\ISearchQueryGenerator;
use buffalokiwi\magicgraph\search\ISearchResults;
use buffalokiwi\magicgraph\search\MySQLSearchResults;

/**
 * Searches EAV tables.
 */
class MySQLEAVSearch implements IAttributeSearch
{
  /**
   * Search query generator 
   * @var MySQLEAVSearchQueryGenerator
   */
  private MySQLEAVSearchQueryGenerator $searchQuery;
  
  /**
   * Database connection 
   * @var IDBConnection 
   */
  private IDBConnection $dbc;
  
  
  /**
   * The entity repo 
   * @var ISQLRepository 
   */
  private ISQLRepository $entityRepo;
  
  
  /**
   * MySQLEAVSearch 
   * @param IDBConnection $dbc Database connection 
   * @param ISQLRepository $entityRepo Entity repository 
   * @param ISQLRepository $attrRepo Attribute repository 
   * @param ISQLRepository $attrGroupRepo Attribute group repository 
   * @param ISQLRepository $attrGroupLinkRepo Attribute group members repository 
   * @param ISQLRepository $attrValueRepo Attribute value repository 
   */
  public function __construct( 
    IDBConnection $dbc,
    ISQLRepository $entityRepo,
    ISQLRepository $attrRepo, 
    ISQLRepository $attrGroupRepo, 
    ISQLRepository $attrGroupLinkRepo,
    ISQLRepository $attrValueRepo,
    ?ISearchQueryGenerator $searchQueryGenerator = null )
  {
    $this->dbc = $dbc;
    $this->entityRepo = $entityRepo;
    
    if ( $searchQueryGenerator == null )
    {
      $this->searchQuery = new MySQLEAVSearchQueryGenerator( 
        $dbc, 
        $entityRepo, 
        $attrRepo, 
        $attrGroupRepo, 
        $attrGroupLinkRepo, 
        $attrValueRepo );    
    }
    else
      $this->searchQuery = $searchQueryGenerator;
  }
  
  
  /**
   * Retrieve the search query generator 
   * @return ISearchQueryGenerator generator 
   */
  public function getSearchQueryGenerator() : ISearchQueryGenerator
  {
    return $this->searchQuery;
  }  
  
  
  /**
   * Search for things.
   * An attribute search will search for entity or attribute values, and return a map of attribute code => value.
   * These results can be used to build models.
   * @param ISearchQueryBuilder $builder Search builder
   * @return ISearchResults 
   * @todo Remove EAV column constants.
   */
  public function search( ISearchQueryBuilder $query ) : ISearchResults
  {
    $f = function( ISearchQueryBuilder $query, bool $returnCount ) {
      $statement = $this->searchQuery->createQuery( $query, $returnCount );
     
      $build = [];

      $entityGroups = $query->getEntityGroups();
      
      foreach( $this->dbc->select( $statement->getQuery(), $statement->getValues()) as $row )
      {   
        
        $curGroup = '';
        foreach( $entityGroups as $g )
        {
          if ( isset( $row[$g] ))
            $curGroup .= $row[$g];
        }
        
        foreach( $row as $col => $val )
        {
          if ( $returnCount && $col == 'count' )
            return (int)$val;
          else if ( $returnCount )
            continue;
          
          //..DIRTY CODE
          //..This needs to be revised.
          if ( $col == 'code' )
          {
            $col = $val;
            $val = $row['value'];
          }

          //..This needs to be revised.
          if ( empty( $col ) || $col === null || $col == 'caption' || $col == 'value' )
            continue;
          
          
          $build[$curGroup][$row[$statement->getUniqueId()]][$col] = $val;
        }
        
        if ( $returnCount )
          return 0;
      }
      
      return $build;
    };
    
    $out = [];
    
    $build = $f( $query, false );
    
    foreach( $build as $group )
    {
      foreach( $group as $eid => $cols )
      {
        $model = $this->entityRepo->create();
        foreach ( $cols as $k => $v )
        {
          $model->setValue( $k, $v );
        }

        $out[] = $model;
      }
    }
    
    
    
    return new MySQLSearchResults( $query->getPage(), $query->getResultSize(), function() use($f, $query) {
      return $f( $query, true );
    }, ...$out );     
  }
}

