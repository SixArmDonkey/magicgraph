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

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\ValidationException;
use Closure;



/**
 * This can be used to add behavior to the events:
 * 
 * Attribut model save - before and after
 * Attribute value save - before and after
 * 
 * An attribute model is an object that implements IAttributeModel
 * 
 */
interface IAttributeModelServiceSaveStrategy
{
  /**
   * Called prior to Attribute models being saved
   * @return Closure|null f( IRepository $repo, IModel $entity, IAttributeModel ...$models ) : void
   * @throws ValidationException for rollback
   */
  public function getBeforeSaveAttributeModel() : ?Closure;
  
  
  /**
   * Called after Attribute models are saved
   * @return Closure|null f( IRepository $repo, IModel $entity, IAttributeModel ...$models ) : void
   * @throws ValidationException for rollback
   */
  public function getAfterSaveAttributeModel() : ?Closure;
  
  
  /**
   * Called prior to Attribute values being saved
   * @return Closure|null f( IRepository $repo, IModel $entity, IAttrValue ...$models ) : void
   * @throws ValidationException for rollback
   */
  public function getBeforeSaveAttributeValue() : ?Closure;


  /**
   * Called after Attribute values are saved
   * @return Closure|null f( IRepository $repo, IModel $entity, IAttrValue ...$models ) : void
   * @throws ValidationException for rollback
   */
  public function getAfterSaveAttributeValue() : ?Closure;
}
