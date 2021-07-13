<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph;

use ArrayIterator;
use buffalokiwi\buffalotools\date\IDateTime;
use buffalokiwi\buffalotools\types\IBigSet;
use buffalokiwi\buffalotools\types\ISet;
use buffalokiwi\buffalotools\types\RuntimeBigSet;
use buffalokiwi\magicgraph\property\IEnumProperty;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyBehavior;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\property\IPropertyType;
use Closure;
use InvalidArgumentException;
use JsonSerializable;
use stdClass;
use Traversable;


/**
 * The default property bag model for storing stuff.
 * 
 * I highly recommend creating children of this class that contain getter/setter
 * methods for any properties expected to be in the model.  It makes development
 * a lot easier in the long run. 
 * 
 * It's worth noting that you DO NOT have to descend from this class.  You can 
 * simply pass a property set to the default model, and go to town.
 */
class DefaultModel implements IModel
{
  /**
   * Maximum depth for toArray
   * @var int
   */
  private int $maxDepth = 5;
  
  /**
   * Properties 
   * @var IPropertySet 
   */
  private $properties;
  
  /**
   * A copy of the internal property set for use in other places.
   * This was short sighted...
   * @var IPropertySet
   */
  //private $cloneProps = null;
  
  /**
   * A copy of the internal list of names for use elsewhere.
   * @var IBigSet
   */
  private $cloneNames = null;
  
  /**
   * Edited properties 
   * @var IPropertySet
   */
  private $edited;
  
  /**
   * A map of prefix => property name.
   * When setValue is called, this map is checked.  If the current property name
   * matches an entry in this map, the value is used as the new property name.
   * 
   * 
   * 
   * @var array
   */
  private $prefixMap;
  
  /**
   * If validation is enabled 
   * @var bool
   */
  private $validationEnabled = true;
  
  /**
   * Can be used instead of checking the property set for members.
   * 
   * @var array
   */
  private array $memberCache = [];
  
  
  /**
   * If setValue is called with a non-valid property, the property and value will appear in this array.
   * getValue() and toArray() will look at the values in this array.
   * @var array [key => value]
   */
  private array $extraData = [];
  
  private static int $ref = 0;
  
  /**
   * Create a new DefaultModel instance.
   * 
   * The supplied property set is used to determine what properties are available
   * and the behavior of those properties attached to the model instance.  
   * 
   * The supplied property set is cloned twice into two internal properties:
   * "properties" and "edited".  "properties" contains the master property set.
   * This is freely editable and can be used as input for various methods.
   * 
   * The "edited" property is the list of properties attached to the model that have 
   * been edited.  Each time setValue() is called, the corresponding bit in the 
   * "edited" property is enabled.  When getModifiedProperties() is called, 
   * the enabled bits in edited are used to determine which properties to return.
   * 
   * For each property, a default value is set.
   * If the property has an init callback, the property's default value property
   * is supplied as an argument to the init callback, and the return value is used
   * as the default property value within the model.  If the init callback is not
   * defined, then the property's default value is used as the default property 
   * value within the model.
   * 
   * 
   * @param IPropertySet $properties Properties 
   */
  public function __construct( IPropertySet $properties )
  {
    $this->properties = $properties;
    //..A reduced version of the property set without all the property data.
    $m = $properties->getMembers();
    $hash = md5( implode( '', $m ));
    $this->edited = new RuntimeBigSet( $m ,'@@' . $hash );  
    
    /*
    $this->properties->setOnAddMember( function( array $newMembers ) use($properties) : void {
      $this->edited->addMember( ...$newMembers );
    });
     * 
     */
    
    /**
     * Prefixes are used to identity a property of a child model
     */
    
    //..Load the list of prefixes from the properties 
    //..This is a map of prefix => child property name 
    $prefixList = [];
    foreach( $this->properties->getProperties() as $p )
    {
      /* @var $p IProperty */
      
      if ( !empty( $p->getPrefix()))
      {
        //..add the entry 
        $prefixList[$p->getPrefix()] = $p->getName();
      }
      
      /*
      if ( $p->getType()->is( IPropertyType::TENUM ))
      {        
        $p->getValueAsEnum()->setOnChange( function() use($p) {
          $this->edited->add( $p->getName());
        });
      }
      */
    }
    
    
    //..Create a prefix map by prefix length
    //..This is a silly "optimization" that hopefully reduces the number of 
    //..checks for prefixes     
    $this->prefixMap = [];
    foreach( $prefixList as $prefix => $propName )
    {
      $len = strlen( $prefix );
      if ( !isset( $this->prefixMap[$len] ))
        $this->prefixMap[$len] = [];
      
      //..Buckets by length 
      $this->prefixMap[$len][] = [$prefix, $propName];
    }
    
    //..Sort by length in reverse 
    uksort( $this->prefixMap, function( &$a, &$b ) {
      if ( $a == $b )
        return 0;
      return ( $a < $b ) ? 1 : -1;
    });    
  }
  
  
  
  
  /**
   * Toggle validation.  If disabled, then the call to validate() simply 
   * returns.
   * @param bool $on
   * @return void
   * @final 
   */
  protected final function toggleValidation( bool $on ) : void
  {
    $this->validationEnabled = $on;
  }
  
  
  protected final function isValidationEnabled() : bool
  {
    return $this->validationEnabled;
  }
  
  
  /**
   * WARNING: Property values MUST be copied over to the cloned model
   * Prefer createCopy() over using clone directly.
   */
  public function __clone() 
  {
    if ( is_object( $this->cloneNames ))
      $this->cloneNames = clone $this->cloneNames;
    //$this->edited = clone $this->edited;
    $this->properties = clone $this->properties;
    $this->memberCache = [];
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
    $data = $this->toArray();
    ksort( $data );    
    return new ArrayIterator( array_reverse( $data ));
  }
  
  
  
