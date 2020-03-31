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

use InvalidArgumentException;


/**
 * SQL Order and limit statement 
 * @deprecated Stupid
 */
class SQLRows implements IRows
{
  /**
   * Order by column name 
   * @var string
   */
  private $order;
  
  /**
   * Start offset
   * @var int
   */
  private $start;
  
  /**
   * Number of rows to return 
   * @var int 
   */
  private $rows;
  
  
  /**
   * Create a new SQLRows instance 
   * @param string $order Order by column name 
   * @param int $start start offset
   * @param int $rows Number of rows to return 
   * @throws InvalidArgumentException
   */
  public function __construct( string $order, int $start, int $rows )
  {
    if ( $start < 0 )
      throw new InvalidArgumentException( 'start must be greater than or equal to zero' );
    else if ( $rows <= 0 )
      throw new InvalidArgumentException( 'rows must be greater than zero' );
    
    $this->order = $order;
    $this->start = $start;
    $this->rows = $rows;
  }
  
  
  /**
   * Retrieve the attribute used to sort
   * @return string attribute name
   */
  public function getOrderBy() : string
  {
    return $this->order;
  }
  
  
  /**
   * Retrieve the start offset 
   * @return int offset
   */
  public function getStart() : int
  {
    return $this->start;
  }
  
  
  /**
   * Retrieve the result set size
   * @return int size
   */
  public function getRows() : int
  {
    return $this->rows;
  }
  
  
  /**
   * Retrieve this part as a string
   * @return string statement 
   */
  public function getStatement() : string
  {
    $out = '';
    if ( !empty( $this->order ))
      $out = 'order by ' . $this->order;
    if ( $this->start > 0 && $this->rows > 0 )
      $out .= ' limit ' . $this->start . ',' . $this->rows;
    else if ( $this->start <= 0 && $this->rows > 0 )
      $out .= ' limit ' . $this->rows;
    
    return $out;
  }  
  
  /**
   * Retrieve this part as a string
   * @return string statement 
   */
  public function __toString()
  {
    return $this->getStatement();
  }
}
