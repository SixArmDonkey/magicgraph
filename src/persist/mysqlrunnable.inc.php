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

namespace buffalokiwi\magicgraph\persist;

use Closure;


class MySQLRunnable implements ISQLRunnable
{
  /**
   * SQL Repository 
   * @var ISQLRepository
   */
  private $repo;
   
  /**
   * On Execute
   * @var \Closure 
   */
  private $run;
  
  
  /**
   * 
   * @param \buffalokiwi\magicgraph\persist\ISQLRepository $repo Repo the run() method 
   * should run against.
   * @param Closure $run Code to execute when run() is called.
   */
  public function __construct( ISQLRepository $repo, Closure $run )
  {
    $this->repo = $repo;
    $this->run = $run;
  }
  
  
  public function getConnection() : \buffalokiwi\magicgraph\pdo\IDBConnection
  {
    return $this->repo->getDatabaseConnection();
  }
  
  
  /**
   * Execute the supplied closure 
   * @return void
   */
  public function run() : void
  {
    $f = $this->run;
    $f();
  }
}