  /**
   * Create a copy/clone of the model and all properties.
   * @param bool $copyIsSaveable If this is false, then the primary key flags
   * are removed from the copied model.  This will cause the repository save
   * method 
   * @return IModel Copied model 
   * @todo This is a code smell: Property types are being checked by name.  This is not scalable.  Need a flag on properties like "can have relationship provider" or something.
   */
  public function createCopy( bool $copyIsSaveable = true, bool $removePriKeys = true ) : IModel
  {
    //..Clone whatever object type this is.
    $r = clone $this;
        
    //..Copy the properties over without knowing a thing about the object
    
    foreach( $this->getPropertySet()->getProperties() as $p )
    {
      if ( !$p->getFlags()->hasVal( IPropertyFlags::PRIMARY ) 
        && !( $p->getFlags()->hasVal( IPropertyFlags::WRITE_EMPTY ) && !$r->getProperty( $p->getName())->isEmpty()))
      {
        $r->setValue( $p->getName(), $this->getValue( $p->getName()));      
      }
    }
    
    //..Find and remove the primary key flag from the new object.
    //..This will prevent the object from being able to be saved.
    foreach( $r->getPropertySet()->getPrimaryKeys() as $primary )
    {
      if ( !$copyIsSaveable )
      {
        /* @var $primary IProperty */
        $primary->getFlags()->remove( IPropertyFlags::PRIMARY, IPropertyFlags::REQUIRED );
      }
      
      if ( $removePriKeys )
      {
        $f = $primary->getFlags();
        $t = $f->getActiveMembers();
        $f->clear();
        
        //..This seems dumb
        /*
        if ( $primary->getType()->is( IPropertyType::TSTRING ))
          $r->setValue( $primary->getName(), '' );
        else if ( $primary->getType()->is( IPropertyType::TINTEGER ))
          $r->setValue( $primary->getName(), '0' );
        */
        
        //..This seems better.
        $primary->setValue( $primary->getDefaultValue());
        
        
        $f->add( ...$t );
      }
    }
    
    return $r;
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
    return $this->properties->getPropertyConfig( $intf );
  }
  

  /**
   * Determine if $name matches a property name defined in the IPropertySet
   * instance supplied to the constructor.
   * 
   * Calls IPropertySet::isMember()
   * 
   * @param string $name property name
   * @return boolean is set
   * @see ISet::isMember()
   */
  public function __isset( $name )
  {
    return $this->properties->isMember( $name );
  }


