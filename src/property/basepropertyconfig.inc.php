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

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\property\htmlproperty\IElement;
use Closure;
use Exception;


abstract class BasePropertyConfig extends DefaultPropertyConfig implements IPropertyConfig
{  
   /**
   * Primary key integer property 
   */
  const FINTEGER_PRIMARY = [
    self::TYPE => IPropertyType::TINTEGER,
    self::FLAGS => [IPropertyFlags::PRIMARY],
    self::VALUE => 0
  ];  

  /**
   * Unsigned integer property
   */
  const FINTEGER = [
    self::TYPE => IPropertyType::TINTEGER,
    self::FLAGS => [],
    self::VALUE => 0,
    self::MIN => 0 //..Pretty much every integer in the entire system is unsigned.  
  ];  
  
    
  /**
   * Signed integer property
   */
  const FINTEGER_SIGNED = [
    self::TYPE => IPropertyType::TINTEGER,
    self::FLAGS => [],
    self::VALUE => 0      
  ];

  
  /**
   * Required unsigned integer 
   */
  const FINTEGER_REQUIRED = [
    self::TYPE => IPropertyType::TINTEGER,
    self::FLAGS => [IPropertyFlags::REQUIRED],
    self::VALUE => 0
  ];  
  
  
  /**
   * Required Unsigned integer property.
   * Value can only be assigned if equal to default value 
   */
  const FINTEGER_REQ_WE = [
    self::TYPE => IPropertyType::TINTEGER,
    self::FLAGS => [IPropertyFlags::REQUIRED, IPropertyFlags::WRITE_EMPTY],
    self::VALUE => 0    
  ];
  
  /**
   * Unsigned integer property.
   * Value can only be assigned if equal to default value 
   */
  const FINTEGER_WE = [
    self::TYPE => IPropertyType::TINTEGER,
    self::FLAGS => [IPropertyFlags::WRITE_EMPTY],
    self::VALUE => 0    
  ];

  
  /**
   * String property
   */
  const FSTRING = [
    self::TYPE => IPropertyType::TSTRING,
    self::FLAGS => [],
    self::VALUE => ''
  ];
  
  
  /**
   * Required string property
   */
  const FSTRING_REQUIRED = [
    self::TYPE => IPropertyType::TSTRING,
    self::FLAGS => [IPropertyFlags::REQUIRED],
    self::VALUE => ''
  ];  

  
  /**
   * Required string property 
   * Value can only be assigned if equal to default value 
   */
  const FSTRING_REQ_WE = [
    self::TYPE => IPropertyType::TSTRING,
    self::FLAGS => [IPropertyFlags::REQUIRED, IPropertyFlags::WRITE_EMPTY],
    self::VALUE => ''
  ];  
  
  
  /**
   * Date time property 
   */
  const FDATE = [
    self::TYPE => IPropertyType::TDATE,
    self::FLAGS => [IPropertyFlags::USE_NULL],
    self::VALUE => null
  ];
  
  
  /**
   * Date time property 
   * Value can only be assigned if equal to default value 
   */
  const FDATE_ONCE = [
    self::TYPE => IPropertyType::TDATE,
    self::FLAGS => [IPropertyFlags::WRITE_EMPTY, IPropertyFlags::USE_NULL],
    self::VALUE => null
  ];
  
  
  /**
   * Enum property
   */
  const FENUM = [
    self::TYPE => IPropertyType::TENUM,
    self::FLAGS => [IPropertyFlags::REQUIRED]
  ];
    
  
  /**
   * Money property
   */
  const FMONEY = [
    self::TYPE => IPropertyType::TMONEY,
    self::FLAGS => [],
    self::VALUE => '0'
  ];
  
  
  /**
   * Required money property
   */
  const FMONEY_REQUIRED = [
    self::TYPE => IPropertyType::TMONEY,
    self::FLAGS => [IPropertyFlags::REQUIRED],
    self::VALUE => '0'
  ];

  
  /**
   * Array property
   */
  const FARRAY = [
    self::TYPE => IPropertyType::TARRAY,
    self::FLAGS => [IPropertyFlags::NO_INSERT, IPropertyFlags::NO_UPDATE],
    self::VALUE => []
  ];
  
  
  /**
   * Required array property
   */
  const FARRAY_REQUIRED = [
    self::TYPE => IPropertyType::TARRAY,
    self::FLAGS => [IPropertyFlags::REQUIRED,IPropertyFlags::NO_INSERT, IPropertyFlags::NO_UPDATE],
    self::VALUE => []
  ];
  
  
  /**
   * Required decimal property
   */
  const FDECIMAL_REQUIRED = [
    self::TYPE => IPropertyType::TFLOAT,
    self::FLAGS => [IPropertyFlags::REQUIRED],
    self::VALUE => 0
  ];
  
  
  /**
   * Decimal property
   */
  const FDECIMAL = [
    self::TYPE => IPropertyType::TFLOAT,
    self::FLAGS => [],
    self::VALUE => 0
  ];
  
  
  /**
   * Boolean property
   * default false
   */
  const FBOOLEAN = [
    self::TYPE => IPropertyType::TBOOLEAN,
    self::FLAGS => [],
    self::VALUE => false
  ];
  
  
  /**
   * Boolean property 
   * default true 
   */
  const FBOOLEAN_TRUE = [
    self::TYPE => IPropertyType::TBOOLEAN,
    self::FLAGS => [],
    self::VALUE => true
  ];  
  
  
  /**
   * List of available callbacks 
   */
  const CALLBACKS = [
    self::INIT,
    self::VALIDATE,
    self::SETTER,
    self::GETTER,
    self::MSETTER,
    self::MGETTER,
    self::CHANGE,
    self::HTMLINPUT,
    self::TOARRAY,
    self::IS_EMPTY
  ];
  

 
  
  
  
