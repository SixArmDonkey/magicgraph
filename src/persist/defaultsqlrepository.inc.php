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

use buffalokiwi\magicgraph\DefaultModelMapper;
use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\property\IPropertySet;
use UI\Exception\InvalidArgumentException;



class DefaultSQLRepository extends SQLRepository
{
  /**
   * SQL Repository 
   * @param string $table Table name 
   * @param IDBConnection $dbc Database connection 
   * @param IPropertySet $properties Property set for models returned by this repo.  
   * @throws InvalidArgumentException
   */
  public function __construct( string $table, IDBConnection $dbc, string $modelClassName, IPropertySet $props )    
  {
    parent::__construct( $table, new DefaultModelMapper( function( IPropertySet $props ) use ($modelClassName) {
      return new $modelClassName( $props );
    }, $modelClassName ), $dbc, $props );
  }  
}

