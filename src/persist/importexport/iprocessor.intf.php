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


/**
 * Processes something 
 */
interface IProcessor 
{
  /**
   * Process imports or exports 
   * @return void
   */
  public function process() : void;
}
