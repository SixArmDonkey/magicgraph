<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\pdo;

use Closure;


class PDOConnectionFactory extends ConnectionFactory
{
  /**
   * Create connection supplier 
   * @var Closure
   */
  private $create;
  
  
  /**
   * 
   * @param IConnectionProperties $args Arguments
   * @param Closure $createConnection Supplier that creates instances of a db connection
   * f( IConnectionProperties $args ) : IDBConnection
   */
  public function __construct( IConnectionProperties $args, Closure $createConnection )
  {
    parent::__construct($args);
    $this->create = $createConnection;
  }
  
  /**
   * Create a new connection 
   * @param IConnectionProperties $args
   */
  protected function createNewConnection( IConnectionProperties $args ): IDBConnection 
  {
    $c = $this->create;
    return $c( $args );
  }
}
