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

use buffalokiwi\magicgraph\ValidationException;
use Closure;
use InvalidArgumentException;
use ReflectionFunction;


/**
 * Base class for all properties.
 * After creating a property, the reset() method MUST be called.
 * I know it's icky, but it makes all this shit work better.
 * Create property instances like this:  $prop = (new Property())->reset();
 * reset must not be called from the constructor.
 * 
 * Hopefully I'll figure out a better way to do all of this without reset()...
 */
abstract class AbstractProperty implements IProperty
{
  /**
   * A list of names that have been checked via preg_match
   * @var array 
   */
  private static $NAME_CACHE = [];
  
  /**
   * An optional unique identifier for this property.
   * @var int
   */
  private $id = 0;
  
  /**
   * Property caption/label
   * @var string
   */
  private $caption;
  
  /**
   * Flags 
   * @var IPropertyFlags
   */
  private $flags;
    
  /**
   * Property Type 
   * @var IPropertyType
   */
  private $type;
  
  /**
   * Property name 
   * @var string
   */
  private $name;
  
  /**
   * Default value 
   * @var mixed
   */
  private $defaultValue;
  
  /**
   * Property behavior/callbacks 
   * @var IPropertyBehavior[]
   */
  private $behavior = [];
  
  /**
   * The stored property value 
   * @var mixed
   */
  private $value = null;
  
  /**
   * Random config data 
   * @var mixed
   */
  private $config;
  
  /**
   * Read only flag 
   * @var bool
   */
  private $readOnly = false;
  
  /**
   * An optional tag for the attribute 
   * @var string
   */
  private $tag = '';

  
  /**
   * An empty function 
   * @var Closure
   */
  private Closure $defaultValidateClosure;
  
  private bool $hasValidateBehaviorCallback = true; //..Must be true to start
  
  private ?Closure $validateBehaviorCallback = null;
  
  private bool $isEdited = false;
 
  /**
   * Validate some property value.
   * Child classes should implement some sort of validation based on the 
   * property type.
   * @param mixed $value The property value 
   * @throws ValidationException If the supplied value is not valid 
   */
  protected abstract function validatePropertyValue( $value ) : void;
  

    
  /**
   * Create a new Property instance.
   */
  public function __construct( IPropertyBuilder $builder )
  {
    //if ( $builder->getType() == null || empty( $builder->getType()->value()))
    //  throw new InvalidArgumentException( 'A valid IPropertyType instance must be supplied and have a valid non-empty value set' );
    //else if ( $builder->getFlags() == null )
    //  throw new \InvalidArgumentExcepton( 'A valid IPropertyFlags instance must be supplied' );
    
    $name = $builder->getName();
    if ( empty( $name ))
      throw new InvalidArgumentException( "name must not be empty" );    
    else if ( empty( $name ))
      throw new InvalidArgumentException( "name must be a non-empty alphanumeric string with optional underscores" );
    else if ( isset( self::$NAME_CACHE[$name] ) && !self::$NAME_CACHE[$name] )
      throw new InvalidArgumentException( "name must be a non-empty alphanumeric string with optional underscores" );
    else if ( !isset( self::$NAME_CACHE[$name] ))
    {
      self::$NAME_CACHE[$name] = preg_match( '/([a-zA-Z0-9_]+)/', $name );
      if ( !self::$NAME_CACHE[$name] )
        throw new InvalidArgumentException( "name must be a non-empty alphanumeric string with optional underscores" );
    }
    
    $this->defaultValidateClosure = function() { return true; };
    $this->type = $builder->getType();
    $this->flags = $builder->getFlags();
    
    
    foreach( $builder->getBehavior() as $b )
    {
      if ( $b instanceof IPropertyBehavior )
      {
        $this->behavior[] = $b;
      }
    }
    
    $this->name = $name;
    
    $this->defaultValue = $builder->getDefaultValue();
    
    $this->caption = $builder->getCaption();
    $this->id = $builder->getId();
    
    $this->tag = $builder->getTag();
    
    $this->config = $this->decodeJson( $builder->getConfig());
    
    $this->prefix = $builder->getPrefix();
    
    if ( !empty( $this->prefix ) && $this->type->value() != IPropertyType::TMODEL )
    {
      throw new \InvalidArgumentException( 'Property ' . $this->name . ' cannot implement a prefix unless the property type is an IModel' );    
    }
  }


