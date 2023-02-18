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
 * The standard property set uses a PropertyFactory and DefaultConfigMapper, so IPropertyConfig
 * instances can be assigned to IModel using the default MagicGraph implementation.
 */
class StandardPropertySet extends DefaultPropertySet implements IPropertySet
{
  /**
   * Create a new StandardPropertySet 
   * @param IPropertyConfig $config One or more property config instances 
   */
  public function __construct( IPropertyConfig ...$config )
  {
    parent::__construct( new MappedPropertyFactory( new DefaultConfigMapper()), ...$config );
  }
}
