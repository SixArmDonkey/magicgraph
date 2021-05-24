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

namespace buffalokiwi\magicgraph\property\htmlproperty;

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;


/**
 * The element factory creates html elements based on IProperty instances 
 */
class ElementFactory implements IElementFactory
{
  /**
   * Suppliers
   * @var IElementFactoryComponent[] 
   */
  private array $suppliers;
  
  
  /**
   * 
   * @param IElementFactoryComponent $suppliers A list of IElement suppliers.
   */
  public function __construct( IElementFactoryComponent ...$suppliers )
  {
    foreach( $suppliers as $s )
    {
      $this->suppliers[$s->getInterface()] = $s;
    }
  }
  
  
  /**
   * Create an HTML Element from a property.
   * @param IModel Model used when creating inputs.  This can be an empty model if you want.  It depends on the model implementation.  
   * So, be careful, I guess?
   * @param IProperty $property Property 
   * @param string $name Element name attribute value 
   * @param string $id Element Id attribute value 
   * @param string $value Property value from model getter.
   * @return IElement Element 
   * @throws HTMLPropertyException If a matching supplier does not exist.
   */
  public function createElement( IModel $model, IProperty $property, string $name, ?string $id, $value ) : IElement
  {
        
    $cb = [];
    foreach ( $property->getPropertyBehavior() as $b )
    {
      /* @var $b \buffalokiwi\magicgraph\property\IPropertyBehavior */
      if ( $b->getHTMLInputCallback() != null )
      {
        $cb[] = $b->getHTMLInputCallback();
      }
    }
    
    if ( !empty( $cb ))
    {
      foreach( $cb as $f )
      {
        $value = $f( $model, $property, $name, $id, $value );
      }
      
      if ( $value instanceof IElement )
        return $value;
    }
    
    
    foreach( $this->suppliers as $intf => $supplier )
    {
      if ( is_a( $property, $intf ))
      {        
        return $supplier->createElement( $property, $name, $id, $value );
      }
    }
    
    throw new HTMLPropertyException( $property->getName() . ' does not have a corresponding IElement supplier.' );
  }
  
  
  /**
   * For a given model, generate a series of HTML form inputs.
   * @param IModel $model
   * @param array $attrs additional attributes
   * @return array Configuration data for an animator/renderer.
   * [
   *   'name' => [
   *     'for' => "Property Name"
   *     '' => "Property Caption"
   *   ],
   *   'html' => "The html element code"
   * ]
   */
  public function createFormInputs( IModel $model, array $attrs = [] ) : array
  {
    $elements = ['General' => []];
    $ps = $model->getPropertySet();
    
    $hasPri = true;
    foreach( $model->getPropertySet()->getPrimaryKeys() as $key )
    {
      if ( empty( $model->getValue( $key->getName())))
      {
        $hasPri = false;
        break;
      }
    }
    
    
    $groupMap = [];
    
    foreach( $model->getPropertySet()->getProperties() as $prop )
    {
      /* @var $prop IProperty */
      $name = $prop->getName();
      
      $tag = ( empty( $prop->getTag())) ? 'General' : $prop->getTag();
      if ( !isset( $elements[$tag] ))
        $elements[$tag] = [];
      
      $groupMap[$name] = $tag;
      
      if ( !isset( $attrs[$name] ))
      {
        $c = $prop->getCaption();
        $val = $model->getValue( $name );
        
        if ( is_scalar( $val ))
          $val = (string)$val;
        else
          $val = '__ADD__';
        
        $attrs[$name] = [ucfirst((empty( $c )) ? $name : $c ), $val];
      }
    }
    
    ksort( $attrs );
    
    foreach( $attrs as $name => $data )
    {
      $value = $data[1];
      $prop = $ps->getProperty( $name );
  
      if ( $prop->getFlags()->hasAny( IPropertyFlags::PRIMARY, IPropertyFlags::NO_INSERT ))
        continue;
      else if ( $hasPri && !empty( $value ) && $prop->getFlags()->hasVal( IPropertyFlags::NO_UPDATE ))
      {
        continue;
      }
      else if ( $prop->getType()->is( IPropertyType::TMODEL, IPropertyType::TOBJECT ))
        continue;
      
      if ( $value == '__ADD__' )
      {
        $value = $model->getValue( $name );
      }
      
      if ( !is_array( $value ))
        $value = (string)$value;
      
      try {        
        $elements[$groupMap[$name]][] = [
          'name' => [
            'for' => $name,
            '' => $data[0]
          ],
          'html' => $this->createElement( $model, $prop, $name, '', $value )->build()
        ];
      } catch( HTMLPropertyException $e ) {
        trigger_error( $e->getMessage(), E_USER_WARNING );
        trigger_error( $e->getTraceAsString(), E_USER_WARNING );
      }
    }

    return $elements;  
  }  
  
  
  /**
   * Convert properties to html form inputs 
   * @param IModel $model model 
   * @param IProperty $properties properties to convert 
   * @return array Configuration data for an animator/renderer.
   * [
   *   'name' => [
   *     'for' => "Property Name"
   *     '' => "Property Caption"
   *   ],
   *   'html' => "The html element code"
   * ]
   */
  public function propertiesToFormInputs( IModel $model, IProperty ...$properties ) : array
  {
    $elements = ['General' => []];
    $ps = $model->getPropertySet();
    
    $hasPri = true;
    foreach( $model->getPropertySet()->getPrimaryKeys() as $key )
    {
      if ( empty( $model->getValue( $key->getName())))
      {
        $hasPri = false;
        break;
      }
    }
    
    
    $attrs = [];
    $groupMap = [];
    
    foreach( $properties as $prop )
    {
      /* @var $prop IProperty */
      $name = $prop->getName();
      
      $tag = ( empty( $prop->getTag())) ? 'General' : $prop->getTag();
      if ( !isset( $elements[$tag] ))
        $elements[$tag] = [];
      
      $groupMap[$name] = $tag;
      
      if ( !isset( $attrs[$name] ))
      {
        $c = $prop->getCaption();
        $val = $model->getValue( $name );
        
        if ( is_scalar( $val ))
          $val = (string)$val;
        else
          $val = '__ADD__';
        
        $attrs[$name] = [ucfirst((empty( $c )) ? $name : $c ), $val];
      }
    }
    
    ksort( $attrs );
    
    foreach( $attrs as $name => $data )
    {
      $value = $data[1];
      $prop = $ps->getProperty( $name );
  
      if ( $prop->getFlags()->hasAny( IPropertyFlags::PRIMARY, IPropertyFlags::NO_INSERT ))
        continue;
      else if ( $hasPri && !empty( $value ) && $prop->getFlags()->hasVal( IPropertyFlags::NO_UPDATE ))
      {
        continue;
      }
      else if ( $prop->getType()->is( IPropertyType::TMODEL, IPropertyType::TOBJECT ))
        continue;
      
      if ( $value == '__ADD__' )
      {
        $value = $model->getValue( $name );
      }
      
      if ( !is_array( $value ))
        $value = (string)$value;
      
      try {        
        $elements[$groupMap[$name]][] = [
          'name' => [
            'for' => $name,
            '' => $data[0]
          ],
          'html' => $this->createElement( $model, $prop, $name, '', $value )->build()
        ];
      } catch( HTMLPropertyException $e ) {
        trigger_error( $e->getMessage(), E_USER_WARNING );
        trigger_error( $e->getTraceAsString(), E_USER_WARNING );
      }
    }

    return $elements;      
  }  
}