  /**
   * Cache for config array 
   * @var array 
   */
  private $cache = [];
  
  
  /**
   * A cache of property names 
   * @var string[] 
   */
  private $nameCache = [];
  
  
  /**
   * Behavior map 
   * @var array [property_name => INamedPropertyBehavior]
   */
  private $behavior = [];
  
  /**
   * Model validation function from INamedPropertyBehavior 
   * @var Closure
   */
  private $mValidate = null;
  
  /**
   * Ghetto, but this is true if the end of the constructor has been reached.
   * @var bool
   */
  private $isConstructed = false;
  
  private $mValidateData = [];
  
  /**
   * Before save events 
   * @var Closure[]
   */
  private $beforeSave = [];
  
  
  /**
   * After save events 
   * @var Closure[] 
   */
  private $afterSave = [];
  
  /**
   * Generic INamedPropertyBehavior instances.
   * These have a property name equal to their class name 
   * @var array
   */
  private array $genericBehavior = [];
  
  
  /**
   * Retrieve the configuration array for generating model properties.
   */
  protected abstract function createConfig() : array;
  
  
  
  /**
   * Constructor 
   * @param INamedPropertyBehavior ...$behavior Additional property behavior 
   * NOTE: the beforeSave and afterSave property behavior methods are unlikely
   * to work with standard IPropertyService implementations.  Functioning
   * would be based on the controlling service.  All IModelPropertyProvider
   * implementations can take advantage of beforeSave() and afterSave().
   * 
   * UPDATE: The before/after save functionality is baked into the SaveableMappingObjectFactory.  Assuming persistence 
   * is based on that implementation, before/after save are guaranteed to be called.
   * 
   */
  public function __construct( INamedPropertyBehavior ...$behavior )
  {
    $bList = [];
    
    if ( !empty( $behavior ))
    {
      foreach( $behavior as $b )
      {
        if ( $b->getPropertyName() == get_class( $b ))
          $this->genericBehavior[] = $b;
        else
          $bList[] = $b;
      }
    }
    
    $mValidate = [];
    foreach( $bList as $b )
    {
      /* @var $b INamedPropertyBehavior */
      if ( !isset( $this->behavior[$b->getPropertyName()] ))
        $this->behavior[$b->getPropertyName()] = [];
      $this->behavior[$b->getPropertyName()][] = $b;
      $f = $b->getModelValidationCallback();
      if ( $f != null )
        $mValidate[] = $f;        
      
      $bs = $b->getBeforeSaveCallback();
      $as = $b->getAfterSaveCallback();
      if ( $bs != null )
        $this->beforeSave[] = $bs;
      if ( $as != null )
        $this->afterSave[] = $as;
      
    }
    
    $this->mValidateData = $mValidate;
    $this->isConstructed = true;
  }

  
  /**
   * Called via SaveableMappingObjectFactory, and happens as part of the 
   * beforeSave event.
   * @param \buffalokiwi\magicgraph\property\IModel $model Model being saved 
   * @return void
   * @final 
   */
  public final function beforeSave( IModel $model ) : void
  {
    foreach( $this->beforeSave as $f )
    {
      $f( $model );
    }
  }
  
  
  /**
   * Called via SaveableMappingObjectFactory, and happens as part of the 
   * afterSave event.
   * @param \buffalokiwi\magicgraph\property\IModel $model Model being saved 
   * @return void
   * @final
   */
  public final function afterSave( IModel $model ) : void
  {
    foreach( $this->afterSave as $f )
    {
      $f( $model );
    }    
  }
  
  
  
