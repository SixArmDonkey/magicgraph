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

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\property\BasePropertyConfig;
use buffalokiwi\magicgraph\property\INamedPropertyBehavior;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;


/**
 * Attribute value config 
 */
class AttrValueConfig extends BasePropertyConfig implements IPropertyConfig, IAttrValueCols
{
  /**
   * entity id column name
   */
  const ALINK_ENTITY = 'link_entity';
  
  /**
   * attribute id column name 
   */
  const ALINK_ATTR = 'link_attribute';
  
  /**
   * Value column name 
   */
  const AVALUE = 'value';
  
  /**
   * Text value column name 
   */
  const ATEXT = 'tvalue';
  
  
  /**
   * Constructor 
   * @param INamedPropertyBsearchior $behavior Property behavior modifications 
   */
  public function __construct( INamedPropertyBehavior ...$behavior )
  {
    parent::__construct( ...$behavior );
  }
  
  
  /**
   * Get entity id column name 
   * @return string name 
   */
  public function getEntityId() : string
  {
    return self::ALINK_ENTITY;
  }
  
  
  /**
   * Get attribute id column name 
   * @return string name 
   */
  public function getAttributeId() : string
  {
    return self::ALINK_ATTR;
  }
  
  
  /**
   * Get value column name 
   * @return string name 
   */
  public function getValue() : string
  {
    return self::AVALUE;
  }
  
  
  /**
   * Retrieve the text value 
   * @return string
   */
  public function getTextValue() : string
  {
    return self::ATEXT;
  }
  
  
  /**
   * Retrieve the property set config array 
   * @return array data 
   */
  protected function createConfig() : array
  {
    return [
      self::ALINK_ENTITY => [
        self::TYPE => IPropertyType::TINTEGER,
        self::FLAGS => [IPropertyFlags::PRIMARY, IPropertyFlags::REQUIRED],
        self::VALUE => 0        
      ],
        
      self::ALINK_ATTR => [
        self::TYPE => IPropertyType::TINTEGER,
        self::FLAGS => [IPropertyFlags::PRIMARY, IPropertyFlags::REQUIRED],
        self::VALUE => 0       
      ],
        
      self::VALUE => [
        self::TYPE => IPropertyType::TSTRING,
        self::FLAGS => [],
        self::VALUE => '',
        self::MSETTER => function( IModel $model, IProperty $prop, string $value ) : string {
          if ( strlen( $value ) > 255 )
          {
            $this->model->setValue( self::ATEXT, $value );
            return '';
          }
          
          return $value;
        },
                
        self::MGETTER => function( IModel $model, IProperty $prop, string $value ) : string {
          $txt = $model->getValue( self::ATEXT );
          if ( !empty( $txt ))
            return $txt;
          
          return $value;
        }
      ],
              
      self::ATEXT => self::FSTRING
    ];
  }  
}
