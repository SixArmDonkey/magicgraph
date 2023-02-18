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

namespace buffalokiwi\magicgraph\property;

use buffalokiwi\buffalotools\date\IDateFactory;
use buffalokiwi\buffalotools\ioc\IIOC;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\money\IMoneyFactory;
use Closure;
use Exception;
use InvalidArgumentException;


/**
 * A factory for creating instances of Builders and Properties based on IPropertyType.
 * 
 * Maintains 2 maps:
 * 
 * 1) A map of IPropertyType => IPropertyBuilder factory for creating property builder instances based on type
 * 2) A map of IPropertyType => IProperty factory for creating instances of IProperty using the appropriate builder defined in step 1
 * 
 * 
 * Note: When using custom properties, this code may be better closer to the project entry point by creating an 
 * instance of BasePropertyBuilderConfigMapper directly.  
 * 
 * This default implementation is provided as both a default and an example.
 * 
 * 
 * This may be shared everywhere.
 * 
 * ===================================
 * 
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
      throw new InvalidArgumentException( 'Date format must not be empty' );
    
    $this->dateFormat = $dateFormat;
    $this->ioc = $ioc;
    
    
    
    /**
     * Invoking via __callStatic causes the enum to become read only by default.
     * We can share the same instance between all property instances.
     * @todo Move this code into composition root.  
     * @todo Figure out how to provide this code as part of a default build
     */
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
      EPropertyType::TBOOLEAN => fn() => new PropertyBuilder( $b, new SPropertyFlags()),
      EPropertyType::TDATE    => fn() => new PropertyBuilder( $d, new SPropertyFlags()),
      EPropertyType::TFLOAT   => fn() => new BoundedPropertyBuilder( $f, new SPropertyFlags()),
      EPropertyType::TENUM    => fn() => new ObjectPropertyBuilder( $e, new SPropertyFlags()),
      EPropertyType::TRTENUM  => fn() => new PropertyBuilder( $r, new SPropertyFlags()),
      EPropertyType::TINTEGER => fn() => new BoundedPropertyBuilder( $i, new SPropertyFlags()),
      EPropertyType::TSET     => fn() => new ObjectPropertyBuilder( $t, new SPropertyFlags()),
      EPropertyType::TSTRING  => fn() => new StringPropertyBuilder( $s, new SPropertyFlags()),
      EPropertyType::TARRAY   => fn() => new ObjectPropertyBuilder( $a, new SPropertyFlags()),
      EPropertyType::TOBJECT  => fn() => new ObjectPropertyBuilder( $o, new SPropertyFlags()),
      EPropertyType::TMODEL   => function() use($l) { 
        $builder = new ObjectPropertyBuilder( $l, new SPropertyFlags());
        $ccf = $this->createModelIOCClosure();
        if ( $ccf instanceof \Closure )
          $builder->setCreateObjectFactory( $ccf );
      }
    ];
    
      
    $factories = [
      EPropertyType::TBOOLEAN => fn( IPropertyBuilder $builder ) => new BooleanProperty( $builder ),
      EPropertyType::TDATE    => fn( IPropertyBuilder $builder ) => new DateProperty( $builder, $df, $dateFormat ),
      EPropertyType::TFLOAT   => fn( IPropertyBuilder $builder ) => new FloatProperty( $builder ),
      EPropertyType::TENUM    => fn( IPropertyBuilder $builder ) => new EnumProperty( $builder ),
      EPropertyType::TRTENUM  => fn( IPropertyBuilder $builder ) => new RuntimeEnumProperty( $builder ),
      EPropertyType::TINTEGER => fn( IPropertyBuilder $builder ) => new IntegerProperty( $builder ),
      EPropertyType::TSET     => fn( IPropertyBuilder $builder ) => new SetProperty( $builder ),
      EPropertyType::TSTRING  => fn( IPropertyBuilder $builder ) => new StringProperty( $builder ),
      EPropertyType::TARRAY   => fn( IPropertyBuilder $builder ) => new ArrayProperty( $builder ),
      EPropertyType::TMODEL   => fn( IPropertyBuilder $builder ) => new ModelProperty( $builder ),
      EPropertyType::TOBJECT  => fn( IPropertyBuilder $builder ) => new ObjectProperty( $builder )
    ];
    
            
    if ( $ioc != null )
    {
      $df = $ioc->getInstance( IDateFactory::class );
      $mf = $this->getMoneyFactory();      
      $builders[EPropertyType::TMONEY] = fn() => new BoundedPropertyBuilder( $m, new SPropertyFlags());
      $factories[EPropertyType::TMONEY] = fn( IPropertyBuilder $builder ) => new MoneyProperty( $builder, $mf );
    }
    else
    {
      //..If no IOC container is passed, back TMONEY with a string.
      $df = null;
      $builders[EPropertyType::TMONEY] = fn() => new StringPropertyBuilder( $s, new SPropertyFlags());
      $factories[EPropertyType::TMONEY] = fn( IPropertyBuilder $builder ) => new StringProperty( $builder );
    }
        
    
    parent::__construct(
      new PropertyBuilderFactory( new EPropertyType(), $builders ),
      new PropertyFactory( new EPropertyType(), $factories )
    );
  }
  
  
  /**
   * Used by object properties.  Creates instances of a defined class/instance type using the supplied container.
   * @return Closure|null A factory or null 
   */
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
  
  
  /**
   * Attempt to retrieve an instance of IMoneyFactory from a container 
   * @return IMoneyFactory factory 
   * @throws Exception not found 
   */
  private function getMoneyFactory() : IMoneyFactory 
  {
    if ( $this->ioc == null )
      throw new Exception( "MONEY properties require the IIOC container to not be null" );
    
    return $this->ioc->getInstance( IMoneyFactory::class );
  }
}