  /**
   * Generic getter.   
   * Alias of getValue()
   * @param string $p Property name
   * @return mixed Property value
   * @see DefaultModel::getValue()
   */
  public function & __get( $p )
  {
    return $this->getValue( $p );
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
    $this->setValue( $p, $v );
  }
  
  
  /**
   * A way to determine if this model OR if a property set is an instance of some interface.
   * This is used due to decorators.
   * @param string $interface Interface name
   * @return bool if this implements it 
   */
  public function instanceOf( string $interface ) : bool 
  {
    return is_subclass_of( $this, $interface ) || $this->properties->containsConfig( $interface );
  }
    
  
  /**
   * Retrieve the value of some property.
   * 
   * When retrieving a value:
   * 
   * First the supplied property name it checked to ensure it exists in the internal IPropertySet instance
   * If not, then an \InvalidArgumentException is thrown.
   * 
   * If the property has a getter callback, it is called with (this,IProperty,value) and the result of that
   * callback is returned.  Otherwise, the stored value is returned.
   * 
   * WARNING: This returns a reference.  Be careful about setting variables equal to this... 
   * 
   * 
   * @param string $property Property 
   * @return mixed value
   * @throws InvalidArgumentException if the property is not a member of the supplied IPropertySet instance  
   */
  public function & getValue( string $property, array $context = [] )
  {
    if ( isset( $this->extraData[$property] ))
      return $this->extraData[$property];
    
    //..Get the property of a child model by prefix
    $res = $this->getPropertyByPrefix( $property );
    if ( !empty( $res ))
    {
      //..Model prop is the property containing the child model
      //..prop is the child model property name 
      list( $modelProp, $prop ) = $res;
      
      //..Returns the property value of a child model 
      return $this->getValue( $modelProp )->getValue( $prop, $context );
    }
    
    
    
    //..Get the property entity 
    $prop = $this->getProperty( $property );    
    
    $value = $prop->getValue( $context );
    
    foreach( $prop->getPropertyBehavior() as $b )
    {
      $mgetter = $b->getModelGetterCallback();
      if ( $mgetter instanceof Closure )
      {
        $value = $mgetter( $this, $prop, $value, $context );
      }
    }
    
    return $value;
  }
    
  
  /**
   * Sets the value of some property.
   * 
   * If the IProperty::getPrepare() callback is used, $aValue is supplied as an argument, and the 
   * result of that callback is used as the value moving forward.
   * 
   * The value is validated against IProperty::validate()
   * 
   * The value is committed to the model using commitValue() 
   * 
   * The edited property set has the corresponding bit enabled 
   * 
   * @param string $property Property to set
   * @param mixed $value property value
   * @throws InvalidArgumentException if the property is not a member of this model
   * @throws ValidationException if value is invalid 
   */
  public function setValue( string $property, $value ) : void
  {    
    //..Edits for a child model by prefix (see other comments in constructor and setValue)
    $res = $this->getPropertyByPrefix( $property );
    if ( !empty( $res ))
    {
      //..prop is the parent property containing the object that has the property $newProperty 
      list( $prop, $newProperty ) = $res;
      
      if ( !$this->properties->isMember( $prop ))
        return;
      
      //..Get the backing object property 
      $newProp = $this->getProperty( $prop );
      
      //..Get the backing object itself 
      //..No issues, set the sub-object property value 
      $this->getValue( $prop )->$newProperty = $value;
      
      
      //..Set this property to edited 
      //if ( !$newProp->getFlags()->hasVal( IPropertyFlags::NO_INSERT ) && !$newProp->getFlags()->hasVal( IPropertyFlags::NO_UPDATE ))
      //  $this->edited->add( $prop );   
      
      return;
    }
    
    if ( !isset( $this->memberCache[$property] ))
      $this->memberCache[$property] = ( $this->properties->isMember( $property )) ? $this->getProperty( $property ) : null;
    
    //if ( !$this->properties->isMember( $property ))
    $prop = $this->memberCache[$property];
    if ( $prop === null )
    {
      
      $this->extraData[$property] = $value;
      return;
    }
    
    //..Get the property entity.  This also checks membership and throws an exception.
    //$prop = $this->getProperty( $property );
    
    if ( $prop->getFlags()->hasVal( IPropertyFlags::PRIMARY ) && !empty( $prop->getValue()))
    {
      throw new ValidationException( 'Primary key may not be changed after instantiation' );
    }
      
    
    foreach( $prop->getPropertyBehavior() as $b )
    {
      /* @var $b IPropertyBehavior */
      $msetter = $b->getModelSetterCallback();

      if ( $msetter instanceof Closure )
      {
        $value = $msetter( $this, $prop, $value );
      }
    }
    
    //..Set the value 
    $prop->setValue( $value );
    
    /*
    //..Set this property to edited 
    if ( !$prop->getFlags()->hasVal( IPropertyFlags::NO_INSERT ) && !$prop->getFlags()->hasVal( IPropertyFlags::NO_UPDATE ))
    {
      try {
        $this->edited->add( $property );
      } catch( \InvalidArgumentException $e ) {
        return;        
      }
    }
    */
  }
    
  
  /**
   * Retrieve a list of modified properties 
   * @return IPropertySet modified properties 
   * @final 
   */
  public function getModifiedProperties() : IBigSet
  {
    $out = $this->getPropertyNameSet();
    $out->clear();
    
    //$out->add( ...$this->edited->getActiveMembers());

    
    foreach( $this->properties->getProperties() as $p )
    {
      /* @var $p IProperty */
      
      
      if ( $p->isEdited())
        $out->add( $p->getName());
      
      /*
      //..This is a messed up hack to ensure that changes to enum and set 
      //  properties are properly committed...
      //..This should be implemented as part of some on change event in the enum/set object
      //..and the associated properties.
      if ( $p->getType()->value() == IPropertyType::TENUM 
        || $p->getType()->value() == IPropertyType::TSET )
      {
        $out->add( $p->getName());
      }
      else if ( $p->getType()->value() == IPropertyType::TMODEL )
      {
        $model = $this->getValue( $p->getName());
        if ( $model != null && $model->hasEdits())
        {
          $out->add( $p->getName());
        }
      }
      */
    }

    
    //..Any non-scalar, non-array properties have potentially been modified 
    //  outside of DefaultModel::setValue().  This means that the edited array
    //  may not include the property names even though they were edited.
    //..This isn't the best way to do this.  There should be change events 
    //..and maybe require all IObjectProperty values to implement that change interface.
    
    foreach( $out->getActiveMembers() as $name )
    {
      $val = $this->getValue( $name );
      if ( !is_null( $val ) && !is_scalar( $val ))
      {
        $out->add( $name );
      }
    }
    
    
    return $out;
  }
  
  
  /**
   * Detect if any properties have been edited in this model 
   * @param string $prop Property name.  If $prop is not empty then this would test that the supplied property name is not empty. 
   * Otherwise, this tests if any property was edited.
   * @return bool has edits
   * 
   * @todo This method is too complex. Consider adding an isEdited property to IProperty instead of using all of this type checking.
   */
  public function hasEdits( string $prop = '' ) : bool 
  {
    foreach( $this->properties->getProperties() as $prop )
    {
      if ( $prop->isEdited())
        return true;
    }
    
    return false;
    
    /*
    
    if ( $this->testEdited( $prop ))
    {
      return true;
    }
    
    //..I didn't want to create a potentially invalid IPropertyType instance.
    foreach( $this->properties->getProperties() as $prop )
    {
      if ( $prop->getType()->value() == IPropertyType::TMODEL ) 
      {
        $model = $this->getValue( $prop->getName());
        
        if (( $model instanceof IModel ) && $model->hasEdits())
        {
          return true;
        }
      }
      else if ( $prop->getType()->value() == IPropertyType::TARRAY )
      {
        foreach( $prop->getValue() as $val ) //..Going to bypass model getters.  Nasty loops can happen
        {
          if ( $val instanceof IModel )
          {
            if ( $val->hasEdits())
            {
              return true;
            }
          }
          else
          {
            //..Not an array of IModel 
            break;
          }
        }
        
      }
    }
    
    return false;
   */
  }
  
