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

namespace buffalokiwi\magicgraph\persist\importexport;

use buffalokiwi\magicgraph\IModel;


abstract class Adapter implements IImportAdapter
{
  private array $handlers;
  
  public function __construct( IAdapterHandler ...$handlers )
  {
    $this->handlers = $handlers;
  }
  
  
  /**
   * Called before the import is started
   * @return void
   */
  public function initialize() : void
  {
    foreach( $this->handlers as $h )
    {
      $h->initialize();
    }
  }
  
  
  /**
   * Called when the import is completed.
   * This MUST NOT be called when an exception is thrown.
   * @return void
   */
  public function finalize() : void
  {
    foreach( $this->handlers as $h )
    {
      $h->finalize();
    }
  }
  
  
  /**
   * Called when an exception is thrown 
   * @return void
   */
  public function exception() : void
  {
    foreach( $this->handlers as $h )
    {
      $h->exception();
    }
  }
  
  
  protected function beforeSave( IModel $model ) : void
  {
    foreach( $this->handlers as $h )
    {
      $h->beforeSave( $model );
    }
  }    
}
