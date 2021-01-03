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
 * A builder object for the NamedPropertyBehavior class.
 * NamedPropertyBehavior accepts an instance of this object as a 
 * constructor argument.
 */
class NamedPropertyBehaviorBuilder
{
  /**
   * Property name 
   * @var string
   */
  private $name;
  
  /**
   * Validate data closure for save() 
   * [bool is valid] = function( IProperty, [input value] )
   * @var Closure
   */
  private $validate = null;
   
  /**
   * Get value callback 
   * @var Closure
   */
  private $getter = null;
  
  
  /**
   * Set value callback 
   * @var Closure
   */
  private $setter = null;
  
  /**
   * Get value callback 
   * @var Closure
   */
  private $mgetter = null;
  
  
  /**
   * Set value callback 
   * @var Closure
   */
  private $msetter = null;
  
  
  /**
   * Initializer callback 
   * @var Closure
   */
  private $init = null;
  
  
  /**
   * Before save 
   * @var Closure 
   */
  private $beforeSave = null;
  
  
  /**
   * After save 
   * @var Closure
   */
  private $afterSave = null;
  
  /**
   * Model Validation 
   * @var ?Closure
   */
  private $mValidate = null;
  
  /**
   * To array callback 
   * @var ?Closure 
   */
  private $toArray = null;
  
  
  /**
   * @param Closure|null $onChange f( IProperty, oldValue, newValue ) : void
   */
  private $onChange = null;
   
  /**
   * @param Closure|null $isEmpty f( IProperty ) : bool
   */
  private $isEmpty = null;
  
  /**
   * @param Closure|null $htmlInput f( IModel $model, IProperty $property, string $name, string $id, string $value ) : IElement
   */
  private $htmlInput = null;
  
  
  /**
   * Create a new property behavior builder
   * @param string $name Property name.  All supplied functions will only apply 
   * to the specified name.
   * @throws \InvalidArgumenException If name is empty 
   */    
  public function __construct( string $name )
  {
    if ( empty( $name ))
      throw new \InvalidArgumenException( 'name must not be empty' );
    
    $this->name = $name;
  }
  
  
  /**
   * Retrieve the property name 
   * @return string name 
   */
  public function getName() : string
  {
    return $this->name;
  }
  
  
  /**
   * Property change event 
   * @param Closure $f f( IProperty, oldValue, newValue ) : void
   * @return \self this
   */
  public function withChange( Closure $f ) : self
  {
    $this->onChange = $f;
    return $this;
  }
  
  
  /**
   * Get the property change event 
   * @return Closure|null
   */
  public function getOnChange() : ?Closure
  {
    return $this->onChange;
  }
  
  
  /**
   * Set the is empty check event 
   * @param Closure $f f( IProperty ) : bool
   * @return \self this
   */
  public function withIsEmpty( Closure $f ) : self
  {
    $this->isEmpty = $f;
    return $this;
  }
  
  
  /**
   * Get the empty change event 
   * @return Closure|null
   */
  public function getIsEmpty() : ?Closure
  {
    return $this->isEmpty;
  }
  
  
  /**
   * Set the html input generator callback.
   * This is used to generate an html input for the property 
   * @param Closure $f f( IModel $model, IProperty $property, string $name, string $id, string $value ) : IElement
   * @return \self this
   */
  public function withHtmlInput( Closure $f ) : self
  {
    $this->htmlInput = $f;
    return $this;
  }
  
  
  /**
   * Get the html input generator callback 
   * @return Closure|null
   */
  public function getHtmlInput() : ?Closure
  {
    return $this->htmlInput;
  }
  
    
  /**
   * Validate some property value
   * IProperty $prop Property object
   * mixed $value The property value 
   * return is valid 
   * throws ValidationException 
   * 
   * f( IProperty $prop, mixed $value ) : bool 
   * 
   * @param Closure $f function 
   * @return \self this 
   */
  public function withValidate( Closure $f ) : self
  {
    $this->validate = $f;
    return $this;
  }
  
  
  /**
   * Retrieve the validate closure 
   * @return Closure|null
   */
  public function getValidate() : ?Closure
  {
    return $this->validate;
  }
  
  
  /**
   * Modify the value of some property on get.
   * 
   * IProperty $prop Property object
   * mixed $value The property value 
   * f( IProperty $prop, mixed $value ) : void
   * 
   * @param Closure $f function 
   * @return \self this 
   */
  public function withGetter( Closure $f ) : self
  {
    $this->getter = $f;
    return $this;
  }
  
  
  /**
   * Retrieve the getter function 
   * @return Closure|null function 
   */
  public function getGetter() : ?Closure
  {
    return $this->getter;
  }
  
  
  
