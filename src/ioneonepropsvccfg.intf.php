<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

declare( strict_types=1 );

namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\property\IPropertySvcConfig;


/**
 * One to one property service config 
 */
interface IOneOnePropSvcCfg extends IPropertySvcConfig
{
  /**
   * Retrieve the linked repo 
   * @return IRepository Repo 
   */
  public function getRepository() : IRepository;
}

