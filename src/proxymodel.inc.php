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

namespace buffalokiwi\magicgraph;

use buffalokiwi\buffalotools\types\IBigSet;
use buffalokiwi\buffalotools\types\ISet;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertySet;
use Exception;
use InvalidArgumentException;
use stdClass;
use Traversable;


/**
 * Delegates all method calls to the supplied model instance.
 */
class ProxyModel implements IModel
{
  /**
   * Model instance 
   * @var IModel
   */
  private $model;
  
  //..I have no idea if method_exists caches, so I did this.
  private $hasGet = false;
  private $hasSet = false;
  private $hasIsset = false;
  
  /**
   * Model to use 
   * @param IModel $model Model to pass calls to 
   */
  public function __construct( IModel $model )
  {
    $this->model = $model;
  }
  
  
  /**
   * Retrieve an external iterator
   * <p>Returns an external iterator.</p>
   * @return Traversable <p>An instance of an object implementing Iterator or Traversable</p>
   * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
   * @since PHP 5, PHP 7
   */
  public function getIterator(): Traversable
  {
    return $this->model->getIterator();
  }  
  
  
  /**
   * Create a copy/clone of the model and all properties.
   * @param bool $copyIsSaveable If this is false, then the primary key flags
   * are removed from the copied model.  This will cause the repository save
   * method 
   * @return IModel Copied model 
   */
  public function createCopy( bool $copyIsSaveable = true ) : IModel
  {
    return $this->model->createCopy( $copyIsSaveable );
  }
  
  /**
   * Clears the internal edited flags for each property
   * @return void
   */
  public function clearEditFlags() : void
  {
    $this->model->clearEditFlags();
  }
  
  
  /**
   * A way to determine if this model is an instance of some interface.
   * This is used due to decorators.
   * @param string $interface Interface name
   * @return bool if this implements it 
   * @final 
   */
  public final function instanceOf( string $interface ) : bool
  {
    return $this->model->instanceOf( $interface );
  }
  
  
  /**
   * Used for any method not part of IModel.
   * @param string $name
   * @param array $arguments
   * @return type
   * @throws Exception
   */
  public function __call($name, $arguments) 
  {
    if ( method_exists( $this, $name ))
      return $this->name( ...$arguments );
    else if ( is_callable( [$this->model, $name] ))
      return $this->model->$name( ...$arguments );
    
    throw new Exception( 'Method ' . $name . ' does not exist' );
  }
  
  

  /**
   * Determine if $name matches a property name defined in the IPropertySet
   * instance supplied to the constructor.
   * 
   * Calls IPropertySet::isMember()
   * 
   * @see ISet::isMember()
   * @param string $name property name
   * @return boolean is set
   */
  public function __isset( $name )
  {
    if ( $this->hasIsset || method_exists( $this->model, '__isset' ))
    {
      $this->hasIsset = true;
      return $this->model->__isset( $name );
    }
  }


  /**
   * Generic getter.   
   * Alias of getValue()
   * @param string $p Property name
   * @return mixed Property value
   * @see DefaultModel::getValue()
   */
  public function __get( $p )
  {
    if ( $this->hasGet || method_exists( $this->model, '__get' ))
    {
      $this->hasGet = true;      
      return $this->model->__get( $p );
    }
  }


