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

use Closure;


/**
 * Yes.  This is almost totally pointless.
 */
class Runnable implements IRunnable
{
  /**
   * Task 
   * @var Closure
   */
  private $task;
  
  public function __construct( ?Closure $task )
  {
    $this->task = $task;
  }
  
  
  /**
   * Execute the supplied closure 
   * @return void
   */
  public function run() : void
  {
    if ( $this->task != null )
    {
      $f = $this->task;
      $f();
    }
  }
}
