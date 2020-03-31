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


namespace buffalokiwi\magicgraph\persist;

use Exception;


/**
 * A transaction for some storage engine.
 */
interface ITransaction extends IRunnable
{
  /**
   * Begins a transaction
   */
  public function beginTransaction() : void;
  
  
  /**
   * Rolls back an uncommitted transaction
   */
  public function rollBack() : void;
  
  
  /**
   * Commits a transaction after beginTransaction has been called
   * @throws Exception 
   */
  public function commit() : void;
}
