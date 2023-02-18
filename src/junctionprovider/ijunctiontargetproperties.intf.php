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


namespace buffalokiwi\magicgraph\junctionprovider;

/**
 * Defines properties to be attached to some target model for use with a junction
 * table repo.
 */
interface IJunctionTargetProperties extends \buffalokiwi\magicgraph\property\IPropertyConfig
{
  /**
   * Retrieve the primary key property name of the target model.
   * @return string name 
   */
  public function getId() : string;
}
