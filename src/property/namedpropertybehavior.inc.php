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

use Closure;
use InvalidArgumentException;



/**
 * Property behavior for some property.
 * Attach this to a property service to add additional functionality.
 */
class NamedPropertyBehavior extends PropertyBehavior implements INamedPropertyBehavior
{
  /**
   * Property name 
   * @var string
   */
  private $name;
  
  /**
   * Before save event 
   * @var ?Closure
   */
  private $beforeSave;
  
  /**
   * After save event 
   * @var ?Closure 
   */
  private $afterSave;
  
  /**
   * Model validation 
   * @var ?Closure
   */
  private $mValidate;
  
  
  /**
   * Create a new PropertyBehavior instance.
   * 
   * WARNING: Possibility of shenanigans!
   * ====================================
   * 
   * Closures attached to this object SHOULD reference some variable equal to 
   * $this (inside the closure), instead of accessing $this directly.  
   * 
   * @param NamedPropertyBehaviorBuilder $builder arguments 
   */
  public function __construct( NamedPropertyBehaviorBuilder $builder )
  {
    parent::__construct( 
      $builder->getValidate(),
      $builder->getInit(),
      $builder->getSetter(),
      $builder->getGetter(),
      $builder->getModelSetter(),
      $builder->getModelGetter());
    
    if ( empty( $builder->getName()))
      throw new InvalidArgumentException( 'name must not be empty' );
    
    $this->name = $builder->getName();
    $this->beforeSave = $builder->getBeforeSave();
    $this->afterSave = $builder->getAfterSave();
    $this->mValidate = $builder->getModelValidation();
  }
  
  
  /**
   * Retrieve the property name associated with this behavior.
   * @return string
   */
  public function getPropertyName() : string
  {
    return $this->name;
  }
  
  
  /**
   * Retrieve the before save function 
   * @return Closure|null function 
   */
  public function getBeforeSaveCallback() : ?Closure
  {
    return $this->beforeSave;
  }
  
  
  /**
   * Retrieve the after save function  
   * @return Closure|null function 
   */
  public function getAfterSaveCallback() : ?Closure
  {
    return $this->afterSave;
  }
  
  
  /**
   * Retrieve the model validation callback.
   * f( IModel $model ) throws ValidationException
   * @return \buffalokiwi\magicgraph\property\Closure|null
   */
  public function getModelValidationCallback() : ?Closure
  {
    return $this->mValidate;
  }
}
