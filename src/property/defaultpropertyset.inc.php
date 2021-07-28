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

use buffalokiwi\buffalotools\types\BigSet;
use Closure;
use InvalidArgumentException;


/**
 * A Basic property set implementation used for storing a list of properties
 * that represent a row from some data source.
 * This property set only allows a single property to be flagged as primary.
 * 
 * When using multiple property config objects, if any property name is ambiguous, the last
 * encountered instance is used.
 * 
 * @todo is there a point to this being backed by BigSet?
 * 
 */
class DefaultPropertySet extends BigSet implements IPropertySet
{
  private static int $ref = 0;
  
  /**
   * List of properties
   * @var IProperty[]
   */
  private $properties = [];  
  
  /**
   * Primary key name 
   * @var string
   */
  private $primaryKey;
  
  /**
   * Property config.
   * Cast this to some type to use it.
   * @var IPropertyConfig[]
   */
  private $config = [];
  
  /**
   * Property factory 
   * @var IPropertyFactory
   */
  private $propFactory;
  
  
  /**
   * Extra validation closures for the model validate 
   * @var Closure[]
   */
  private $modelValidation = [];
  
  
  /**
   * Read only flag 
   * @var boolean 
   */
  private $readOnly = false;
  
  /**
   * A list of property names that have been added via addPropertyConfig().
   * @var string[] 
   */
  private $addedMembers = [];
  
  /**
   * Callbacks for when new members are added.
   * 
   * f( array $memberNames ) : void 
   * 
   * @var \Closure[] 
   */
  private $onAddMember = [];
  
  private $hash;
  
  
  private array $prefixList = [];
  
  /**
   * Create a new DefaultPropertySet instance 
   * @param IPropertyFactory $properties Properties 
   * @throws InvalidArgumentException 
   */
  public function __construct( IPropertyFactory $properties, IPropertyConfig ...$config )
  {

    $this->hash = $this->generateHash( ...$config );
    //..Build the set instance 
    parent::__construct( '@@' . $this->hash );
    
    $this->propFactory = $properties;

    $this->addPropertyConfig( ...$config );
  }
  
  
  private function generateHash( IPropertyConfig ...$config ) : string
  {
    $props = '';
    
    foreach( $config as $c )
    {
      $props .= implode( '', $c->getPropertyNames());
    }    
    
    //..This allows things like the databag to work.
    //  Empty property sets produce the same hash, which causes collisions in the caching mechanism 
    if ( empty( $props ))
      $props = uniqid() . ++self::$ref;
    
    return md5( $props );
  }
  
  
  public function setOnAddMember( \Closure $callback ) : void
  {
    $this->onAddMember[] = $callback;
  }
  
  
  /**
   * Sets the property set to read only.
   * This is a method because the model still needs to be written to when 
   * creating instances populated from persistent storage.  The idea is for the
   * mapping object factory to call this method after filling the model, but 
   * before returning it.
   */
  public function setReadOnly() : void
  {
    $this->readOnly = true;
    foreach( $this->properties as $p )
    {
      /* @var $p IProperty */
      $p->setReadOnly();
    }
  }
  
  
  /**
   * Retrieve the product property configuration array 
   * @return array
   */
  public function getPropertyConfigArray() : array
  {
    $out = [];
    foreach( $this->config as $c )
    {
      $out = array_merge( $out, $c->getConfig());
    }
    
    return $out;
  }
  
  
  
  /**
   * An array of closures for model validation
   * f( IModel
   * @return array Closures
   */
  public final function getModelValidationArray() : array
  {
    return $this->modelValidation;
  }
  
  
  /**
   * Used to add properties to this property set 
   * @param IPropertyConfig ...$config config to add
   * @throws InvalidArgumentException 
   * @final 
   */
  public final function addPropertyConfig( IPropertyConfig ...$config )
  {
    foreach( $config as $c )
    {
      /* @var $c IPropertyConfig */
      $f = $c->getValidation();
      if ( $f != null )
        $this->modelValidation[] = $f;
    }
    
    $this->config = array_merge( $this->config, $config );
    
    
    $newNames = [];
    foreach( $this->propFactory->getProperties( ...$config ) as $property )
    {
      //..Ensure the instance is correct
      if ( !( $property instanceof IProperty ))
        throw new InvalidArgumentException( "All properties must be an instance of IProperty" );
      
      
      if ( isset( $this->properties[$property->getName()] ))
        continue;
      
      $newNames[] = $this->addAndInitializeNewProperty( $property );
    }    
    
    $this->addNewMembers( ...$newNames );
  }
  
  
  
