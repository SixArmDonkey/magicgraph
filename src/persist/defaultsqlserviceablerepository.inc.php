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
use buffalokiwi\magicgraph\IModelPropertyProvider;
use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\property\IPropertySet;


class DefaultSQLServiceableRepository extends SQLServiceableRepository
{
  private array $providers;
  
  public function __construct( string $table, IDBConnection $dbc, string $modelClassName, IPropertySet $props, ITransactionFactory $tfact, IModelPropertyProvider ...$providers ) 
  {    
    parent::__construct(
      new SQLRepository(
        $table,
        new DefaultModelMapper( function( IPropertySet $props ) use ($modelClassName) {
          return new $modelClassName( $props, ...$this->providers );
        },
        $modelClassName ),
        $dbc,
        $props ),
      $tfact,
      ...$providers
    );    
        
    $this->providers =& $this->getProviders();
  }
}
