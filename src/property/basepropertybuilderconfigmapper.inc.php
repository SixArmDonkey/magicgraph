<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\property;

use Closure;
use InvalidArgumentException;




/**
 * Maps a configuration array to a list of properties.
 * The configuration array is as follows:
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
 */
class BasePropertyBuilderConfigMapper extends DefaultPropertyConfig implements IConfigMapper
{
  /**
   * PropertyBuilder instance factory 
   * @var IPropertyBuilderIoC
   */
  private $pbIoc;
  
  /**
   * Property factory 
   * @var IPropertyIoC
   */
  private $pIoc;
  
  /**
   * A map of string => IPropertyType
   * @var array
   */
  private $propertyTypes;
  
  /**
   * Retrieve a new propertyFlags instance
   * @var \Closure 
   */
  private $getPropertyFlags;
  
  
  /**
   * Create a new PropertyFactory using some configuration array 
   * @param IPropertyConfig $config One or more configuration instances.
   * @param Closure|null $getPropertyFlags f() : IPropertyFlags - Retrieve a new property flags instance for use 
   */
  public function __construct( IPropertyBuilderIoC $pbIoc, IPropertyIoC $pIoc, ?Closure $getPropertyFlags = null )
  {
    $k1 = $pIoc->getTypes();
    $k2 = $pbIoc->getTypes();
    
    if ( sizeof( $k1 ) != sizeof( $k2 ) || !empty( array_diff( $k1, $k2 )))
      throw new InvalidArgumentException( 'pbIoC and pIoc must have matching key sets' );
    
    $this->pbIoc = $pbIoc;
    $this->pIoc = $pIoc;
    $this->propertyTypes = $k1;
    $this->getPropertyFlags = $getPropertyFlags;
  }

  
  /**
   * Take a config array and convert it to a list of IProperty instances.
   * If anything is wrong, exceptions get thrown.
   * @param array $config
   * @return array IProperty[] list of properties defined in $config 
   * @throws \InvalidArgumentException
   * @throws \Exception 
   */
  public function map( array $config ) : array
  {
    $out = [];

    foreach( $config as $name => $data )
    {
      if ( isset( $out[$name] ))
        throw new InvalidArgumentException( "A property with the name: " . $name . " has already been defined. Check " . get_class( $config ));
      else if ( !is_array( $data ))
        throw new InvalidArgumentException( 'Config entries must be an array.  "'  . $name . '" (encountered key) must be an array' );
      else if ( !isset( $data[self::TYPE] ))
        throw new InvalidArgumentException( 'Config entries must a type.  "'  . $name . '" must have a type.' );

      $b = $this->createBuilder( $name, $data );
      $out[$name] = $this->createProperty( $b );
    }
    
    return $out;
  }
  
  
  /**
   * Create a new PropertyBehavior instance.
   * @param Closure|null $validate Validate closure f( IProperty, $value ) 
   * Throw a ValidationException on error 
   * @param Closure|null $init
   * @param Closure|null $setter
   * @param Closure|null $getter
   */
  protected function createPropertyBehavior( ?Closure $validate = null, ?Closure $init = null, ?Closure $setter = null, ?Closure $getter = null,
    ?Closure $msetter = null, ?Closure $mgetter = null, ?Closure $onChange = null, ?Closure $isEmpty = null, ?Closure $htmlInput = null ) : IPropertyBehavior
  {
    //..I didn't think this was all that necessary to provide easy control over.
    //..Gonna have to subclass to override.  It's just a bucket...
    return new PropertyBehavior( $validate, $init, $setter, $getter, $msetter, $mgetter, $onChange, $isEmpty, $htmlInput );
  }
  
    
  /**
   * 
   * @return \buffalokiwi\magicgraph\IPropertyFlags
   */
  protected function createPropertyFlags( $values ) : IPropertyFlags
  {
    if ( $this->getPropertyFlags != null )
    {
      $f = $this->getPropertyFlags;
      $flags = $f();
    }
    else
      $flags = new SPropertyFlags();
    
    if ( !empty( $values ))
    {
      $flags->add( ...$values );
      
      if ( $flags->hasVal( IPropertyFlags::PRIMARY ))
        $flags->add( IPropertyFlags::NO_UPDATE, IPropertyFlags::WRITE_EMPTY );
    }
    
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
  
  
  
  
  
 
  
  private function createProperty( IPropertyBuilder $builder ) : IProperty 
  {
    $prop = $this->pIoc->create( $builder );
    if ( !( $prop instanceof IProperty ))
      throw new \Exception( sprintf( 'Property factory for %s does not return an instance of IProperty.  Got %s of class %s', $builder->getType()->value(), gettype( $prop ), get_class( $prop )));
    return $prop;
  }
    
  
  private function createBuilder( string $name, array $data ) : IPropertyBuilder
  {
    $b = $this->pbIoc->create( $name, $data[self::TYPE] );
    $b->setName( $name );
    
    $validate = null;
    $init = null;
    $setter = null;
    $getter = null;
    $msetter = null;
    $mgetter = null;
    $onChange = null;
    $isEmpty = null;
    $htmlInput = null;
    
    foreach( $data as $k => $v )
    {
      switch( $k )
      {
        case self::SETTER:
          if ( !( $v instanceof Closure ))
            throw new InvalidArgumentException( $name . '::' . $k . ' must be a closure' );
          $setter = $v;
        break;

        case self::GETTER:
          if ( !( $v instanceof Closure ))
            throw new InvalidArgumentException( $name . '::' . $k . ' must be a closure' );

          $getter = $v;
        break;
        
        case self::MSETTER:
          if ( !( $v instanceof Closure ))
            throw new InvalidArgumentException( $name . '::' . $k . ' must be a closure' );
          $msetter = $v;
        break;

        case self::MGETTER:
          if ( !( $v instanceof Closure ))
            throw new InvalidArgumentException( $name . '::' . $k . ' must be a closure' );

          $mgetter = $v;
        break;

        case self::INIT:
          if ( !( $v instanceof Closure ))
            throw new InvalidArgumentException( $name . '::' . $k . ' must be a closure' );

          $init = $v;
        break;

        case self::VALIDATE:
          if ( !( $v instanceof Closure ))
            throw new InvalidArgumentException( $name . '::' . $k . ' must be a closure' );
          $validate = $v;
        break;            

        case self::CHANGE:
          if ( !( $v instanceof Closure ))
            throw new InvalidArgumentException( $name . '::' . $k . ' must be a closure' );
          $onChange = $v;
        break;
        
        case self::IS_EMPTY:
          if ( !( $v instanceof Closure ))
            throw new InvalidArgumentException( $name . '::' . $k . ' must be a closure' );
          $isEmpty = $v;
        break;
        
        case self::HTMLINPUT:
          if ( !( $v instanceof Closure ))
            throw new InvalidArgumentException( $name . '::' . $k . ' must be a closure' );
          
          $htmlInput = $v;
        break;
        
        default:
          $this->setProperty( $b, $name, $k, $v );
        break;
      }          
    }
    
    $b->addBehavior( $this->createPropertyBehavior( $validate, $init, $setter, $getter, $msetter, $mgetter, $onChange, $isEmpty, $htmlInput ));
    
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
}
