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

namespace buffalokiwi\magicgraph\persist\importexport;

use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\pdo\TransactionUnit;


/**
 * When used with ISQLRepository, this will import everything in a single transaction. 
 * Exceptions will cause a rollback.
 */
class SingleTransactionHandler extends AdapterHandler 
{
  private ?TransactionUnit $unit = null;
  private IDBConnection $dbc;
  
  
  public function __construct( IDBConnection $dbc )
  {
    $this->dbc = $dbc;
  }
  
  /**
   * Called before the import is started
   * @return void
   */
  public function initialize() : void
  {
    $this->unit = new TransactionUnit( $this->dbc );    
  }
  
  
  /**
   * Called when the import is completed.
   * This MUST NOT be called when an exception is thrown.
   * @return void
   */
  public function finalize() : void
  {
    if ( $this->unit != null )
      $this->unit->commit();
  }
  
  
  /**
   * Called when an exception is thrown 
   * @return void
   */
  public function exception() : void
  {
    if ( $this->unit != null )
      $this->unit->rollBack();
  }
}