    /**
   * Check to see if const is a member of this set.
   * @param string $const constant
   * @return boolean is member
   */
  public function isMember( string ...$const ) : bool
  {
    foreach( $const as $c )
    {
      if ( !parent::isMember( $c ))
      {
        $p = $this->getPrefixProperty( $c );
        if ( $p === false )
        {
          return false;
        }
        $p = $this->getProperty( $p );
        
        
        if ( $p->getType()->is( IPropertyType::TMODEL ) && !$this->getProperty( $p->getName())->getValue()->getPropertySet()->isMember( substr( $c, strlen( $p->getPrefix()))))
        {
          return false;
        }
      }

    }
    
    
    
    return true;
  }
  
  
  private function getPrefixProperty( string $prop ) : string|false
  {
    foreach( $this->prefixList as $parent => $prefix )
    {
      if ( substr( $prop, 0, strlen( $prefix )) == $prefix )
      {
        return $parent;
      }
    }
    
    return false;
  }
  
  
  
  
  
  /**
   * Given an IProperty, initialize the property by calling reset() and add that property to the
   * DefaultPropertySet.  Call addNewMembers() after calling this method to add the property names to the underlying
   * BigSet instance. 
   * @param IProperty $property Property 
   * @return string New property name 
   * @todo watch out for the preg_match call in the profiler.  Optimize if necessary.
   */
  private function addAndInitializeNewProperty( IProperty $property ) : string
  {
    //..Adding an alphanumeric check for property names.  Since these are directly inserted into queries,
    //  this should at least provides some basic protection against sql injection via column names
    //..The idea is for property sets/configurations to whitelist column names, but since we can allow properties
    //  to be added at runtime, from the database, etc AND custom IProperty implementations without name checking may be 
    //  used, we need to at least have minimal sanity checking.    
    if ( !preg_match( '/^[a-zA-Z0-9_]+$/', $property->getName()))
      throw new \InvalidArgumentException( 'Property names must match the pattern "/^[a-zA-Z0-9_]+$/"' );
        
    
    //..THIS IS CRITICALLY IMPORTANT TO CALL PRIOR TO USING ANY NEW IPROPERTY INSTANCE
    //  THIS CALL IS REQUIRED TO INTIALIZE ALL DEFAULT VALUES AND INSTANCES WITHIN THE PROPERTY
    $property->reset();

    //..Add this to the members array for the parent Set instance
    $this->members[] = $property->getName();

    //..Add the property to the local list 
    $this->properties[$property->getName()] = $property;

    
    if ( empty( $this->primaryKey ) && $property->getFlags()->hasVal( IPropertyFlags::PRIMARY ))
    {
      //..Save the name 
      $this->primaryKey = $property->getName();
    }    
    
    if ( !empty( $property->getPrefix()))
    {
      $this->prefixList[$property->getName()] = $property->getPrefix();
    }
    
    return $property->getName();
  }
  
  
  /**
   * Modifies the parent BigSet instance and adds the newly created members.  
   * @param string $newNames
   * @return void
   */
  private function addNewMembers( string ...$newNames ) : void
  {
    $this->addMember( ...$newNames );
    
    $args = [];
    foreach( $newNames as $n )
    {
      if ( !in_array( $n, $this->addedMembers ))
      {
        $this->addedMembers[] = $n;
        $args[] = $n;
      }
    }
    
    if ( !empty( $args ))
    {
      foreach( $this->onAddMember as $f )
      {
        $f( $args );
      }    
    }    
  }
  
  
  /**
   * Adds a property to the property set.  For a more robust solution, please use the preferred method: addPropertyConfig().
   * @param IProperty $prop Property to add
   * @return void
   * @final 
   */
  public final function addProperty( IProperty $prop ) : void
  {
    $this->addNewMembers( $this->addAndInitializeNewProperty( $prop ));    
  }
  
  
  /**
   * Retrieve a list of property names that have been added via addPropertyConfig().
   * @return array names 
   */
  public final function getNewMembers() : array
  {
    return $this->addedMembers;
  }
  
  
  /**
   * Retrieve the property configuration.
   * The supplied IPropertyConfig instance must have the name interface or class
   * name as the value of $interface.  Otherwise an exception is thrown.
   * This is used to retrieve methods attached to the property config instance.
   * @param string $interface The interface name of the desired property config.
   * If the internal instance does not implement $interface, then an exception is thrown.
   * @return IPropertyConfig
   * @throws \Exception if config does not implement $interface
   */
  public function getPropertyConfig( string $interface ) : IPropertyConfig
  {
    
    $tested = [];
    foreach( $this->config as $c )
    {

      if ( is_a( $c, $interface, false )) 
        return $c;
      else
        $tested[] = get_class( $c );
    }
    
    throw new \Exception( 'Requested IPropertyConfig instance can not be cast to ' . $interface . ' when type must equal one of: ' . implode( ',', class_implements( $interface )) . '.  Tested classes: ' . implode( ',', $tested ));
  }
  
  
  /**
   * Determine if this property set was built using a specific interface.
   * @param string $interface Interface to test.
   * @return bool Implements 
   */
  public function containsConfig( string $interface ) : bool
  {
    foreach( $this->config as $c )
    {
      if ( is_a( $c, $interface, false )) 
        return true;
    }

    return false;    
  }
  
  
  /**
   * Retrieve a list of all of the currently enabled property config instances 
   * attached to this property set.
   * @return array IPropertyConfig[] config instances 
   */
  public function getConfigObjects() : array
  {
    return $this->config;
  }
  
  
  
