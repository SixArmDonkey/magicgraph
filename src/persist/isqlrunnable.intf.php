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

use buffalokiwi\magicgraph\pdo\IDBConnection;


/**
 * A runnable with a database connection 
 */
interface ISQLRunnable extends IRunnable
{
  /**
   * Retrieve the database connection that should be used to execute the code.
   * @return IDBConnection connection 
   */
  public function getConnection() : IDBConnection;  
}