  /*
  private function testEdited( string $prop = '' ) : bool
  {
    if ( !empty( $prop ))
    {
      return $this->edited->hasVal( $prop );
    }
    else
    {
      return !$this->edited->isEmpty();    
    }
  }
  */
  
  
  /**
   * Clears the internal edited flags for each property
   * @return void
   */
  public function clearEditFlags() : void
  {
    //..Not sure this is necessary
    foreach( $this->prefixMap as $props )
    {
      foreach( $props as $data )
      {
        list( $prefix, $name ) = $data;
        $prop = $this->getProperty( $name );
        $prop->clearEditFlag();
        //$val = $prop->getValue(); //..calling getValue will set the edited flag 
        //if ( $val instanceof IModel )
       // {
       //   $val->clearEditFlags();
       // }
      }
    }
    
    
    //..This part is necessary
    foreach( $this->properties->getProperties() as $prop )
    {
      /* @var $prop IProperty */
      $prop->clearEditFlag();
    }
  }
  
  
  /**
   * Retrieve the property set used for this model.
   * 
   * @reutrn IPropertySet properties
   * @final 
   */
  public final function getPropertySet() : IPropertySet
  {
    return $this->properties;
  }
  
  
  /**
   * Retrieve property names as an IBigSet instance.
   * This will return a set containing all of the property names, and have 
   * zero members active.  
   * 
   * This returns a cloned instance.
   * 
   * @return IBigSet property names 
   */
  public function getPropertyNameSet() : IBigSet
  {
    //..This may be better if it always clones.
    //..Adding note for now.
    if ( $this->cloneNames == null )
      $this->cloneNames = clone $this->edited;
    
    $this->cloneNames->clear();
    return $this->cloneNames;
  }
  
  
  /**
   * Retrieve a set with the property name bits toggled on for properties
   * with the supplied flags.
   * @param string $flags Flags 
   * @return IBigSet names 
   */
  public function getPropertyNameSetByFlags( string ...$flags ) : IBigSet
  {
    $names = clone $this->edited;
    $names->clear();
    foreach( $this->properties->getPropertiesByFlag( ...$flags ) as $p )
    {
      if ( $names->isMember( $p->getName()))
        $names->add( $p->getName());
    }
    
    return $names;
  }

  
  private function arrayToObject( array &$a ) 
  {
    //..object output
    $out = new stdClass();
    
    //..array output
    $aOut = [];
    
    //..Loop elements
    foreach( $a as $k => &$v )
    {
      //..If this is an int, then this is an array, not an object
      if ( is_int( $k ))
      {
        //..This is an array index 
        //..If value is an array, map those elements too
        //..Add to array output 
        if ( is_array( $v ))
          $aOut[$k] = $this->arrayToObject( $v );
        else
          $aOut[$k] = $v;
      }
      else if ( is_array( $v ))
      {
        //..If value is an array, map those elements too
        if ( empty( $v ))
          $out->{$k} = [];
        else
          $out->{$k} = $this->arrayToObject( $v );
      }
      else
      {
        //..Map the value 
        $out->{$k} = $v;
      }
    }
    
    
    //..Check object output for properties 
    $outEmpty = true;
    foreach( $out as $v )
    {
      $outEmpty = false;
      break;
    }
    
    //..Check for mixed object and array properties
    if ( !empty( $aOut ) && !$outEmpty )
    {      
      //..There are mixed property types.  Create a property called "array"
      //  on the object output, and set the value equal to the array output.
      $out->array = $aOut;
      return $out;
    }
    else if ( !empty( $aOut ))
    {
      //..Return array output because there is no object output
      return $aOut;
    }
        
    //..No array output, so return the object 
    return $out;
  }
  
  
  /**
   * Sets properties from some array.
   * Invalid properties are quietly ignored.
   * @param array $data data to set 
   * @return void
   */
  public function fromArray( array $data ) : void
  {
    $pk = $this->properties->getPrimaryKeyNames();
    foreach( $data as $prop => $value )
    {
      if ( $this->properties->isMember( $prop ) && !in_array( $prop, $pk ))
        $this->setValue( $prop, $value );        
    }
  }
  
  
  /**
   * Convert the model to a json representation.
   * This probably does not work with object properties.  Need to test.
   * @return string JSON object 
   */
  public function toObject( ?IBigSet $properties = null, bool $includeArrays = false, bool $includeModels = false, bool $includeExtra = false ) : stdClass
  {
    $a = $this->toArray( $properties, $includeArrays, $includeModels, $includeExtra );
    return $this->arrayToObject( $a );
  }
  
  
  /**
   * Convert this model to an array.
   * @param IPropertySet $properties Properties to include 
   */
  public function toArray( ?IBigSet $properties = null, bool $includeArrays = false, bool $includeModels = false, bool $includeExtra = false, int $_depth = 0 ) : array
  {    
    $out = [];
    
    if ( $properties == null )
    {
      $members = $this->properties->getMembers();
    }
    else
    {
      $members = $properties->getActiveMembers();    
    }
    
    
    foreach( $members as $p )
    {
      try {
        $prop = $this->getProperty( $p );
        $prop->getPropertyBehavior();
      } catch( \InvalidArgumentException $e ) {
        //..Just skip it.  These are either orphaned attributes or attributes without values.
        /**
         * @todo Figure out how the property sets could differ.  This probably shouldn't be happening.  Too much code!
         */
        continue;
      }
      
      if ( $prop->getFlags()->hasVal( IPropertyFlags::NO_ARRAY_OUTPUT ))
      {
        continue;
      }
      
      //..This logic blows, but it's late and I'll fix it later.
      $prefix = $prop->getPrefix();
      
      if ( empty( $prefix ) && !$includeModels && $prop->getType()->is( IPropertyType::TMODEL, IPropertyType::TOBJECT ))
      {
        //..Without this, extra db queries can happen.
        continue;
      }
      else if ( empty( $prefix ) && !$includeArrays && $prop->getType()->is( IPropertyType::TARRAY ))
      {
        //..No arrays.
        continue;
      }        
      
      //..getValue() must be used instead of IProperty::__toString() due to model-level getter and setters.
      $val = $this->getValue( $p );
      
      if ( $_depth < $this->maxDepth && !empty( $prefix ) && ( $val instanceof IModel ))
      {
        foreach( $val->toArray( null, $includeArrays, $includeModels, $includeExtra, $_depth + 1 ) as $k => $v )
        {
          $out[$prefix . $k] = $v;
        }
        
        
        continue;
      }      
      
      
      if ( is_array( $val ))
      {
        if ( $includeArrays )
        {
          $a = [];
          foreach( $val as $k => $v )
          {
            if ( $_depth < $this->maxDepth && ( $v instanceof IModel ) && $v !== $this )
            {
              $a[$k] = $v->toArray( null, $includeArrays, $includeModels, $includeExtra, $_depth + 1 );
            }
            else if (( $v instanceof IModel ) && $v === $this )
              $a[$k] = 'this';
            else
              $a[$k] = $v;
          }
          
          $out[$prop->getName()] = $this->modifyToArrayValue( $prop, $a );
        }
        //..Don't want to output invalid column names...
        //else 
          //$out[$prop->getName()] = $prop->__toString();
      }
      else if ( empty( $prefix ) && ( $val instanceof IModel ))
      {
        if ( $_depth < $this->maxDepth && $includeModels )
          $out[$prop->getName()] = $val->toArray( null, $includeArrays, $includeModels, $includeExtra, $_depth + 1 );
      }
      else 
      {
        
        //..Modify for toArray
        $val = $this->modifyToArrayValue( $prop, $val );
        
        //..Casting the property itself to a string bypasses model level 
        //  getter/setter methods attached to the property set.  
        //  That is no good, so we're using the value cast to a string.  Should be fine.
        
        //..This date time shit seems a lil stinky to me.
        //..Forcing the choice to UTC might not be the best choice
        //..Testing property types is generally a no-no.
        //..The reason for this is due to how prop->__toString() is no longer 
        //..A reliable way to get the value as a string.  There are too many behavior options.
        //..Consider changing DateTimeWrapper::__toString() to simply output a sql timestamp        
        if ( $val instanceof IDateTime )
        {
          //..We want the timezone and stuff as separate properties when dumping to an array
          //..The pdo library has code to handle IDateTimeInterface
          $out[$prop->getName()] = $val->getUTC();
        }
        else if ( $prop->getType()->value() == IPropertyType::TDATE )
        {
          //..Special handling for null dates.
          $out[$prop->getName()] = null;
        }
        else if ( is_bool( $val ))
        {
          $out[$prop->getName()] = ( $val ) ? 1 : 0;
        }
        else if ( $prop->getFlags()->hasVal( IPropertyFlags::USE_NULL ) && is_null( $val ))
        {
          $out[$prop->getName()] = null;
        }
        else if ( is_null( $val ))
        {
          //..Use default instead of null (null is likely due to some left join, etc) since null is not allowed for this property.
          $out[$prop->getName()] = $prop->getDefaultValue();
        }
        else if ( is_scalar( $val ))
        {
          $out[$prop->getName()] = $val;
        }
        else 
        {
          $out[$prop->getName()] = (string)$val; //$prop->__toString();//(string)$prop;
        }
      }
    }
    
    //..Used for properties outside of the main property set 
    //..For joined tables, etc, this may be used to provide those values.
    //..This could be dangerous
    /**
     * @todo Consider validating these properties against model/array property sets embedded within this model.
     */
    if ( $includeExtra )
    {      
      foreach( $this->extraData as $k => $v )
      {
        $out[$k] = $v;
      }
    }
    
    return $out;
  }
  
  
  /**
   * Test if this model is equal to some other model
   * @param IModel $that model to compare
   * @return bool is equals
   */
  public function equals( IModel $that ) : bool
  {
    //..Check that the objects are of the same type and have the same properties 
    if ( $this->hash() != $that->hash() 
      || !$this->properties->equals( $that->getPropertySet()))
    {
      return false;
    }
    
    //..Check if the primary keys match 
    foreach( $this->getPropertySet()->getPrimaryKeyNames() as $pk )
    {
      $pk1 = $this->getValue( $pk );
      try {
        $pk2 = $that->getValue( $pk );
        if ( $pk1 != $pk2 )
          return false;
      } catch (Exception $ex) {
        return false;
      }
    }
    
    if ( !empty( $pk1 ))
    {
      //..Primary keys match and have been persisted 
      return true;
    }
    
        
    //..These are new models that have not been persisted.  Check if the properties are equal.
    //..Note: this may trigger relationship providers, which cause db queries.  
    //..Note2: arrays and object properties are run through json_encode.
    //..I'm trying to say this might be slow.  
    foreach( $this->getPropertySet()->getProperties() as $p1 )
    {
      try {
        $p2 = $that->getPropertySet()->getProperty( $p2 );
        
        if (( is_array( $p1 ) || is_object( $p1 ))
          && json_encode( $p1 ) != json_encode( $p2 ))
        {
          return false;
        }
          
        if ( $p1->getValue() != $p2->getValue())
          return false;
      } catch (Exception $ex) {
        return false;
      }
    }
    
    return true;
  }
  
  
  /**
   * Test to see if this model is valid prior to save().
   * There is an order of operations to validate.
   * It starts at the top of the list of properties and works its way down the list.  The first property listed in 
   * the property set is the first property to be validated.
   * When nesting models, validation will walk down the tree and return to the parent to continue processing the list 
   * of properties.
   * 
   * Model (behaviors) are validated last.  
   * 
   * 
   * 
   * @throws ValidationException 
   */
  public function validate() : void
  {
    if ( !$this->validationEnabled )
      return;
    
    $this->beforeValidate();
    
    $this->validateProperties();
    
    foreach( $this->properties->getModelValidationArray() as $f )
    {
      $f( $this );
    }
  }
  
  
  /**
   * Validation without exceptions.  This will
   * simply call validate() and return false if validate() throws an exception.
   * @return bool is valid
   */
  public function isValid() : bool
  {
    try {
      $this->validate();
    } catch (Exception $ex) {
      return false;
    }
    
    return true;
  }
  
  
  
