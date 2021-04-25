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

namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\property\IPropertySvcConfig;


interface IOneManyPropSvcCfg extends IPropertySvcConfig
{
  /**
   * Retrieve the linked model repository 
   * @return IRepository Repo 
   */
  public function getRepository() : IRepository;


  /**
   * retrieve the (idproperty argument in constructor) foreign key property name.  This will be the property name 
   * that contains the id of the parent model, and is used for lookups.
   * @return string property name 
   */
  public function getForeignKey() : string;  
}

