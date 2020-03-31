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

namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\persist\IRunnable;
use buffalokiwi\magicgraph\persist\Runnable;


/**
 * A one to one read only property service.
 * The attached model will not be saved if edits are made.
 */
class OneOneROPropertyService extends OneOnePropertyService
{
  /**
   * Test to see if this model is valid prior to save()
   * @throws ValidationException
   */
  public function validate( IModel $model ) : void
  {
    //..do nothing
  }
  
  
  /**
   * Retrieve the save function used for saving stuff from the provider.
   * This does nothing in this implementation.
   * @param IModel $parent
   * @return IRunnable[]
   */
  public function getSaveFunction( IModel $parent ) : array
  {
    return [];
  }  
}