  private function validateProperties( bool $throw = true ) : array 
  {
    $out = [];
    foreach( $this->properties->getProperties() as $prop )
    {      
      /* @var $prop IProperty */
      $value = $prop->getValue();
      if ( $prop->getFlags()->hasVal( IPropertyFlags::REQUIRED ) && !$prop->getFlags()->hasVal( IPropertyFlags::PRIMARY ) && (( empty( $value ) && $value !== false ) || $value === '0000-00-00 00:00:00' ))
      {       
        $message = '"' . $prop->getName() . '" property of class "' . static::class . '" of type "' . $prop->getType() .'" is REQUIRED and must not be empty.';
        if ( $throw )
          throw new ValidationException( $message );
        else
          $out[$prop->getName()] = $message;
      }
      
      try {
        $prop->validate( $this->getValue( $prop->getName()));
      } catch( ValidationException $e ) {
        if ( $throw )
          throw $e;
        else
          $out[$prop->getName()] = $e->getMessage();
      }
    }    
    
    return $out;
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
    $out = [];
   
    $this->beforeValidate();
    $errors = $this->validateProperties( false );
    
    try {
      foreach( $this->properties->getModelValidationArray() as $f )
      {
        $f( $this );
      }    
    } catch( ValidationException $e ) {
      $errors[] = $e->getMessage();
    }
    
    return $out;
  }
  
  
  