  /**
   * Retrieve a validation callback that can be used for IModel instances.
   * @return Closure f( IModel $model ) throws ValidationException
   * @final 
   */
  public final function getValidation() : ?Closure
  {
    //..Ick.
    if ( !$this->isConstructed )
      throw new Exception( static::class . ' has not yet been initialized.  Call parent::__construct() prior to calling getValidation()' );
    else if ( $this->mValidate == null ) //..Whatever, just build this on demand.
    {
      $this->mValidate = $this->createModelValidationClosure( $this->mValidateData );
    }
    
    return $this->mValidate;
  }  
  
  
  /**
   * Get the property config for the main property set 
   * @return IPropertyConfig config 
   */
  public function getPropertyConfig() : IPropertyConfig
  {
    return $this;
  }  
  
  
  /**
   * Adds an additional validation closure to the stack.
   * @param Closure $f New validation function.  f( IModel $model ) : void throws ValidationException
   * @return void
   * @final 
   */
  protected final function addValidation( Closure $f ) : void
  {
    if ( !$this->isConstructed )
      throw new Exception( static::class . ' has not yet been initialized.  Call parent::__construct() prior to calling addValidation()' );
    
    $this->mValidateData[] = $f;
    $this->mValidate = null;
  }


  /**
   * Adds an additional beforeSave closure to the stack.
   * @param Closure $f New beforeSAve function.  f( IModel $model ) : void 
   * @return void
   * @final 
   */
  protected final function addBeforeSave( Closure $f ) : void
  {
    if ( !$this->isConstructed )
      throw new Exception( static::class . ' has not yet been initialized.  Call parent::__construct() prior to calling addBeforeSave()' );
    
    $this->beforeSave[] = $f;
  }


  /**
   * Adds an additional afterSave closure to the stack.
   * @param Closure $f New afterSave function.  f( IModel $model ) : void 
   * @return void
   * @final 
   */
  protected final function addAfterSave( Closure $f ) : void
  {
    if ( !$this->isConstructed )
      throw new Exception( static::class . ' has not yet been initialized.  Call parent::__construct() prior to calling addAfterSave()' );
    
    $this->afterSave[] = $f;
  }

  
  
