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

use Exception;


/**
 * Import adapters are called for each entry in some file to be imported.
 */
interface IImportAdapter 
{
  /**
   * Execute something against a row to be imported.
   * This is where the row should be saved.
   * @param array $data Data to be imported
   * @return void
   * @throws Exception
   */
  public function execute( array $data ) : void;
  
  
  /**
   * Called before the import is started
   * @return void
   */
  public function initialize() : void;
  
  
  /**
   * Called when the import is completed.
   * This MUST NOT be called when an exception is thrown.
   * @return void
   */
  public function finalize() : void;
  
  
  /**
   * Called when an exception is thrown 
   * @return void
   */
  public function exception() : void;  
}

