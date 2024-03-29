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

use buffalokiwi\magicgraph\IModel;



/**
 * A property backed by an IModel instance 
 */
interface IModelProperty extends IProperty
{
  /**
   * Get the value as a model.
   */
  public function getValueAsModel() : ?IModel;
}

