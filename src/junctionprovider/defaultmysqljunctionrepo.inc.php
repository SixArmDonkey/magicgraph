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

namespace buffalokiwi\magicgraph\junctionprovider;

use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\persist\DefaultSQLRepository;
use buffalokiwi\magicgraph\property\DefaultConfigMapper;
use buffalokiwi\magicgraph\property\DefaultPropertySet;
use buffalokiwi\magicgraph\property\PropertyFactory;


/**
 * Default mysql junction repository 
 * This will use IJunctionModel and JunctionModelProperties 
 */
class DefaultMySQLJunctionRepo extends DefaultSQLRepository
{
  public function __construct( string $table, IDBConnection $dbc )
  {
    parent::__construct(
      $table,
      $dbc,
      IJunctionModel::class,
      new DefaultPropertySet( new PropertyFactory( new DefaultConfigMapper()), new JunctionModelProperties()));
  }
}

