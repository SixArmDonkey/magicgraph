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

use Closure;

/**
 * Named property behavior is the same as property behavior, except that 
 * it adds a property name method.  This interface is used to define one or more
 * behavior instances per property.
 */
interface INamedPropertyBehavior extends IPropertyBehavior
{
  /**
   * Retrieve the property name associated with this behavior.
   * @return string
   */
  public function getPropertyName() : string;
  
  
  /**
   * Retrieve the model validation callback.
   * f( IModel $model ) throws ValidationException
   * @return Closure|null
   */
  public function getModelValidationCallback() : ?Closure;  
  

  /**
   * Retrieve the before save function 
   * f( IModel ) : void
   * @return Closure|null function 
   */
  public function getBeforeSaveCallback() : ?Closure;
  
  
  /**
   * Retrieve the after save function  
   * f( IModel ) : void
   * @return Closure|null function 
   */
  public function getAfterSaveCallback() : ?Closure;  
  
}
