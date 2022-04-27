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


/**
 * Functional implementation of IPropertyBehavior.
 * This is an immutable DTO containing several optional event handlers defined via the constructor.
 */
class PropertyBehavior implements IPropertyBehavior
{
  /**
   * Validate data closure for save() 
   * [bool is valid] = function( IProperty, [input value] )
   * @var Closure|null
   */
  private $validate;
   
  /**
   * Get value callback 
   * @var Closure|null
   */
  private $getter;  
  
  /**
   * Set value callback 
   * @var Closure|null
   */
  private $setter;
  
  /**
   * Get value callback 
   * @var Closure|null
   */
  private $mgetter;  
  
  /**
   * Set value callback 
   * @var Closure|null
   */
  private $msetter;
    
  /**
   * Initializer callback 
   * @var Closure|null
   */
  private $init;  
  
  /**
   * On Change event 
   * @var Closure|null 
   */
  private $onChange;
  
  /**
   * Get html input event 
   * @var Closure|null
   */
  private $htmlInput;
  
  /**
   * Is empty event 
   * @var Closure|null 
   */
  private $isEmpty;
  
  /**
   * Toarray event  
   * @var Closure|null
   */
  private $toArray;
  
  
  
  /**
   * Create a new PropertyBehavior instance.
   * 
   * WARNING: Possibility of shenanigans!
   * ====================================
   * 
   * When using non-staic closures:
   * Closures attached to this object SHOULD reference some variable equal to 
   * $this (inside the closure), instead of accessing $this directly.  
   * 
   * @param Closure|null $validate Validate closure f( IProperty, $value ) 
   * Throw a ValidationException on error 
   * @param Closure|null $init f( value ) : mixed 
   * @param Closure|null $setter f( IProperty, value ) : mixed 
   * @param Closure|null $getter f( IProperty, value ) : mixed
   * @param Closure|null $msetter f( IModel, IProperty, value ) : mixed
   * @param Closure|null $mgetter f( IModel, IProperty, value ) : mixed
   * @param Closure|null $onChange f( IProperty, oldValue, newValue ) : void
   * @param Closure|null $isEmpty f( IProperty ) : bool
   * @param Closure|null $htmlInput f( IModel $model, IProperty $property, string $name, string $id, string $value ) : IElement
   * @param Closure|null $toArray f( IModel, IProperty, value ) : mixed
   */
  public function __construct( ?Closure $validate = null, ?Closure $init = null, ?Closure $setter = null, 
    ?Closure $getter = null, ?Closure $msetter = null, ?Closure $mgetter = null, ?Closure $onChange = null,
    ?Closure $isEmpty = null, ?Closure $htmlInput = null, ?Closure $toArray = null )
  {
    $this->validate = $validate;
    $this->init = $init;    
    $this->setter = $setter;
    $this->getter = $getter;
    $this->msetter = $msetter;
    $this->mgetter = $mgetter;
    $this->onChange = $onChange;
    $this->isEmpty = $isEmpty;
    $this->htmlInput = $htmlInput;
    $this->toArray = $toArray;
  }
  
  
  public function __clone()
  {
    //..This might not be so good.
    //..If the closures refernce $this, then $this will change.
    //..Closures should probably reference some value equal to whatever $this was or be static.
    if ( $this->validate != null )
      $this->validate = $this->validate->bindTo( $this );      
    if ( $this->init != null )
      $this->init = $this->init->bindTo( $this );      
    if ( $this->setter != null )
      $this->setter = $this->setter->bindTo( $this );      
    if ( $this->getter != null )
      $this->getter = $this->getter->bindTo( $this );      
    if ( $this->msetter != null )
      $this->msetter = $this->msetter->bindTo( $this );      
    if ( $this->mgetter != null )
      $this->mgetter = $this->mgetter->bindTo( $this );      
    if ( $this->onChange != null )
      $this->onChange = $this->onChange->bindTo( $this );
    if ( $this->isEmpty != null )
      $this->isEmpty = $this->isEmpty->bindTo( $this );
    if ( $this->htmlInput != null )
      $this->htmlInput = $this->htmlInput->bindTo( $this );
    if ( $this->toArray != null )
      $this->toArray = $this->toArray->bindTo( $this );
  }
  
  
  /**
   * Retrieve the onChange callback
   * f( IProperty, oldValue, newValue ) : void
   * @return Closure|null
   */
  public function getOnChangeCallback() : ?Closure
  {
    return $this->onChange;
  }
  
  
  /**
   * Retrieve the closure that can test empty state.
   * @return Closure|null 
   */
  public function getIsEmptyCallback() : ?Closure
  {
    return $this->isEmpty;
  }
  
  
  /**
   * Callback used to provide additional validation for some property.
   * This is called prior to IProperty::validate()
   * 
   * 
   * function( IProperty, [input value] ) : bool 
   * 
   * @return Closure callback
   */
  public function getValidateCallback() : ?Closure
  {
    return $this->validate;
  }
    

  /**
   * Callback used to set a value.
   * This is called prior to IProperty::validate() and the return value will 
   * replace the supplied value.
   * 
   * f( IProperty, value ) : mixed
   * 
   * @return Closure callback
   */
  public function getSetterCallback() : ?Closure
  {
    return $this->setter;
  }
  
  
  /**
   * Callback used when retrieving a value.
   * Called during IProperty::getValue().  The return value will be used
   * as the return value from getValue();
   * f( IProperty, value ) : mixed 
   * 
   * @return Closure|null
   */
  public function getGetterCallback() : ?Closure
  {
    return $this->getter;
  }
  
  
  /**
   * Callback used to set a value.
   * This is called prior to IProperty::validate() and the return value will 
   * replace the supplied value.
   * 
   * f( IModel, IProperty, value ) : mixed 
   * 
   * @return Closure callback
   */
  public function getModelSetterCallback() : ?Closure
  {
    return $this->msetter;
  }
  
  
  /**
   * Callback used when retrieving a value.
   * Called during IProperty::getValue().  The return value will be used
   * as the return value from getValue();
   * f( IModel, IProperty, value ) : mixed 
   * 
   * @return Closure|null
   */
  public function getModelGetterCallback() : ?Closure
  {
    return $this->mgetter;
  }  
    
  
  /**
   * Callback used for initializing some value when the model is loaded.
   * The return value will be used as the default value for the property.
   * f( $value ) : mixed 
   * @return Closure callback 
   */
  public function getInitCallback() : ?Closure
  {
    return $this->init;
  }
  
  
  /**
   * Retrieve a callback that converts a property into an html form input.
   * 
   * f( IModel $model, IProperty $property, string $name, string $id, $value ) : IElement
   * 
   * @return Closure|null
   */
  public function getHTMLInputCallback() : ?Closure
  {
    return $this->htmlInput;
  }
  
  
  /**
   * Callback used when retrieving a value within IModel::toArray().
   * When the value used within the application differs from the persisted value, this can be used to 
   * modify the persisted value.
   * This will always be called after GETTER and MGETTER.
   * f( IModel, IProperty, mixed $value ) : mixed 
   * @return Closure|null
   */  
  public function getToArrayCallback() : ?Closure
  {
    return $this->toArray;
  }
}
