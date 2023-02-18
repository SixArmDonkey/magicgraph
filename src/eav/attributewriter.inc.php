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

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\IPropertyServiceProvider;
use buffalokiwi\magicgraph\property\DefaultPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertySetFactory;
use buffalokiwi\magicgraph\property\QuickPropertyConfig;


/**
 * Writes new attributes to the IAttributeRepo based on configured properties 
 * contained within $services.
 */
class AttributeWriter 
{
  private $repo;
  private $setFactory;
  private $services;
  
  public function __construct( IAttributeRepo $repo, IPropertySetFactory $setFactory, IPropertyServiceProvider ...$services )
  {
    $this->repo = $repo;
    $this->setFactory = $setFactory;
    $this->services = $services;
  }
  
  
  private function getAttributesToCheckFromServices()
  {
    $cfg = [];
    foreach( $this->services as $s )
    {
      $cfg = array_merge( $cfg, $s->getPropertyConfig()->getConfig());
    }
    
    $toCheck = [];
    
    foreach( $cfg as $name => $data )
    {
      if ( !isset( $data[DefaultPropertyConfig::FLAGS] ) 
        || !is_array( $data[DefaultPropertyConfig::FLAGS] ) 
        || !in_array( IPropertyFlags::SUBCONFIG, $data[DefaultPropertyConfig::FLAGS] ))
      {
        continue;
      }
      
      $toCheck[$name] = $data;
    }    
    
    return $toCheck;
  }
  
  
  private function getAttributeToAddFromCheckedAttributes( array $toCheck )
  {
    $toAdd = [];
    $report = $this->repo->existsReport( ...array_keys( $toCheck ));
    
    foreach( $report as $code => $exists )
    {
      if ( !$exists )
      {
        $toAdd[$code] = $toCheck[$code];
      }
    }

    return $toAdd;    
  }
  
  
  private function getAttributesToSaveFromAttributesToAdd( array $toAdd )
  {
    $toSave = [];
    //..Add each attribute 
    foreach( $toAdd as $code => $config )
    {
      $attr = $this->repo->getAttributeRepo()->create( [] );
      /* @var $attr IAttribute */
      $testSet = $this->setFactory->createPropertySet( new QuickPropertyConfig( $toAdd ));
      $prop = $testSet->getProperty( $code );
      
      $dv = $prop->getDefaultValue();
      if ( is_bool( $dv ))
        $dv = ( $dv ) ? '1' : '0';
      else 
        $dv = (string)$dv;
      
      $attr->setCode( $code );
      $attr->setCaption( $prop->getCaption());
      $attr->setConfig( $prop->getConfig());
      $attr->setDefaultValue( $dv );
      $attr->setFlags( $prop->getFlags());
      $attr->setMax(( isset( $config[DefaultPropertyConfig::MAX] )) ? $config[DefaultPropertyConfig::MAX] : 2147483647 );
      $attr->setMin(( isset( $config[DefaultPropertyConfig::MIN] )) ? $config[DefaultPropertyConfig::MIN] : -2147483647 );
      $attr->setPattern(( isset( $config[DefaultPropertyConfig::PATTERN] )) ? $config[DefaultPropertyConfig::PATTERN] : '' );
      $attr->setPropertyClass(( isset( $config[DefaultPropertyConfig::CLAZZ] )) ? $config[DefaultPropertyConfig::CLAZZ] : '' );
      
      /**
       * @todo getType() may be deprecated in the future.  Manage eav property types within the eav package.
       * @todo Maybe make EAVProperty extends AbstractProperty and require IPropertyType as a constructor argument to EAVProperty 
       */
      $attr->setPropertyType( $prop->getType());

      $toSave[] = $attr;        
    }    
    
    return $toSave;
  }
  
  
  public function writeAttributes()
  {
    $toSave = $this->getAttributesToSaveFromAttributesToAdd( $this->getAttributeToAddFromCheckedAttributes( $this->getAttributesToCheckFromServices()));
    
    
    
    if ( !empty( $toSave ))
      $this->repo->getAttributeRepo()->saveAll( ...$toSave );
  }  
}
