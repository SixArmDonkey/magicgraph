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



class GenericAttributeModelServiceSaveStrategy implements IAttributeModelServiceSaveStrategy
{
  /**
   * Called prior to Attribute models being saved
   * @return Closure|null f( IRepository $repo, IModel $entity, IAttributeModel ...$models ) : void
   * @throws ValidationException for rollback
   */
  public function getBeforeSaveAttributeModel() : ?Closure
  {
    return null;
  }
  
  
  /**
   * Called after Attribute models are saved
   * @return Closure|null f( IRepository $repo, IModel $entity, IAttributeModel ...$models ) : void
   * @throws ValidationException for rollback
   */
  public function getAfterSaveAttributeModel() : ?Closure
  {
    return null;
  }
  
  
  /**
   * Called prior to Attribute values being saved
   * @return Closure|null f( IRepository $repo, IModel $entity, IAttrValue ...$models ) : void
   * @throws ValidationException for rollback
   */
  public function getBeforeSaveAttributeValue() : ?Closure
  {
    return null;
  }


  /**
   * Called after Attribute values are saved
   * @return Closure|null f( IRepository $repo, IModel $entity, IAttrValue ...$models ) : void
   * @throws ValidationException for rollback
   */
  public function getAfterSaveAttributeValue() : ?Closure
  {
    return null;
  }
}