  /**
   * Retrieve a unique hash for this object 
   * @return string hash
   */
  public function hash() : string
  {
    return md5(
        get_class( $this )
      . get_class( $this->properties )
      . implode( '', $this->properties->getMembers())
    );
  }
  
  
  /**
   * Gets A propertyset containing properties for insert
   * @return IBigSet insert properties
   */
  public function getInsertProperties() : IBigSet 
  {
    $out = $this->getPropertyNameSet();
    
    foreach( $this->properties->getProperties() as $prop )
    {
      if ( !$prop->getFlags()->hasVal( IPropertyFlags::NO_INSERT ))
      {
        $out->add( $prop->getName());
      }
    }
    
    return $out;    
  }
  
  
  /**
   * Specify data which should be serialized to JSON
   * <p>Serializes the object to a value that can be serialized natively by <code>json_encode()</code>.</p>
   * @return mixed <p>Returns data which can be serialized by <code>json_encode()</code>, which is a value of any type other than a resource.</p>
   * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
   * @since PHP 5 >= 5.4.0, PHP 7
   */
  public function jsonSerialize()
  {
    return $this->toObject( null, false, false );
  }
  
  
  /**
   * Called prior to the validation loop
   */
  protected function beforeValidate()
  {
    //..do nothing
  }
  
  
  /**
   * Checks to see if some property is a member of the property set 
   * @param IProperty $p Property 
   * @see IPropertySet::isMember()
   */
  private function isMember( string $p ) : bool
  {
    return $this->properties->isMember( $p );
  }
  
  
  /**
   * Retrieve a prop.
   * If the property name is not a member of the listed IPropertySet instance, 
   * an InvalidArgumentExcepiton is thrown.  Otherwise 
   * IPropertySet::getProperty( $property ) is returned.
   * 
   * @param string $property Property name
   * @return IProperty property requested property object 
   * @throws InvalidArgumentException if property is not a valid member 
   * @see IPropertySet::getMember()
   * @see IPropertySet::getProperty()
   */
  private function getProperty( string $property ) : IProperty
  {
    if ( !$this->isMember( $property ))
    {
      throw new InvalidArgumentException( $property . ' is not a property of ' . static::class );
    }
    
    return $this->properties->getProperty( $property );
  }  
  
  
  
