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


namespace buffalokiwi\magicgraph\property;


/**
 * A property set that initializes with an array of IProperty instances 
 */
class PropertyListSet extends DefaultPropertySet
{
  /**
   * Create a new PropertyListSet
   * @param array $properties List of IProperty instances 
   * @throws InvalidArgumentException 
   */
  public function __construct( IProperty ...$properties )
  {
    parent::__construct( new PropertyList( $properties ));
  }
}
