<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */

declare( strict_types=1 );

namespace buffalokiwi\magicgraph\property\htmlproperty;

use buffalokiwi\magicgraph\property\IBooleanProperty;
use buffalokiwi\magicgraph\property\IDateProperty;
use buffalokiwi\magicgraph\property\IEnumProperty;
use buffalokiwi\magicgraph\property\IFloatProperty;
use buffalokiwi\magicgraph\property\IIntegerProperty;
use buffalokiwi\magicgraph\property\IMoneyProperty;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\ISetProperty;
use buffalokiwi\magicgraph\property\IStringProperty;
use InvalidArgumentException;


/**
 * A default set of IElement factories used to create IElementFactoryComponent instances used within the ElementFactory.
 * 
 * ie: Pass ( new DefaultComponentMap())->getMap() to ElementFactory::__construct() 
 * 
 * This is simply a default setup object for the html element generators.
 */
class DefaultComponentMap
{
  private array $map;
  
  public function __construct( array $map = [] )
  {
    $this->map = array_merge([
      IBooleanProperty::class => function( IBooleanProperty $prop, string $name, ?string $id, string $value ) : IElement {
        $attrs = [];
        if ( in_array( strtolower( $value ), ['1','true'] ))
          $attrs['checked'] = 'checked';
      
        return new FancyCheckboxElement( $name, $id ?? '', '', $attrs );
      },
      
      IDateProperty::class => function( IProperty $prop, string $name, ?string $id, string $value ) : IElement {
        $attrs = [];
        if ( $prop->getFlags()->hasVal( IPropertyFlags::REQUIRED ))
          $attrs['required'] = 'required';
        
        return new InputElement( 'date', $name, $id ?? '', $value, $attrs );
      },
              
      IEnumProperty::class => function( IEnumProperty $prop, string $name, ?string $id, string $value ) : IElement {
        $enum = $prop->getValueAsEnum();
        if ( !empty( $value ) && !$enum->isValid( $value ))
          throw new InvalidArgumentException( 'Invalid enum value' );
        
        $stored = $enum->getStoredValues();
        $options = [];
        foreach( $enum->getEnumValues() as $ev )
        {
          $options[$ev] = ( isset( $stored[$ev] )) ? $stored[$ev] : ucfirst( $ev );
        }
        
        $attrs = [];
        if ( $prop->getFlags()->hasVal( IPropertyFlags::REQUIRED ))
          $attrs['required'] = 'required';
        
        return new SelectElement( $name, $id ?? '', $value, $options, $attrs );
      },


      ISetProperty::class => function( ISetProperty $prop, string $name, ?string $id, $value ) : IElement {
        $set = $prop->getValueAsSet();
        
        if ( empty( $value ))
          $value = [];        
        else if ( !is_array( $value ))
          $value = explode( ',', trim( $value ));
        
        if ( !empty( $value ) && !$set->isMember( ...$value ))
          throw new InvalidArgumentException( 'Invalid set value' );
        
        $options = [];
        foreach( $set->getMembers() as $member )
        {
          $options[$member] = ucfirst( $member );
        }
        
        $attrs = [
          'multiple' => 'multiple',
          'size' => 10            
        ];
        
        if ( $prop->getFlags()->hasVal( IPropertyFlags::REQUIRED ))
          $attrs['required'] = 'required';
        

        
        return new SelectElement( $name . '[]', $id ?? '', implode(',', $value), $options, $attrs );
      },
                    
      
      IFloatProperty::class => function( IFloatProperty $prop, string $name, ?string $id, string $value ) : IElement {
        if ( !is_numeric( $value ))
          throw new InvalidArgumentException( 'value must be numeric' );
        
        $s = strrchr( $value, '.' );
        if ( $s !== false )
          $step = strlen( substr( $s , 1 ));
        else
          $step = 0.01;
        
        $attrs = [
          'step' => ( empty( $step )) ? 'any' : $step
        ];
        
        
        if ( $prop->getMin() > -2147483647 )
          $attrs['min'] = $prop->getMin();
        
        if ( $prop->getMax() < 2147483647 )
          $attrs['max'] = $prop->getMax();
        
        if ( $prop->getFlags()->hasVal( IPropertyFlags::REQUIRED ))
          $attrs['required'] = 'required';
        
        return new InputElement( 'number', $name, $id ?? '', $value, $attrs );
      },
              
      IIntegerProperty::class => function( IIntegerProperty $prop, string $name, ?string $id, string $value ) : IElement {
        if ( !empty( $value ) && !is_numeric( $value ))
          throw new InvalidArgumentException( 'value must be numeric' );
        
        
        $attrs = [
          'step' => '1'
        ];
        
        
        if ( $prop->getMin() > -2147483647 )
          $attrs['min'] = $prop->getMin();
        
        if ( $prop->getMax() < 2147483647 )
          $attrs['max'] = $prop->getMax();
        
        if ( $prop->getFlags()->hasVal( IPropertyFlags::REQUIRED ))
          $attrs['required'] = 'required';
        
        return new InputElement( 'number', $name, $id ?? '', $value, $attrs );
      },
              
      IMoneyProperty::class => function( IMoneyProperty $prop, string $name, ?string $id, string $value ) : IElement {
        $value = str_replace( '$', '', $value );
        
        if ( !empty( $value ) && !is_numeric( $value ))
        {
          throw new InvalidArgumentException( 'value must be numeric' );
        }
        
        $attrs = [
          'step' => '0.001'
        ];
        
        
        if ( $prop->getMin() > -2147483647 )
          $attrs['min'] = $prop->getMin();
        
        if ( $prop->getMax() < 2147483647 )
          $attrs['max'] = $prop->getMax();

        
        if ( $prop->getFlags()->hasVal( IPropertyFlags::REQUIRED ))
          $attrs['required'] = 'required';
        
        return new InputElement( 'number', $name, $id ?? '', $value, $attrs );
      },
              
      IStringProperty::class => function( IStringProperty $prop, string $name, ?string $id, string $value ) : IElement {
        $attrs = [];
        
        if ( $prop->getMin() != -1 )
          $attrs['minlength'] = $prop->getMin();
        
        if ( $prop->getMax() != -1 )
          $attrs['maxlength'] = $prop->getMax();
        
        if ( !empty( $prop->getPattern()))
        {          
          $attrs['pattern'] = substr( $prop->getPattern(), 1, -1 );
        }
        
        if ( $prop->getFlags()->hasVal( IPropertyFlags::REQUIRED ))
          $attrs['required'] = 'required';
        
        if ( $prop->getMax() != -1 && $prop->getMax() > 255 )
          return new TextAreaElement( $name, $id, $value, $attrs );
        else
          return new InputElement( 'text', $name, $id ?? '', $value, $attrs );
      }
    ], $map );
  }
  
  
  /**
   * Get the factory components 
   * @return array IElementFactoryComponent[] Components 
   */
  public function getMap() : array
  {
    $out = [];
    foreach( $this->map as $k => $f )
    {
      $out[] = new ElementFactoryComponent( $k, $f );
    }
    
    return $out;
  }
}
