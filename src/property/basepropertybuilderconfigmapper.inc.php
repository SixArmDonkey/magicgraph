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
 * Maps a configuration array to a list of properties.
 * 
 * This is a very specific implementation based on IPropertyConst and IBehaviorConst constants.
 * All configuration array properties within those two interfaces must be implemented here.
 * 
 * Some of the configuration array is this (see readme):
 * 
 * [
 *   'property_name' => [   //..This should be a constant from the property set interface for whatever model is being used 
 *     PropertyFactory::TYPE => IPropertyType::[constant],
 *     PropertyFactory::FLAGS => [IPropertyFlags::[constant], ...],
 *     PropertyFactory::CLASS => '\namespace\classname', //(Required for TENUM,TSET and TMONEY types)
 *     PropertyFactory::MIN => 0, 
 *     PropertyFactory::MAX => 100,
 *     PropertyFactory::PREPARE => function( $value, IModel $model) {},
 *     PropertyFactory::VALIDATE => function( IProperty $prop, $value ) {},
 *     PropertyFactory::PATTERN => '/[a-z]+/', //Some pattern to use as validation 
 *   ]
 * ];
 * 
 * @todo maybe make this extensible?  This is not good.
 */
class BasePropertyBuilderConfigMapper extends DefaultPropertyConfig implements IConfigMapper
{
  /**
   * PropertyBuilder instance factory 
   * @var IPropertyBuilderFactory
   */
  private IPropertyBuilderFactory $pbIoc;
  
  /**
   * Property factory 
   * @var IPropertyFactory
   */
  private IPropertyFactory $pIoc;
  
  /**
   * A map of string => IPropertyType
   * @var array
   */
  private array $propertyTypes;
  
  /**
   * Retrieve a new propertyFlags instance
   * @var \Closure 
   */
  private ?\Closure $getPropertyFlags;
  
  /**
   * Flags 
   * @var IPropertyFlags
   */
  private IPropertyFlags $flags;
  
  /**
   * If the flag set contains PRIMARY, NO_UPDATE and WRITE_EMPTY,
   * PRIMARY will cause NO_UPDATE and WRITE_EMPTY to be auto set 
   * @var bool
   */
  private bool $setDefaultPrimaryFlags;
  
  
  /**
   * @param IPropertyBuilderFactory $pbIoc Property Builder Factory 
   * @param IPropertyFactory $pIoc IProperty Factory 
   * @param Closure|null $getPropertyFlags f() : IPropertyFlags - Retrieve a new property flags instance for use 
   * @throws InvalidArgumentException
   */
  public function __construct( IPropertyBuilderFactory $pbIoc, IPropertyFactory $pIoc, ?Closure $getPropertyFlags = null )
  {
    $k1 = $pIoc->getTypes();
    $k2 = $pbIoc->getTypes();
    
    if ( sizeof( $k1 ) != sizeof( $k2 ) || !empty( array_diff( $k1, $k2 )))
      throw new InvalidArgumentException( 'PropertyBuilderFactory and PropertyFactory must have matching key sets' );
    
    $this->pbIoc = $pbIoc;
    $this->pIoc = $pIoc;
    $this->propertyTypes = $k1;
    $this->getPropertyFlags = $getPropertyFlags;
    
    //..createPropertyFlags is invoked a lot, and this test only needs to happen once.      
    $this->flags = $this->createPropertyFlagsInstance();
    $this->setDefaultPrimaryFlags = $this->flags->isMember( 
      IPropertyFlags::PRIMARY, 
      IPropertyFlags::NO_UPDATE, 
      IPropertyFlags::WRITE_EMPTY 
    );    
  }

  
  /**
   * Take a config array and convert it to a list of IProperty instances.
   * If anything is wrong, exceptions get thrown.
   * @param array $config Property configuration array 
   * @return array IProperty[] list of properties defined by $config 
   * @throws \InvalidArgumentException
   * @throws \Exception 
   */
  public function map( array $config ) : array
  {
    $out = [];

    foreach( $config as $name => $data )
    {
      if ( isset( $out[$name] ))
      {
        throw new InvalidArgumentException( "A property with the name: " . $name . " has already been defined. Check "
          . get_class( $config ));
      }
      else if ( !is_array( $data ))
      {
        throw new InvalidArgumentException( 'Config entries must be an array.  "'  . $name 
          . '" (encountered key) must be an array' );
      }      
      else if ( !isset( $data[self::TYPE] ))
      {
        throw new InvalidArgumentException( 'Config entries must a type.  "'  . $name . '" must have a type.' );
      }

      $b = $this->createBuilder( $name, $data );
      $out[$name] = $this->pIoc->createProperty( $b );
    }
    
    return $out;
  }
  
  
  /**
   * Create a property behavior instance.  This creates and returns instances of class: PropertyBehavior.
   * Subclass and override if desired.
   * @param Closure|null $validate Validate callback
   * @param Closure|null $init Property initialization callback 
   * @param Closure|null $setter Property setter 
   * @param Closure|null $getter Property getter 
   * @param Closure|null $msetter Property setter with IModel
   * @param Closure|null $mgetter Property getter with IModel
   * @param Closure|null $onChange change event
   * @param Closure|null $isEmpty empty event 
   * @param Closure|null $htmlInput converts the property to an html input 
   * @param Closure|null $toArray used with IModel::toArray().  Determines the serialized property value.
   * @return IPropertyBehavior The behavior instance 
   */
  protected function createPropertyBehavior( ?Closure $validate = null, ?Closure $init = null, ?Closure $setter = null,
    ?Closure $getter = null, ?Closure $msetter = null, ?Closure $mgetter = null, ?Closure $onChange = null, 
    ?Closure $isEmpty = null, ?Closure $htmlInput = null, ?Closure $toArray = null ) : IPropertyBehavior
  {
    //..I didn't think this was all that necessary to provide easy control over.
    //..Gonna have to subclass to override.  It's just a bucket...
    return new PropertyBehavior( $validate, $init, $setter, $getter, $msetter, $mgetter, $onChange, 
      $isEmpty, $htmlInput, $toArray );
  }


