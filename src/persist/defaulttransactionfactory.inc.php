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


/**
 * A default transaction factory implementation.
 * This ONLY works for MySQL as that is currently the only storage engine I've
 * provided code for.
 */
class DefaultTransactionFactory extends TransactionFactory 
{
  public function __construct()
  {
    //..Sort reverse of nested level in the object hierarchy.  Top level goes last.
    parent::__construct([
      ISQLRunnable::class => function( ISQLRunnable ...$tasks ) { return new MySQLTransaction( ...$tasks ); },
      IRunnable::class => function( IRunnable ...$tasks ) { return new Transaction( ...$tasks ); }
    ]);
  }
}