  /**
   * Retrieve a list of property names defined via this config 
   * @return array names 
   */
  public function getPropertyNames() : array
  {
    if ( empty( $this->nameCache ))
      $this->nameCache = array_keys( $this->getConfig());
    
    return $this->nameCache;
  }  
  
  
  /**
   * Retrieve the configuration array used to create model instances.
   * @return array config data 
   */
  public function getConfig() : array
  {
    if ( empty( $this->cache ))
      $this->cache = $this->processBehavior( $this->createConfig());
    return $this->cache;
  }
  
  
  protected function getEnumCfg( string $class, $value = '' ) : array
  {
    $a = self::FENUM;
    $a[self::CLAZZ] = $class;
    $a[self::VALUE] = $value;
    return $a;
  }
  
  
  protected function removeFlagFromConfigEntry( array &$entry, string $flag ) : void
  {
    if ( isset( $entry[self::FLAGS] ))
    {
      if ( !is_array( $entry[self::FLAGS] ) && $entry[self::FLAGS] == $flag )
        unset( $entry[self::FLAGS] );
      else if ( is_array( $entry[self::FLAGS] ))
      {
        foreach( $entry[self::FLAGS] as $k => $v )
        {
          if ( $v == $flag )
          {
            unset( $entry[self::FLAGS][$k] );
            break;
          }
        }
      }          
    }
  }
  
  
  protected function addFlagToConfigEntry( array &$entry, string $flag )
  {
    if ( !isset( $entry[self::FLAGS] ))
      $entry[self::FLAGS] = [];
    
    if ( !in_array( $flag, $entry[self::FLAGS] ))
      $entry[self::FLAGS][] = $flag;
  }
  
  
  /**
   * Creates the model validation closure 
   * @param array $mValidate Closure[] behavior closures 
   * @return Closure|null function 
   */
  private function createModelValidationClosure( array $mValidate ) : ?Closure 
  {
    if ( empty( $mValidate ))
      return null;
    
    return function( IModel $model ) use ($mValidate) : void {
      foreach( $mValidate as $f )
      {
        $f( $model );
      }
    };
  }
  
  
  /**
   * Retrieve any additional behavior for some property.
   * If none is assigned, then an empty array is returned.
   * @param string $property Property name 
   * @return array INamedPropertyBehavior[] additional behavior 
   */
  private function getBehavior( string $property ) : array
  {
    return ( isset( $this->behavior[$property] )) ? $this->behavior[$property] : [];
  }
  
  private function addBehaviorToArray( &$out, string $key, ?\Closure $f )
  {
    if ( $f != null )
    {
      if ( !isset( $out[$key] ))
        $out[$key] = [];
      
      $out[$key] = $f;
    }
  }
  
  
  /**
   * This is currently UNUSED. HA!
   * @param INamedPropertyBehavior $behavior
   * @return array
   */
  private function getUsedBehavior( INamedPropertyBehavior ...$behavior )
  {
    $out = [];
    
    foreach( $behavior as $b )
    {
      /* @var $b INamedPropertyBehavior */
      $this->addBehaviorToArray( $out, self::INIT, $b->getInitCallback());
      $this->addBehaviorToArray( $out, self::VALIDATE, $b->getValidateCallback());
      $this->addBehaviorToArray( $out, self::SETTER, $b->getSetterCallback());
      $this->addBehaviorToArray( $out, self::GETTER, $b->getGetterCallback());
      $this->addBehaviorToArray( $out, self::MSETTER, $b->getModelSetterCallback());
      $this->addBehaviorToArray( $out, self::MGETTER, $b->getModelGetterCallback());
      $this->addBehaviorToArray( $out, self::CHANGE, $b->getOnChangeCallback());
      $this->addBehaviorToArray( $out, self::HTMLINPUT, $b->getHTMLInputCallback());
      $this->addBehaviorToArray( $out, self::TOARRAY, $b->getToArrayCallback());
      $this->addBehaviorToArray( $out, self::IS_EMPTY, $b->getIsEmptyCallback());
    }
    
    return $out;
  }
  
  
  /**
   * Process any associated behavior for some config array 
   * @param array $config config array  
   * @return array modified array 
   */
  private function processBehavior( array $config ) : array
  {
    foreach( $config as $propName => &$entry )
    {
      //..Get the behavior 
      $behavior = $this->getBehavior( $propName );
      foreach( $this->genericBehavior as $b )
      {
        $behavior[] = $b;
      }
      
      
      //..Skip entries that have no additional behavior 
      if ( empty( $behavior ))
        continue;
      
      //..Modify any behavior callbacks 
      foreach( $entry as $k => $v )
      {
        $entry[$k] = $this->processEntry( $k, $v, ...$behavior );
      }
      
      
      foreach( self::CALLBACKS as $cb )
      {
        if ( !isset( $entry[$cb] ))
        {
          $res = $this->processEntry( $cb, null, ...$behavior );
          if ( $res != null )
            $entry[$cb] = $res;
        }
      }
    }
    
    return $config;
  }
  
  
  /**
   * Create a list of functions for some behavior function 
   * @param string $method Behavior function to call.
   * @param INamedPropertyBehavior $behavior Behavior objects
   * @return array \Closure[] list of additional behavior functions 
   */
  private function getFunctionList( string $method, INamedPropertyBehavior ...$behavior ) : array
  {
    $functions = [];
    foreach( $behavior as $b )
    {
      /* @var $b INamedPropertyBehavior */
      $f = $b->$method();
      if ( $f != null )
        $functions[] = $f;
    }
    
    return $functions;    
  }
  
  
  /**
   * Create an init callback property value for multiple functions 
   * @param ?Closure $value Closure|null from property config array
   * @param Closure ...$functions A list of additional init functions 
   * @return Closure|null Combined function
   */
  private function getInitCallback( ?Closure $value, Closure ...$functions ) : ?Closure 
  {
    if ( empty( $functions ))
      return $value;
    
    return function( $defaultValue ) use ($functions,$value) {
      if ( $value != null )
        $defaultValue = $value( $defaultValue );

      foreach( $functions as $f )
      {
        $defaultValue = $f( $defaultValue );
      }

      return $defaultValue;
    };
  }
  
  
    