  /**
   * create a property flags instance using the constructor-supplied flag instance factory closure, or 
   * an instance of SPropertyFlags and initialized by $values.
   * @param array|string|int $values An array of property flag values, a string representing a single flag value, or 
   * an integer representing the total bitmask value.
   * @return IPropertyFlags Flags instance 
   */
  protected function createPropertyFlags( array|string|int $values ) : IPropertyFlags
  {
    $flags = $this->createPropertyFlagsInstance();
    if ( is_array( $values ))
      $flags->add( ...$values );
    else if ( is_string( $values ))
      $flags->add( $values );
    else if ( is_int( $values ))
      $flags->setValue( $values );
    
    if ( $this->setDefaultPrimaryFlags && $flags->hasVal( IPropertyFlags::PRIMARY ))
      $flags->add( IPropertyFlags::NO_UPDATE, IPropertyFlags::WRITE_EMPTY );
    
    return $flags;
  }
  
  
  /**
   * Sets the builder property value based on the array key.
   * This function intentionally does nothing.  Override this to handle
   * some custom property type. 
   * @param PropertyBuilder $b Builder instance 
   * @param string $name Property Name 
   * @param string $k Property Attribute Name 
   * @param string $v Property Attribute Value 
   * @throws InvalidArgumentException
   */
  protected function setCustomProperty( PropertyBuilder $b, string $name, string $k, $v )
  {
    //..Do nothing.  Override this and implement something.
  }
    

