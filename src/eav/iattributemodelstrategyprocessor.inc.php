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
use buffalokiwi\magicgraph\ValidationException;


interface IAttributeModelStrategyProcessor
{
  /**
   * Called prior to Attribute models being saved
   * @throws ValidationException for rollback
   */
  public function processBeforeSaveAttributeModel( IRepository $repo, IModel $parentModel, IAttributeModel ...$modelList ) : void;
  
  
  /**
   * Called after Attribute models are saved
   * @throws ValidationException for rollback
   */
  public function processAfterSaveAttributeModel( IRepository $repo, IModel $parentModel, IAttributeModel ...$modelList ) : void;
  
  
  /**
   * Called prior to Attribute values being saved
   * @throws ValidationException for rollback
   */
  public function processBeforeSaveAttributeValue( IRepository $repo, IModel $parentModel, IAttrValue ...$modelList ) : void;


  /**
   * Called after Attribute values are saved
   * @throws ValidationException for rollback
   */
  public function processAfterSaveAttributeValue( IRepository $repo, IModel $parentModel, IAttrValue ...$modelList ) : void;
}
