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


namespace buffalokiwi\magicgraph\persist;

use buffalokiwi\magicgraph\DefaultModel;
use buffalokiwi\magicgraph\DefaultModelMapper;
use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\property\PropertyListSet;
use \InvalidArgumentException;


/**
 * Build a repo and associated models in a snazzy easy way, and only using
 * this one object.  WEEEEEEEEFOOOOOOOOO!
 */
class InlineSQLRepo extends SQLRepository
{
  public function __construct( string $table, IDBConnection $dbc, IProperty ...$properties )
  {
    if ( empty( $table ))
      throw new InvalidArgumentException( 'Table must not be empty' );
    else if ( empty( $properties ))
      throw new InvalidArgumentException( 'You must specify at least one property' );
    
    parent::__construct( 
      $table, 
      new DefaultModelMapper( function( IPropertySet $props ) {
        return new DefaultModel( $props );
      }, \buffalokiwi\magicgraph\IModel::class ),
      $dbc,
      new PropertyListSet( ...$properties )
    );              
  }
}