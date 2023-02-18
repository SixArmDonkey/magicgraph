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

namespace buffalokiwi\magicgraph\property;


/**
 * An integer property flagged as a primary key with a default value of zero.
 */
class PrimaryIntegerProperty extends DefaultIntegerProperty
{
  public function __construct( string $name )
  {
    parent::__construct( $name, 0, null, SPropertyFlags::PRIMARY );
  }
}