  /**
   * Initialize and/or reset the property state to default.
   * First: checks for an init callback attached to IPropertyBehavior.  If it exists, then
   * the result of that callback is used as the default value, otherwise the default value
   * specified during object construction is used.
   * 
   * Second: calls setValue() with the derived default value.
   * 
   * This allows the default value to go through validation.
   * 
   * I really don't like this, but it makes object construction more clear.
   * @return IProperty this - Makes object creation and initialization a single statement.
   * @throws \InvalidArgumentException
   * @throws ValidationException 
   */
  public function reset() : IProperty
  {
    $this->value = $this->initValue();
        
    //..Run the init callback and store the result
    $val = $this->defaultValue;
    
    foreach( $this->behavior as $b )
    {
      $init = $b->getInitCallback();
    
      //..If the init callback returned a function, call that and use the result as the default property value.  
      //  Otherwise use the result of getDefaultValue() as the default property value.
      if ( $init != null )
        $val = $init( $val );
    }
    
    //..Set the property value
    $this->value = $this->setPropertyValue( $this->preparePropertyValue( $val ));    
    
    $this->isEdited = false;
    
    return $this;
  }
  
  
  /**
   * Make this clonable.
   * Clones the default value if it is an object
   * Clones flags
   * Clones the type
   * 
   * WARNING: Property values MUST be copied over to the cloned model
   * 
   */
  public function __clone()
  {
    if ( is_object( $this->defaultValue ))
      $this->defaultValue = clone $this->defaultValue;
    
    if ( is_object( $this->value ))
      $this->value = clone $this->value;
    
    $this->flags = clone $this->flags;
    $this->type = clone $this->type;
    
    foreach( $this->behavior as $k => $b )
    {
      $this->behavior[$k] = clone $b;
    }
    
    if ( is_object( $this->config ))
      $this->config = clone $this->config;
    else if ( is_array( $this->config ))
      $this->config = $this->cloneArray( $this->config );

    //..Does this work? 
    //..Test says this is ok.        
    $this->reset();
  }
  
  private function cloneArray( array &$data ) : array
  {
    $out = [];
    foreach( $data as $k => $v )
    {
      if ( is_object( $v ))
        $out[$k] = clone $v;
      else if ( is_array( $v ))
        $out[$k] = $this->cloneData( $v );
      else
        $out[$k] = $v;
    }
    
    return $out;
  }  
  
  
  
  
  
