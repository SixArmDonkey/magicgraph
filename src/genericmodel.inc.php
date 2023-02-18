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

namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\property\DefaultConfigMapper;
use buffalokiwi\magicgraph\property\DefaultPropertySet;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\PropertyFactory;
use \InvalidArgumentException;


/**
 * A generic model.
 */
class GenericModel extends DefaultModel
{
  public function __construct( IPropertyConfig ...$config )
  {
    if ( empty( $config ))
      throw new InvalidArgumentException( 'config must not be empty' );
    
    parent::__construct( new DefaultPropertySet( new PropertyFactory( new DefaultConfigMapper()), ...$config ));
  }
}

