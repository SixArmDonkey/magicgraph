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


/**
 * A property that is backed by some object
 * Object properties cannot list a default value in the supplied builder instance.
 * default value is set to a new instance of the class described by IObjectPropertyBuilder::getClass()
 * To manipulate the default value, define a behavior callback to be returned by IPropertyBehavior::getInitCallback()
 */
class ObjectProperty extends AbstractProperty implements IObjectProperty
{
  /**
   * Class name 
   * @var string
   */
  private $clazz;
  
  /**
   * Optional callback for creating instances of $clazz.
   * f( string $clazz ) : instance of $clazz 
   * @var Closure
   */
  private $createClass;
  
  
  /**
   * Create a new ObjectProperty instance 
   * @param IObjectPropertyBuilder $builder Builder 
   */
  public function __construct( IObjectPropertyBuilder $builder )
  {
    parent::__construct( $builder );
    $this->clazz = $builder->getClass();    
    $this->createClass = $builder->getCreateClassClosure();
    
    if ( empty( $this->clazz ))
      throw new InvalidArgumentException( "IObjectPropertyBuilder::getClass() must return a non-empty string that represents a fully qualified class name for property " . $builder->getName());
  }
  
  
  /**
   * Retrieve the class or interface name of the stored object instance.
   * @return string class name
   * @final 
   */
  public final function getClass() : string
  {
    return $this->clazz;
  }
  
  
  /**
   * Initialize the value property with some value.
   * This will be immediately overwritten by the initial call to reset(), but 
   * is useful for when value is some object type that must not be null. 
   * 
   * Returns null by default.
   * 
   * @return mixed value 
   * @throws \Exception 
   */
  protected function initValue()
  {
    if ( $this->createClass instanceof Closure )
    {
      $c = $this->createClass;
      try {
        $instance = $c( $this->clazz );
        if ( $instance == null )
        {
          //..Simply try to create it 
          
          //..If clazz is an interface, then always return null since we can't instantiate an interface.
          if ( interface_exists( $this->clazz ))
            return null;
          
          //..Try for a default constructor
          $c = $this->clazz;
          
          return new $c();
        }
      } catch( \Exception $e ) {
        throw new \Exception( $e->getMessage() . ' for property ' . $this->getName(), 0, $e );
      }
      
      
      
      if ( !is_a( $instance, $this->clazz ))
        throw new \Exception( 'Property backing object must be an instance of ' . $this->clazz . ' for property ' . $this->getName());
      
      return $instance;
    }
    else if ( $this->isUseNull())
    {
      return null;
    }
    else
    {
      try {
        $c = $this->clazz;
        return new $c();
      } catch( \Error $e ) {
        throw new \Exception( 'Failed to create instance of ' . $this->clazz . '.  This is most likely because the model requires constructor arguments.  Try adding "null" to the property flags array for the property "' . $this->getName() . '"', $e->getCode(), $e );
      }
    }
  }  
  
  
  protected function validatePropertyValue( $value ) : void
  {
    if ( $this->getFlags()->hasVal( IPropertyFlags::USE_NULL ) && $value === null )
      return;
    
    if ( empty( $value ) || !is_a( $value, $this->getClass()))
      throw new ValidationException( sprintf( 'Value "%s" for property %s must be an instance of %s Got %s %s', (string)$value, $this->getName(), $this->getClass(), gettype( $value ), ( is_object( $value )) ? ' of class ' . get_class( $value ) : '' ));
  }  
}