  /**
   * Retrieve the tag value for this attribute 
   * @return string tag 
   */
  public function getTag() : string
  {
    return $this->tag;
  }
  
  
  /**
   * Retrieve the optionally set unique identifier for this property.
   * This may be zero if unassigned.
   * @return int optional id 
   */
  public function getId() : int
  {
    return $this->id;
  }
  
  
  /**
   * Retrieve the default value for some property 
   * @return mixed Default value 
   */
  public function getDefaultValue()
  {
    return $this->defaultValue;
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
   * Retrieve the property type
   * @return IPropertyType type
   */
  public function getType() : IPropertyType
  {
    return $this->type;
  }
  
  
  /**
   * Retrieve the set of flags for this property
   * @return IPropertyFlags flags
   */
  public function getFlags() : IPropertyFlags
  {
    return $this->flags;
  }
  
  
  /**
   * Retrieve the object containing callbacks that can modify some behavior
   * of the property.
   * @return IPropertyBehavior[] List of IPropertyBehavior instances 
   */
  public function getPropertyBehavior() : array
  {
    return $this->behavior;
  }
  
  
  /**
   * Retrieve the attribute caption/label
   * @return string
   */
  public function getCaption() : string
  {
    return ( empty( $this->caption )) ? $this->name : $this->caption;
  }
  
  
  /**
   * Retrieve random config data
   * @return mixed
   */
  public function getConfig()
  {
    return $this->config;
  }
  
  
  /**
   * Retrieve the prefix for this property.
   * This means this property maps to a child model 
   * @return string
   */
  public function getPrefix() : string
  {
    return $this->prefix;
  }
  
  
  /**
   * Sets the property set to read only.
   * This is a method because the model still needs to be written to when 
   * creating instances populated from persistent storage.  The idea is for the
   * mapping object factory to call this method after filling the model, but 
   * before returning it.
   * 
   * I don't think this feature was ever finished.
   */
  public function setReadOnly() : void
  {
    $this->readOnly = true;
  }
  

  /**
   * Test to see if some value is valid 
   * @param type $value
   * @throws ValidationException 
   * @final 
   */
  public final function validate( $value ) : void
  {
    if ( $this->hasValidateBehaviorCallback )
    {
      $cb = $this->validateBehaviorCallback ?? $this->getValidateBehaviorCallback();

      if ( !$cb( $this, $value ))
      {
        if ( is_array( $value ))
          $value = implode( ',', $value );

        throw new ValidationException( '"' . $value . '" of type "' . static::class .'" is not a valid value for the "' . $this->getName() 
           . '" property.  Check any behavior callbacks, and ensure that the property is set to the correct type.  IPropertyBehavior::getValidateCallback() failed.' );    
      }
    }
    
    if ( !$this->isUseNull() && $value === null )
    {
      
      throw new ValidationException( '"' . $this->getName() . '" property of type "' . static::class .'" must not be null.  If you want null, set the IPropertyFlags::USE_NULL flag or set the default value to some non-null value' );
    }
    
    
    
    $this->validatePropertyValue( $value );
  }
  
  
  /**
   * Tests that the value is empty.
   * If no behavior is found (IS_EMPTY) then 
   * this simply does empty( value ) && value != '0000-00-00 00:00:00'.
   * If behavior is used, the above logic is ignored and the is_empty callback
   * determines empty state.
   * 
   * Override AbstractProperty::isPropertyEmpty() to customize isEmpty without the use of behaviors.
   * @return bool
   * @final
   */
  public final function isEmpty() : bool
  {
    $hasBehavior = false;
    foreach( $this->behavior as $b )
    {
      $f = $b->getIsEmptyCallback();
      if ( $f instanceof Closure )
      {
        $hasBehavior = true;
        if ( !$f( $this, $this->value, $this->defaultValue ))
        {
          return false;
        }
      }
    }
    
    if ( $hasBehavior )
      return true;  
    else
    {
      return $this->isPropertyEmpty( $this->value );
    }
  }
  
  
  /**
   * When the property behavior empty callback is not used, this 
   * method will determine if the property is empty.
   * 
   * This returns empty(value) || $value == 0000-00-00 00:00:00 || $value === $this->defaultValue;
   * 
   * Override as necessary.
   * 
   * @param type $value
   * @return bool
   */
  protected function isPropertyEmpty( $value ) : bool
  {
    return empty( $value ) 
      || ( is_string( $value ) && $value == '0000-00-00 00:00:00' ) 
      || $value === $this->defaultValue;
  }
  
  
  /**
   * Sets the property value 
   * If the IProperty instance contains a valid Setter callback, 
   * it is called and the result of that callback is returned from this method.
   * If that callback is null, the passed value is returned.
   * @param mixed $value Value to set
   * @return void
   * @throws ValidationException 
   * @todo Figure out why readonly is commented out 
   * @final 
   */
  public final function setValue( $value ) : void
  {
//    if ( $this->readOnly )
//      throw new ValidationException( $this->name . ' is read only' );
    if ( $this->isWriteEmpty() && !$this->isEmpty() && $this->value !== $value )
    {
      throw new ValidationException( $this->name . ' has already been assigned a value, and is now read only' );
    }
    //..Prepare for validate
    $value = $this->preparePropertyValue( $value );

    
    //..Behavior modifications 
    foreach( $this->behavior as $b )
    {
      $cb = $b->getSetterCallback();
      if ( $cb instanceof Closure )
        $value = $cb( $this, $value );      
    }
    
    
    
    //..Validate 
    $this->validate( $value );    
    
    /**
     * On Change event code
     * There is no point in calling getValue for every property regardless
     * of change events.  It's possible that getValue could be 
     * expensive depending on the implementation.
     * We cache any events in a list and if that list is not empty, then
     * we get the current value and call each event.
     */
    $onChange = [];
    foreach( $this->behavior as $b )
    {
      /* @var $b IPropertyBehavior */
      $f = $b->getOnChangeCallback();
      if ( $f instanceof \Closure )
      {
        $onChange[] = $f;        
      }
    }    
    
    if ( !empty( $onChange ))
    {
      $curValue = $this->getValue();
      
      //..All object must be castable to a string, so we can do this.
      
      if ( is_object( $curValue ))
        $curValue = (string)$curValue;
      
      $this->value = $this->setPropertyValue( $value );
      
      foreach( $onChange as $f )
      {
        $f( $this, $curValue, $this->value );      
      }      
    }
    else
    {
 
      $this->value = $this->setPropertyValue( $value );
    }
  
    
    if ( !$this->getFlags()->hasVal( IPropertyFlags::NO_INSERT ) && !$this->getFlags()->hasVal( IPropertyFlags::NO_UPDATE ))
    {
      $this->isEdited = true;
    }
  }
  
  
  /**
   * Checks the internal edited flag.
   * This is set to true when setValue() is called 
   * @return bool is edited 
   */
  public function isEdited() : bool
  {
    return $this->isEdited;
  }
  
  
  /**
   * Sets the edited flag to true
   * @return void
   */
  protected final function setEdited() : void
  {
    $this->isEdited = true;
  }
  
  
  /**
   * Sets the internal edited flag to false 
   * @return void
   */
  public function clearEditFlag() : void
  {
    $this->isEdited = false;
  }
  
  
  /**
   * Retrieve the stored property value 
   * @return mixed value 
   * @final 
   */
  public final function getValue( array $context = [] )
  {
    $value = $this->getPropertyValue( $this->value );
    
    foreach( $this->behavior as $b )
    {
      $cb = $b->getGetterCallback();
      if ( $cb != null )
        $value = $cb( $this, $value, $context );
    }
    
    if ( empty( $value ) && $this->flags->hasVal( IPropertyFlags::USE_NULL ))
      return null;
    
    return $value;
  }
  
  
  /**
   * All properties must be able to be cast to a string.
   * If value is an array, it will be serialized by default.
   * Classes overriding this method may change this behavior.
   * 
   * Values other than array are simply cast to a string.  Here be dragons.
   * 
   * @return string property value 
   */
  public function __toString()
  {
    if ( is_array( $this->value ))
      return serialize( $this->value );
    else if ( $this->value === null )
      return null;
    else
      return (string)$this->value;
  }
  
  
  /**
   * Called after the behavior callback setter, and BEFORE validate.
   * Override this to prepare data for validation.
   * 
   * DO NOT USE THIS TO COMMIT DATA.
   * 
   * @param mixed $value Value being set.
   * @return mixed value to validate and set
   */
  protected function preparePropertyValue( $value )
  {
    return $value;
  }
  
  
  /**
   * Called when setting a property value.
   * This is called AFTER validate.
   * Override this in child classes to modify the value prior to committing it.
   * This is the default implementation which simply returns the supplied value.
   * @param mixed $value Value being set
   * @return mixed Value to set 
   */
  protected function setPropertyValue( $value )
  {
    return $value;
  }
  
  
  /**
   * Called when getting a property value.
   * Override this in child classes to modify the value prior to returning it from the getValue() method.
   * This is the default implementation which simply returns the supplied value.
   * @param mixed $value Value being returned
   * @return mixed Value to return 
   */
  protected function getPropertyValue( $value )
  {
    return $value;
  }
  
  
  /**
   * Initialize the value property with some value.
   * This will be immediately overwritten by the initial call to reset(), but 
   * is useful for when value is some object type that must not be null. 
   * 
   * Returns null by default.
   * 
   * @return mixed value 
   */
  protected function initValue()
  {
    return null;
  }
  
  
  protected final function isUseNull() : bool
  {
    return $this->flags->hasVal( IPropertyFlags::USE_NULL );
  }
  
  
  protected final function isWriteEmpty() : bool
  {
    return $this->flags->hasVal( IPropertyFlags::WRITE_EMPTY );
  }
  
  
  
  /**
   * Retrieve the validate callback from the IPropertyBehavior instance.
   * If the callback contained with IPropertyBehavior is null, a function
   * that always returns true (valid property value) is returned.
   * @return type
   */
  private function getValidateBehaviorCallback()
  {
    $funcArr = [];
  
    foreach( $this->behavior as $b )
    {
      $c = $b->getValidateCallback();
      if ( $c != null ) 
      { 
        //..Return a function that is always valid 
        $funcArr[] = $c;        
      }
    }
    
    if ( empty( $funcArr ))
    {
      $this->hasValidateBehaviorCallback = false;
      return $this->defaultValidateClosure;
    }
    else
      $this->hasValidateBehaviorCallback = true;
    
    
    return function( IProperty $prop, $value ) use ($funcArr) { 
      foreach( $funcArr as $f )
      {
        if ( !$f( $prop, $value ))
        {
          /* @var $f \Closure */
          $r = new ReflectionFunction( $f );
          $inObj = get_class( $r->getClosureThis()) . ' in file ' . $r->getFileName() . ' on line ' . $r->getStartLine();
          trigger_error( 'Behavior validation failure in closure: ' . $inObj, E_USER_WARNING );          
          return false;
        }
      }
      return true;       
    };
  }
  
  
  private function decodeJson( $value )
  {
    if ( is_string( $value ))
    {
      $decoded = json_decode( $value );
      if ( json_last_error() == JSON_ERROR_NONE && is_array( $decoded ))
        return $decoded;
    }    
    
    return $value;
  }
  
}
