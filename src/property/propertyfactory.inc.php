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




/**
 * Maps a configuration array to a list of properties.
 * 
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
 * 
 * ];
 * 
 * 
 */
class PropertyFactory implements IPropertyFactory
{
  /**
   * Config mapper 
   * @var IConfigMapper 
   */
  private $mapper;

  
  /**
   * Create a new PropertyFactory using some configuration array 
   */
  public function __construct( IConfigMapper $mapper )
  {
    $this->mapper = $mapper;
  }

  
  /**
   * Retrieve the config mapper used to create IProperty instances from 
   * config arrays.
   * @return \buffalokiwi\magicgraph\IConfigMapper Mapper 
   */
  public function getMapper() : IConfigMapper
  {
    return $this->mapper;
  }
  
  
  /**
   * Retrieve a list of properties 
   * @param IPropertyConfig $config One or more configuration instances.
   * @return IProperty[] properties
   */
  public function getProperties( IPropertyConfig ...$config ) : array
  {
    $out = [];
    foreach( $config as $c )
    {
      try {
        $out = array_merge( $out, $this->mapper->map( $c->getConfig()));
      } catch( \Exception $e ) {
        trigger_error( 'Property configuration object ' . get_class( $c ) . ' contains errors and cannot be compiled', E_USER_ERROR );
        throw $e;
      }
    }
    
    
    return $out;
  }
}
