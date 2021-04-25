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

use buffalokiwi\buffalotools\date\IDateFactory;
use buffalokiwi\buffalotools\ioc\IIOC;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\money\IMoneyFactory;
use Closure;
use Exception;


/**
 * A factory for creating instances of Builders and Properties based on IPropertyType.
 * 
 * Maintains 2 maps:
 * 
 * 1) A map of IPropertyType => IPropertyBuilder factory for creating property builder instances based on type
 * 2) A map of IPropertyType => IProperty factory for creating instances of IProperty using the appropriate builder defined in step 1
 * 
 * @todo maybe make this extensible?  Create ways to create instances of 
 */
class DefaultConfigMapper extends BasePropertyBuilderConfigMapper
{
  /**
   * IOC container 
   * @var IIOC
   */
  private $ioc;
  
  /**
   * Date format to use when converting Date properties to strings.
   * @var string
   */
  private $dateFormat;
  
  
  /**
   * 
   * @param IIOC|null $ioc DANGER: Passing the IOC container is not inherently 
   * safe, but it is required to create IModel instances.
   * @param string $dateFormat Date format to use when converting Date properties to strings.
   */
  public function __construct( ?IIOC $ioc = null, string $dateFormat = 'Y-m-d H:i:s' )
  {
    if ( empty( $dateFormat ))
      throw new \InvalidArgumentException( 'Date format must not be empty' );
    
    $this->dateFormat = $dateFormat;
    $this->ioc = $ioc;
    if ( $ioc != null )
      $df = $ioc->getInstance( IDateFactory::class );
    else
      $df = null;
    
    
    
    
    $b = EPropertyType::TBOOLEAN();
    $d = EPropertyType::TDATE();
    $f = EPropertyType::TFLOAT();
    $e = EPropertyType::TENUM();
    $r = EPropertyType::TRTENUM();
    $i = EPropertyType::TINTEGER();
    $m = EPropertyType::TMONEY();
    $t = EPropertyType::TSET();
    $s = EPropertyType::TSTRING();
    $a = EPropertyType::TARRAY();
    $l = EPropertyType::TMODEL();
    $o = EPropertyType::TOBJECT();
    
    
    $builders =  [
      EPropertyType::TBOOLEAN => function() use($b) { return new PropertyBuilder( $b, new SPropertyFlags()); },
      EPropertyType::TDATE    => function() use($d) { return new PropertyBuilder( $d, new SPropertyFlags()); }, //..placeholder
      EPropertyType::TFLOAT   => function() use($f) { return new BoundedPropertyBuilder( $f, new SPropertyFlags()); }, 
      EPropertyType::TENUM    => function() use($e) { return new ObjectPropertyBuilder( $e, new SPropertyFlags()); },
      EPropertyType::TRTENUM  => function() use($r) { return new PropertyBuilder( $r, new SPropertyFlags()); },
      EPropertyType::TINTEGER => function() use($i) { return new BoundedPropertyBuilder( $i, new SPropertyFlags()); },
      EPropertyType::TSET     => function() use($t) { return new ObjectPropertyBuilder( $t, new SPropertyFlags()); },
      EPropertyType::TSTRING  => function() use($s) { return new StringPropertyBuilder( $s, new SPropertyFlags()); },
      EPropertyType::TARRAY   => function() use($a) { return new ObjectPropertyBuilder( $a, new SPropertyFlags()); },
      EPropertyType::TMODEL   => function() use($l) { return new ObjectPropertyBuilder( $l, new SPropertyFlags(), $this->createModelIOCClosure()); },
      EPropertyType::TOBJECT  => function() use($o) { return new ObjectPropertyBuilder( $o, new SPropertyFlags()); },
    ];
    
      
    $factories = [
      EPropertyType::TBOOLEAN => function( IPropertyBuilder $builder ) { return new BooleanProperty( $builder ); },
      EPropertyType::TDATE    => function( IPropertyBuilder $builder ) use ($df,$dateFormat) { return new DateProperty( $builder, $df, $dateFormat ); },
      EPropertyType::TFLOAT   => function( IPropertyBuilder $builder ) { return new FloatProperty( $builder ); },
      EPropertyType::TENUM    => function( IPropertyBuilder $builder ) { return new EnumProperty( $builder ); },
      EPropertyType::TRTENUM  => function( IPropertyBuilder $builder ) { return new RuntimeEnumProperty( $builder ); },
      EPropertyType::TINTEGER => function( IPropertyBuilder $builder ) { return new IntegerProperty( $builder ); },
      EPropertyType::TSET     => function( IPropertyBuilder $builder ) { return new SetProperty( $builder ); },
      EPropertyType::TSTRING  => function( IPropertyBuilder $builder ) { return new StringProperty( $builder ); },
      EPropertyType::TARRAY   => function( IPropertyBuilder $builder ) { return new ArrayProperty( $builder ); },
      EPropertyType::TMODEL   => function( IPropertyBuilder $builder ) { return new ModelProperty( $builder ); },
      EPropertyType::TOBJECT  => function( IPropertyBuilder $builder ) { return new ObjectProperty( $builder ); },
    ];

        
    if ( $ioc != null )
    {
      $mf = $this->getMoneyFactory();      
      $builders[EPropertyType::TMONEY] = function() use($m) { return new BoundedPropertyBuilder( $m, new SPropertyFlags()); };
      $factories[EPropertyType::TMONEY] = function( IPropertyBuilder $builder ) use ($mf) { return new MoneyProperty( $builder, $mf ); };
    }
    else
    {
      //..If no IOC container is passed, back TMONEY with a string.
      $builders[EPropertyType::TMONEY] = function() use($s) { return new StringPropertyBuilder( $s, new SPropertyFlags()); };
      $factories[EPropertyType::TMONEY] = function( IPropertyBuilder $builder ) { return new StringProperty( $builder ); };
    }
        
    
    parent::__construct(
      new PropertyBuilderIoC( new EPropertyType(), $builders ),
      new PropertyIoC( new EPropertyType(), $factories )
    );
  }
  
  
  
  private function createModelIOCClosure() : ?Closure
  {
    if ( $this->ioc == null )
      return null;
    
    $ioc = $this->ioc;
    return function( string $clazz ) use($ioc) : ?IModel {
      if ( !$ioc->hasInterface( $clazz ))
        return null;
      
      return $ioc->newInstance( $clazz );
    };
  }  
  
  
  private function getMoneyFactory() : IMoneyFactory 
  {
    if ( $this->ioc == null )
      throw new Exception( "MONEY properties require the IIOC container to not be null" );
    
    return $this->ioc->getInstance( IMoneyFactory::class );
  }
}
