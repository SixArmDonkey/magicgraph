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
 * Property behavior for some property.  Extend this and override methods.
 * 

 */
class GenericNamedPropertyBehavior extends PropertyBehavior implements INamedPropertyBehavior
{
  /**
   * Property name 
   * @var string
   */
  private $name;
  
  
  /**
   * Create a new PropertyBehavior instance.
   * The PropertyBehavior constructor is called with zero arguments, therefore
   * the closures contained within are always null unless overridden.
   * 
   * WARNING: Possibility of shenanigans!
   * ====================================
   * 
   * Closures attached to this object SHOULD reference some variable equal to 
   * $this (inside the closure), instead of accessing $this directly.  
   * 
   * @param string $name Property name this behavior modifies.
   * Pass static::class from the descending class if this is part of a generic strategy.
   */
  public function __construct( string $name )
  {
    parent::__construct();
    
    if ( empty( $name ))
      throw new InvalidArgumentException( 'name must not be empty' );
    
    $this->name = $name;
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
   * @return Closure|null function f( IModel ) : void
   */
  public function getBeforeSaveCallback() : ?Closure
  {
    return null;
  }
  
  
  /**
   * Retrieve the after save function  
   * @return Closure|null function f( IModel ) : void
   */
  public function getAfterSaveCallback() : ?Closure
  {
    return null;
  }
  
  
  /**
   * Retrieve the model validation callback.
   * f( IModel $model ) throws ValidationException
   * @return Closure|null f( IModel $model ) throws ValidationException
   */
  public function getModelValidationCallback() : ?Closure
  {
    return null;
  }
}