  /**
   * Create the on change callback 
   * @param ?Closure $value Closure|null from property config array
   * @param Closure ...$functions A list of additional functions 
   * @return Closure|null Combined function
   */
  private function getOnChangeCallback( ?Closure $value, Closure ...$functions ) : ?Closure 
  {
    if ( empty( $functions ))
      return $value;
    
    return function( IProperty $prop, $oldValue, $newValue ) use($value,$functions) : void {
      if ( $value != null )
        $value( $prop, $oldValue, $newValue );
      
      foreach( $functions as $f )
      {
        $f( $prop, $oldValue, $newValue );
      }
    };      
  }
  
  
  
  /**
   * @param ?Closure $value Closure|null from property config array
   * @param Closure ...$functions A list of additional functions 
   * @return Closure|null Combined function
   */
  private function getHTMLInputCallback( ?Closure $value, Closure ...$functions ) : ?Closure 
  {
    if ( empty( $functions ))
      return $value;
    
    return function( IModel $model, IProperty $prop, string $name, string $id ) use($value,$functions) : IElement {
      $propValue = null;
      if ( $value != null )
        $propValue = $value( $model, $prop, $name, $id, $propValue );
      
      foreach ( $functions as $f )
      {
        $propValue = $f( $model, $prop, $name, $id, $propValue );
      }

      return $propValue;
    };      
  }  
  
  
  
  /**
   * @param ?Closure $value Closure|null from property config array
   * @param Closure ...$functions A list of additional functions 
   * @return Closure|null Combined function
   */
  private function getIsEmptyCallback( ?Closure $value, Closure ...$functions ) : ?Closure 
  {
    if ( empty( $functions ))
      return $value;
    
    $dv = $this->defaultValue;
    
    return function( IProperty $prop, $propValue ) use($value,$functions,$dv) {
      if ( $value != null 
        && $value( $prop, $propValue, $dv ))
      {
        return true;
      }
        
      
      foreach( $functions as $f )
      {
        if ( $f( $prop, $propValue, $dv ))
          return true;
      }
      
      return false;
    };    
  }    
  
  
  
  
  /**
   * Create a validate callback for multiple functions.
   * @param ?Closure $value Closure|null from property config array
   * @param Closure ...$functions A list of additional functions 
   * @return Closure|null Combined function
   */
  private function getValidateCallback( ?Closure $value, Closure ...$functions ) : ?Closure 
  {
    if ( empty( $functions ))
      return $value;
    
    return function( IProperty $prop, $propValue ) use($value,$functions) : bool {
      if ( $value != null && !$value( $prop, $propValue ))
        return false;
      
      foreach( $functions as $f )
      {
        if ( !$f( $prop, $propValue ))
          return false;
      }
      
      return true;
    };      
  }
  
  
  
  
  