  /**
   * Sets some property value.
   * Alias of setValue()
   * @param string $p Property name
   * @param mixed $v Property value 
   * @see DefaultModel::setValue()
   */
  public function __set( $p, $v )
  {
    if ( $this->hasSet || method_exists( $this->model, '__set' ))
    {
      $this->hasSet = true;
      $this->model->__set( $p, $v );
    }
  }
  
  
  /**
   * Retrieve a property config instance attached to this model.
   * @param string $intf Interface of the config instance 
   * @return IPropertyConfig The config instance
   * @throws \Exception if The requested interface was not used to build this
   * model.
   */
  public function getPropertyConfig( string $intf ) : IPropertyConfig
  {
    return $this->model->getPropertyConfig( $intf );
  }
  
  
  /**
   * Retrieve the property set used for this model.
   * This MUST return a clone of the backing property set and never the 
   * one used internally to set values.
   * @reutrn IPropertySet properties
   */
  public function getPropertySet() : IPropertySet
  {
    return $this->model->getPropertySet();
  }
  
  
  /**
   * Detect if any properties have been edited in this model 
   * @param string $prop Property name.  If $prop is not empty then this would test that the supplied property name is not empty. 
   * Otherwise, this tests if any property was edited.
   * @return bool has edits
   */
  public function hasEdits( string $prop = '' ) : bool
  {
    return $this->model->hasEdits( $prop );
  }  
  
  
  /**
   * Retrieve property names as an IBigSet instance.
   * This will return a set containing all of the property names, and have 
   * zero members active.  This is available due to how expensive cloning 
   * the backing IPropertySet instance can be.  Use this for simple operations
   * such as determining if a property name is valid.
   * @return IBigSet property names 
   */
  public function getPropertyNameSet() : IBigSet
  {
    return $this->model->getPropertyNameSet();
  }
  
  
  /**
   * Retrieve a set with the property name bits toggled on for properties
   * with the supplied flags.
   * @param string $flags Flags 
   * @return IBigSet names 
   */
  public function getPropertyNameSetByFlags( string ...$flags ) : IBigSet
  {
    return $this->model->getPropertyNameSetByFlags( ...$flags );
  }
  
  
  /**
   * Convert the model to a json representation.
   * @return string JSON object 
   */
  public function toObject( ?IBigSet $properties = null, bool $includeArrays = false, bool $includeModels = false, bool $includeExtra = false ) : stdClass
  {
    return $this->model->toObject( $properties, $includeArrays, $includeModels, $includeExtra );
  }
  
  
  /**
   * Convert this model to an array.
   * @param IPropertySet $properties Properties to include 
   */
  public function toArray( ?IBigSet $properties = null, bool $includeArrays = false, bool $includeModels = false, bool $includeExtra = false ) : array
  {
    return $this->model->toArray( $properties, $includeArrays, $includeModels, $includeExtra );
  }
  
  
  /**
   * Test if this model is equal to some other model
   * @param IModel $that model to compare
   * @return bool is equals
   */
  public function equals( IModel $that ) : bool
  {
    return $this->model->equals( $that );
  }
  
  
  /**
   * Retrieve the value of some property
   * @param string $property Property 
   * @return mixed value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function getValue( string $property, array $context = []  )
  {
    return $this->model->getValue( $property, $context );
  }
  
  
  /**
   * Sets the value of some property
   * @param string $property Property to set
   * @param mixed $value property value
   * @throws InvalidArgumentException if the property is invalid 
   */
  public function setValue( string $property, $value ) : void
  {
    $this->model->setValue( $property, $value );
  }
  
  
  /**
   * Retrieve a list of modified properties 
   * @return ISet modified properties 
   */
  public function getModifiedProperties() : IBigSet
  {
    return $this->model->getModifiedProperties();
  }
  
  /**
   * Gets A propertyset containing properties for insert
   * @return IBigSet insert properties
   */
  public function getInsertProperties() : IBigSet
  {
    return $this->model->getInsertProperties();
  }
  
  
  /**
   * Test to see if this model is valid prior to save()
   * @throws ValidationException
   */
  public function validate() : void
  {
    $this->model->validate();
  }
  
  
  /**
   * If you want to validate each property and return a list of errors indexed by property name, this 
   * is the method to call.
   * 
   * Note: This simply calls validate() in a loop, catches exceptions and tosses some errors in a list.  
   * 
   * @return array [property name => message]
   */
  public function validateAll( bool $debugErrors = false ) : array
  {
    return $this->model->validateAll();
  }

  
  
  /**
   * Retrieve a unique hash for this object 
   * @return string hash
   */
  public function hash() : string
  {
    return $this->model->hash();
  }
  
  
  /**
   * Retrieves the model
   * @return IModel model 
   */
  protected function getModel()
  {
    return $this->model;
  }
  
  
  public function fromArray( array $data ) : void
  {
    $this->model->fromArray( $data );
  }
  
  
  /**
   * Test that the model has all primary key values 
   * @return bool has values 
   */
  public function hasPrimaryKeyValues() : bool
  {
    return $this->model->hasPrimaryKeyValues();
  }
}