  /**
   * Modify the value of some property prior to committing the value to the 
   * underlying property.
   * 
   * IProperty $prop Property object
   * mixed $value The property value 
   * f( IProperty $prop, mixed $value ) : void
   * 
   * @param Closure $f function
   * @return \self this 
   */  
  public function withSetter( Closure $f ) : self
  {
    $this->setter = $f;
    return $this;
  }
  
  
  /**
   * Retrieve the setter function 
   * @return Closure|null function 
   */
  public function getSetter() : ?Closure
  {
    return $this->setter;
  }
  
  
  /**
   * Modify the value of some property on get.
   * 
   * Called prior to getter callback.
   * 
   * IModel $model The model containing the property 
   * IProperty $prop Property object
   * mixed $value The property value 
   * f( IModel $model, IProperty $prop, mixed $value ) : void
   * 
   * @param Closure $f function
   * @return \self this 
   */  
  public function withModelGetter( Closure $f ) : self
  {
    $this->mgetter = $f;
    return $this;
  }
  
  
  /**
   * Retrieve the model getter function 
   * @return Closure|null function 
   */
  public function getModelGetter() : ?Closure
  {
    return $this->mgetter;
  }
  
  
  /**
   * Callback used when retrieving a value within IModel::toArray().
   * When the value used within the application differs from the persisted value, this can be used to 
   * modify the persisted value.
   * This will always be called after GETTER and MGETTER.
   * f( IModel, IProperty, mixed $value ) : mixed 
   * @return \self this 
   */    
  public function withToArray( Closure $f ) : self 
  {
    $this->toArray = $f;
    return $this;
  }
  
  
  /**
   * Retrive to toArray callback
   * @return Closure|null
   */
  public function getToArray() : ?Closure 
  {
    return $this->toArray;
  }
  
  
  /**
   * Modify the value of some property prior to committing the value to the 
   * underlying property.
   * 
   * Called prior to setter callback.
   * 
   * IModel $model The model containing the property 
   * IProperty $prop Property object
   * mixed $value The property value 
   * f( IModel $model, IProperty $prop, mixed $value ) : void
   * 
   * @param Closure $f function
   * @return \self this 
   */  
  public function withModelSetter( Closure $f ) : self
  {
    $this->msetter = $f;
    return $this;
  }
  
  
  /**
   * Get the model setter function 
   * @return Closure|null function 
   */
  public function getModelSetter() : ?Closure
  {
    return $this->msetter;
  }
  
  
  /**
   * A callback used to initialize the default value within the model.
   * This is called only once, when the model is first loaded.
   * 
   * Default value is supplied, and the returned value is used as the new
   * default value.
   * 
   * f( mixed $defaultValue ) : mixed
   * 
   * @param Closure $f function
   * @return \self this
   */
  public function withInit( Closure $f ) : self
  {
    $this->init = $f;
    return $this;
  }
  
  
  /**
   * Get the init function 
   * @return Closure|null function 
   */
  public function getInit() : ?Closure
  {
    return $this->init;
  }
  
  
  /**
   * Called prior to the save event and is returned as part of the save 
   * function chain.
   * 
   * f( IModel $model ) : \buffalokiwi\magicgraph\persist\IRunnable
   * 
   * This is only used with IPropertySvcConfig instances.
   * @param Closure $f function 
   * @return \self this 
   */
  public function withBeforeSave( Closure $f )  : self
  {
    $this->beforeSave = $f;
    return $this;
  }
  
  
  /**
   * Retrieve the before save function 
   * @return Closure|null function 
   */
  public function getBeforeSave() : ?Closure
  {
    return $this->beforeSave;
  }
  
  
  /**
   * Called after the save event and is returned as part of the save 
   * function chain.
   * 
   * f( IModel $model ) : \buffalokiwi\magicgraph\persist\IRunnable
   * 
   * This is only used with IPropertySvcConfig instances.
   * @param Closure $f function 
   * @return \self this 
   */
  public function withAfterSave( Closure $f ) : self
  {
    $this->afterSave = $f;
    return $this;
  }
  
  
  /**
   * Retrieve the after save function  
   * @return Closure|null function 
   */
  public function getAfterSave() : ?Closure
  {
    return $this->afterSave;
  }
  
  
  
  /**
   * Sets the model validation callback.
   * f( IModel $model ) throws ValidationException
   * @param Closure $f function 
   * @return this 
   */  
  public function withModelValidation( Closure $f ) : self
  {
    $this->mValidate = $f;
    return $this;
  }
  
  
  
  /**
   * Retrieve the model validation callback.
   * f( IModel $model ) throws ValidationException
   * @return \buffalokiwi\magicgraph\property\Closure|null
   */
  public function getModelValidation() : ?Closure
  {
    return $this->mValidate;
  }
  
  
  /**
   * Retrieve the NamedPropertyBehavior instance 
   * @return NamedPropertyBehavior behavior 
   */
  public function build() : NamedPropertyBehavior 
  {
    return new NamedPropertyBehavior( $this );
  }
}
