<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\persist;


/**
 * A SQL where statement 
 * @deprecated Stupid
 */
class SQLFilter implements ISQLFilter
{
  /**
   * Conditions 
   * @var array 
   */
  private $conditions = [];
  
  
  public function __construct( ICondition $condition = null )
  {
    if ( $condition != null )
      $this->conditions[] = [$condition, false];    
  }
  
  /**
   * Add some condition to the group 
   * @param ICondition $condition
   * @param bool $or
   */  
  public function addWhere( ICondition $condition, bool $or = false ) : void
  {
    $this->conditions[] = [$condition, $or];
  }


  /**
   * Retrieve the condition group as a string 
   * @return string condition 
   */
  public function getFilter() : string
  {  
    $out = '';
    foreach( $this->conditions as $data )
    {
      list( $condition, $or ) = $data;
      if ( !empty( $out ))
        $out .= ( $or ) ? ' or ' : ' and ';
      
      $out .= $condition->getCondition();      
    }
    
    if ( !empty( $out ))
      return 'where ' . $out;
    
    return $out;
  }  
  
  
  /**
   * Retrieve a list of values 
   * @return array values 
   */
  public function getValues() : array
  {
    $out = [];
    foreach( $this->conditions as $data )
    {
      list( $condition, $or ) = $data;
      $out = array_merge( $out, $condition->getValues());
    }
    
    return $out;    
  }  
}