  /**
   * Retrieve a multi-dimensional array, which defines all properties in this object.
   * @return array schema
   */
  public function getSchema() : array
  {
    $out = [];
    
    foreach( $this->getProperties() as $prop )
    {
      /* @var $prop IProperty */
      $out[$prop->getName()] = [
        'name' => $prop->getName(),
        'caption' => $prop->getCaption(),
        'type' => $prop->getType()->value(),
        'defaultValue' => $prop->getDefaultValue(),
        'flags' => implode( ',', $prop->getFlags()->getActiveMembers())
      ];
    }
    
    return $out;
  }
  
  
  
  /**
   * Clone the internal property list 
   */
  public function __clone()
  {
    foreach( array_keys( $this->properties ) as $k )
    {
      $this->properties[$k] = clone $this->properties[$k];
    }      
    
        
    foreach( $this->modelValidation as &$f )
    {
      $f = $f->bindTo( $this );
    }
  }

  
  /**
   * Retrieve a property by name 
   * @param string $name name
   * @return IProperty property 
   */
  public function getProperty( string $name ) : IProperty
  {
    if ( !isset( $this->properties[$name] ))
    {
      $name = $this->getPrefixProperty( $name );
      if ( $name === false || !isset( $this->properties[$name] ))
        throw new InvalidArgumentException( $name . ' is not a member of this property set' );
    }
    
    return $this->properties[$name];
  }
    
  
  /**
   * Retrieve the property that represents the primary key.
   * If multiple primary keys are used, this returns the first one in the list.
   * Use getPrimaryKeys() if using compound primary keys.
   * @return IProperty property   
   * @throws \Exception if there is no primary key defined 
   */
  public function getPrimaryKey() : IProperty
  {    
    if ( empty( $this->primaryKey ))
      throw new \Exception( 'There is no primary key defined in this set' );
    
    return $this->properties[$this->primaryKey];
  }
  
  
  /**
   * Retrieve an array of keys flagged as primary.
   * If there are no primary keys defined, this returns an empty array 
   * @return array IProperty[] primary keys
   */
  public function getPrimaryKeys() : array
  {
    $out = [];
    
    /* @var $prop IProperty */
    foreach( $this->properties as $prop )
    {
      if ( $prop->getFlags()->hasVal( IPropertyFlags::PRIMARY ))
        $out[] = $prop;
    }
    
    return $out;
  }
  
  
  /**
   * Retrieve the primary key property names.
   * @return string[] names
   */
  public function getPrimaryKeyNames() : array
  {
    $out = [];
    foreach( $this->getPrimaryKeys() as $prop )
    {
      $out[] = $prop->getName();
    }
    
    return $out;
  }
  
  
  
  /**
   * Retrieve a list of all the properties 
   * @param string ...$name Optional list of properties to return by name 
   * @return IProperty[] properties
   */
  public function getProperties( string ...$name ) : array
  {
    if ( empty( $name ))
      return $this->properties;
    
    $out = [];
    foreach( $name as $n )
    {
      $out[] = $this->getProperty( $n );
    }
    
    return $out;
  }
  
  
  /**
   * Retrieve a list of properties by flag.
   * @param IPropertyFlags $flags Flags to test 
   * @return IProperty[] properties
   */
  public function findProperty( IPropertyFlags $flags ) : array
  {
    $out = [];
    foreach( $this->properties as $p )
    {
      foreach( $flags->getActiveMembers() as $f )
      {
        if ( $p->getFlags()->hasVal( $f ))
        {
          $out[] = $p;
          break;
        }
      }
    }
    
    return $out;
  }
  
  
  /**
   * Retrieve a list of properties by data type.
   * @param IPropertyType $type Type
   * @return IProperty[] properties
   */
  public function getPropertiesByType( IPropertyType $type ) : array
  {
    $out = [];
    
    foreach( $this->properties as $p )
    {
      if ( $p->getType()->value() == $type->value())
        $out[] = $p;
    }
    
    return $out;
  }
  
  
  /**
   * Retrieve a list of properties by flag.
   * @param string $flags flags 
   * @return array properties 
   */
  public function getPropertiesByFlag( string ...$flags ) : array
  {
    $out = [];
    foreach( $this->properties as $p )
    {
      if ( $p->getFlags()->hasVal( ...$flags ))
        $out[] = $p;
    }
    
    return $out;
  }
}
