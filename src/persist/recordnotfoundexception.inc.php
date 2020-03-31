<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\persist;

use Exception;
use Throwable;

/**
 * Thrown when something can't be found 
 */
class RecordNotFoundException extends Exception 
{
  public function __construct( string $message = "", int $code = 404, Throwable $previous = NULL )
  {
    parent::__construct( $message, $code, $previous );
  }
} 