  /**
   * Given array $data, test $key is set and if so, pass $data[$key] to $actionIfIsset and invoke.  Otherwise
   * return null;
   * @param array $data Data array
   * @param string $key Key to find 
   * @param \Closure $actionIfIsset fn( string $key, mixed $v ) : void - Invoked if isset 
   * @return mixed|null Value or null 
   */
  private function getPropertyFromArray( array $data, string $key, \Closure $actionIfIsset ) : mixed 
  {
    if ( isset( $data[$key] ))
    {
      //..This cast might break things
      $actionIfIsset((string)$key, $data[$key] );
      return $data[$key];
    }
    
    return null;
  }

  
  /**
   * Maps config entries to a property builder.
   * Behavior events are tested to ensure they are closures.
   * 
   * @param string $name Property name 
   * @param array $data Configuration array 
   * @return IPropertyBuilder A type-appropriate builder instance 
   * @throws InvalidArgumentException
   */
  private function createBuilder( string $name, array $data ) : IPropertyBuilder
  {
    if ( !is_string( $data[self::TYPE] ))
    {
      throw new \InvalidArgumentException( self::TYPE . ' configuration property of ' . $name . ' must be a string,  ' 
        . gettype( $data[self::TYPE] ) . ' given.' );
    }
    
    
    $b = $this->pbIoc->create( $name, $data[self::TYPE] );
    $b->setName( $name );
    
    $biTest = function( string $k, mixed $v ) use ($name) : void {
      if ( !( $v instanceof Closure ))
        throw new InvalidArgumentException( $name . '::' . $k . ' must be a closure' );
    };
    
    $b->addBehavior( $this->createPropertyBehavior( 
      $this->getPropertyFromArray( $data, self::VALIDATE, $biTest ),
      $this->getPropertyFromArray( $data, self::INIT, $biTest ),
      $this->getPropertyFromArray( $data, self::SETTER, $biTest ),
      $this->getPropertyFromArray( $data, self::GETTER, $biTest ),
      $this->getPropertyFromArray( $data, self::MSETTER, $biTest ),
      $this->getPropertyFromArray( $data, self::MGETTER, $biTest ),
      $this->getPropertyFromArray( $data, self::CHANGE, $biTest ),
      $this->getPropertyFromArray( $data, self::IS_EMPTY, $biTest ),
      $this->getPropertyFromArray( $data, self::HTMLINPUT, $biTest ),
      $this->getPropertyFromArray( $data, self::TOARRAY, $biTest )
    ));
    
    foreach( $data as $k => $v )
    {
      switch( $k )
      {
        case self::SETTER:
        case self::GETTER:
        case self::MSETTER:
        case self::MGETTER:
        case self::INIT:
        case self::VALIDATE:
        case self::CHANGE:
        case self::IS_EMPTY:
        case self::HTMLINPUT:
        case self::TOARRAY:
          //..do nothing
        break;
      
        default:
          $this->setProperty( $b, $name, $k, $v );
        break;
      }          
    }
    
    return $b;
  }
  
  
  /**
   * Sets the builder property value based on the array key 
   * @param PropertyBuilder $b Builder instance 
   * @param string $name Property Name 
   * @param string $k Property Attribute Name 
   * @param string $v Property Attribute Value 
   * @throws InvalidArgumentException
   */
  private function setProperty( IPropertyBuilder $b, string $name, string $k, $v )
  {
    try {
      switch( $k )
      {
        case self::TYPE:
          //..do nothing, this must not be overridden
        break;
      
        case self::VALUE:
          $b->setDefaultValue( $v );
        break;
      
        case self::FLAGS:
          $b->setFlags( $this->createPropertyFlags( $v ));
        break;

        case self::CLAZZ:
          if ( $b instanceof IObjectPropertyBuilder )
            $b->setClass( $v );
        break;

        case self::MIN:
          if ( $b instanceof IBoundedPropertyBuilder )
            $b->setMin( $v );
        break;

        case self::MAX:           
          if ( $b instanceof IBoundedPropertyBuilder )
            $b->setMax( $v );
        break;

        case self::PATTERN:
          if ( $b instanceof IStringPropertyBuilder )
            $b->setPattern( $v );
        break;
        
        case self::CONFIG:
          $b->setConfig( $v );
        break;
      
        case self::ID:
          $b->setId( $v );
        break;
      
        case self::CAPTION:
          $b->setCaption( $v );
        break;
      
        case self::PREFIX:
          $b->setPrefix( $v );
        break;
      
        case self::TAG:
          $b->setTag( $v );
        break;
      
        default:
          $this->setCustomProperty( $b, $name, $k, $v );
        break;
      }
    } catch( \Exception $e ) {
      throw new \Exception( 'Failed to set property ' . $k . ' for ' . $name . ':' . $e->getMessage(), 0, $e );
    }  
  }
  
  
  /**
   * Create an instance of IPropertyFlags.
   * If getPropertyFlags closure was supplied to the constructor, that is called.  otherwise an instance of 
   * SPropertyFlags is returned.
   * @return IPropertyFlags Flag set 
   * @throws \Exception If getPropertyFlags closure does not return an instance of IPropertyFlags 
   */
  private function createPropertyFlagsInstance() : IPropertyFlags
  {
    if ( $this->getPropertyFlags != null )
    {
      $f = $this->getPropertyFlags;
      $flags = $f();
      if ( !( $flags instanceof IPropertyFlags ))
      {
        throw new \Exception( 'getPropertyFlags closure must return an instance of IPropertyFlags.  got ' .
         (( is_object( $flags )) ? get_class( $flags ) : gettype( $flags )));
      }
    }
    else
      return new SPropertyFlags();
  }  
}