  /**
   * If a prefix is used on some object type, this will return the 
   * @param string $inp
   * @return string
   */
  private function getPropertyByPrefix( string $inp ) : array
  {
    if ( empty( $inp ) || empty( $this->prefixMap ))
      return [];
    
    
    
    $inpLen = strlen( $inp );
    
    foreach( $this->prefixMap as $len => $data )
    {
      foreach( $data as $entry )
      {
        if ( $len <= $inpLen && $inp != $entry[0] && strpos( $inp, $entry[0] ) !== false )
        {
          return [$entry[1], substr( $inp, $len )];
        }
      }
    }

    return [];
  }  
  
  
  private function getPrimaryKeyMap() : array
  {
    $out = [];
    foreach( $this->properties->getPrimaryKeys() as $key )
    {
      /* @var $key IProperty */
      $out[$key->getName()] = $key->getValue();
    }
    
    return $out;
  }
  
  
  /**
   * Test that the model has all primary key values 
   * @return bool has values 
   */
  public function hasPrimaryKeyValues() : bool
  {
    $priKeys = $this->getPrimaryKeyMap();
    if ( empty( $priKeys ))
      return true;
    
    foreach( $priKeys as $v )
    {
      if ( empty( $v ))
        return false;
    }
    
    return true;
  }
  
  
  /**
   * Called from setValue(), and used when the property has a prefix value set.
   * If the WRITE_NEW flag is set, then the The sub-model primary key properties
   * are tested for empty.  If not empty, then this throws a validation exception.
   * 
   * @param IModel $o
   * @param string $newProperty
   * @throws ValidationException
   */
  private function testPrefixModelWriteNewFlag( IModel $o, string $newProperty )
  {
    if (( $o instanceof IModel ) && $o->getPropertySet()->getProperty( $newProperty )->getFlags()->hasVal( IPropertyFlags::WRITE_NEW ))
    {
      foreach( $o->getPropertySet()->getPrimaryKeys() as $key )
      {
        /* @var $key IProperty */
        if ( empty( $key->getValue()))
          throw new ValidationException( 'Model ' . get_class( $o ) . '::' . $newProperty . ' cannot be written to due to the parent model having the IPropertyFlags::WRITE_NEW flag set.' );
      }
    }    
  }  
  
  
  /**
   * Used with toArray.  Modifies the value to be output via toArray property behavior callbacks.
   * @param IProperty $prop
   * @param type $value
   */
  private function modifyToArrayValue( IProperty $prop, $value ) 
  {
    foreach( $prop->getPropertyBehavior() as $b )
    {
      /* @var $b IPropertyBehavior */
      $f = $b->getToArrayCallback();
      if ( $f instanceof Closure )
      {
        $value = $f( $this, $prop, $value );
      }
    }
    
    return $value;
  }
  
}
