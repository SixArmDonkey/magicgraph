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

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\persist\IRepository;



/**
 * An attribute model service for obtaining IModel instances
 * with additional/virtual attributes attached.
 */
interface IAttributeModelService extends IRepository
{
  /**
   * Retrive the attribute repository 
   * @return IAttributeRepo Attribute repo
   */
  public function getAttributeRepo() : IAttributeRepo;  
}
