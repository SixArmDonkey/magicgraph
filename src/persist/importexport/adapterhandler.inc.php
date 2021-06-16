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

use buffalokiwi\magicgraph\IModel;


/**
 * Base handler that does nothing.
 */
class AdapterHandler implements IAdapterHandler
{
  /**
   * Called before the import is started
   * @return void
   */
  public function initialize() : void
  {
    //..Do nothing
  }
  
  
  /**
   * Called when the import is completed.
   * This MUST NOT be called when an exception is thrown.
   * @return void
   */
  public function finalize() : void
  {
    //..Do nothing
  }
  
  
  /**
   * Called when an exception is thrown 
   * @return void
   */
  public function exception() : void
  {
    //..Do nothing
  }
  
  
  /**
   * Optional before save handler 
   * @param IModel $model Model being saved 
   * @return void
   */
  public function beforeSave( IModel $model ) : void
  {
    //..Do nothing
  }
}
