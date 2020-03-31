<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\persist;


/**
 * A condition group for a sql statement.
 * @deprecated Stupid
 */
class SQLConditionGroup implements ISQLConditionGroup
{
  /**
   * Conditions 
   * @var array 
   */
  private $conditions = [];
  
  /**
   * Add some condition to the group 
   * @param ICondition $condition
   * @param bool $or
   */
  public function addCondition( ICondition $condition, bool $or = false )
  {
    $this->conditions[] = [$condition, $or];
  }


  /**
   * Retrieve the condition group as a string 
   * @return string condition 
   */
  public function getCondition() : string
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
      return '(' . $out . ')';
    
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
