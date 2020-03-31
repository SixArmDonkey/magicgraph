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
 * A property that holds an instance of some object.
 * The getClass() method returns the fully qualified class name of the stored 
 * object instance.
 */
interface IObjectProperty extends IProperty
{
  /**
   * Retrieve the class or interface name of the stored object instance.
   * @return string class name
   */
  public function getClass() : string;
}