  /**
   * Create a property getter/setter callback for multiple functions.
   * @param ?Closure $value Closure|null from property config array
   * @param Closure ...$functions A list of additional functions 
   * @return Closure|null Combined function
   */
  private function getGetterSetterCallback( ?Closure $value, Closure ...$functions ) : ?Closure 
  {
    if ( empty( $functions ))
      return $value;
    
    return function( IProperty $prop, $propValue ) use($value,$functions) {
      if ( $value != null )
        $propValue = $value( $prop, $propValue );
      
      foreach( $functions as $f )
      {
        $propValue = $f( $prop, $propValue );
      }
      
      return $propValue;
    };
  }
  
  
  /**
   * Create a property model setter callback for multiple functions.
   * @param ?Closure $value Closure|null from property config array
   * @param Closure ...$functions A list of additional functions 
   * @return Closure|null Combined function
   */
  private function getModelGetterSetterCallback( ?Closure $value, Closure ...$functions ) : ?Closure 
  {
    if ( empty( $functions ))
      return $value;
    
    return function( IModel $model, IProperty $prop, $propValue, array $context = [] ) use($value,$functions) {
      if ( $value != null )
        $propValue = $value( $model, $prop, $propValue, $context );
      
      foreach( $functions as $f )
      {
        $propValue = $f( $model, $prop, $propValue, $context );
      }
      
      return $propValue;
    };    
  }
  
  
  /**
   * Process a config entry.
   * 
   * For any callbacks attached to the config, concatenate that callback with 
   * any attached to some named property behavior.
   * 
   * @param string $prop Property name 
   * @param mixed $value Config entry property value 
   * @param INamedPropertyBehavior $behavior Behavior for this property 
   * @return mixed value 
   * 
   * @todo This is not maintainable.
   */
  private function processEntry( string $prop, $value, INamedPropertyBehavior ...$behavior )
  {
    if ( empty( $behavior ))
      return $value; 
    
    switch( $prop )
    {
      case self::INIT:
        return $this->getInitCallback( $value, ...$this->getFunctionList( 'getInitCallback', ...$behavior ));
        
      case self::VALIDATE:
        return $this->getValidateCallback( $value, ...$this->getFunctionList( 'getValidateCallback', ...$behavior ));
        
      case self::SETTER:
        return $this->getGetterSetterCallback( $value, ...$this->getFunctionList( 'getSetterCallback', ...$behavior ));
        
      case self::GETTER:
        return $this->getGetterSetterCallback( $value, ...$this->getFunctionList( 'getGetterCallback', ...$behavior ));
                
      case self::MSETTER:
        return $this->getModelGetterSetterCallback( $value, ...$this->getFunctionList( 'getModelSetterCallback', ...$behavior ));
        
      case self::MGETTER:
        return $this->getModelGetterSetterCallback( $value, ...$this->getFunctionList( 'getModelGetterCallback', ...$behavior ));
        
      case self::CHANGE:
        return $this->getOnChangeCallback( $value, ...$this->getFunctionList( 'getOnChangeCallback', ...$behavior ));
        
      case self::HTMLINPUT:
        return $this->getHTMLInputCallback( $value, ...$this->getFunctionList( 'getHTMLInputCallback', ...$behavior ));
        
      case self::TOARRAY:
        return $this->getModelGetterSetterCallback( $value, ...$this->getFunctionList( 'getToArrayCallback', ...$behavior ));
        
      case self::IS_EMPTY:
        return $this->getIsEmptyCallback( $value, ...$this->getFunctionList( 'getIsEmptyCallback', ...$behavior ));
        
      default:
        return $value;
    }
  }
}
