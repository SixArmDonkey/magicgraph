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
 * Save functions are used to include save functionality from service 
 * providers or plugins within some repository for some model object.
 * 
 * When a Repository or repo decorator supporting service providers is used, 
 * The getSaveFunction() method is called, which returns save functions from 
 * various service providers attached to the repo.  
 */
interface ISaveFunction
{
  /**
   * Retrieve a list of IRunnable that contain code for saving things.
   * @return array
   */
  public function getSaveFunction() : array;
}
