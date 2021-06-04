<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\buffalotools\types\IEnum;
use buffalokiwi\buffalotools\types\ISet;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\property\BasePropertyConfig;
use buffalokiwi\magicgraph\property\EPropertyType;
use buffalokiwi\magicgraph\property\htmlproperty\IElement;
use buffalokiwi\magicgraph\property\htmlproperty\InputElement;
use buffalokiwi\magicgraph\property\htmlproperty\SelectElement;
use buffalokiwi\magicgraph\property\htmlproperty\TextAreaElement;
use buffalokiwi\magicgraph\property\INamedPropertyBehavior;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\property\SPropertyFlags;
use function json_decode;




/**
 * Product attribute property model configuration 
 */
class AttributeProperties extends BasePropertyConfig implements IPropertyConfig, IAttributeCols
{
  /**
   * Id column name
   */
  const AID = 'id';
  
  /**
   * type column name
   */
  const ATYPE = 'type';
  
  /**
   * default value column name
   */
  const ADEFAULT = 'default_value';
  
  /**
   * class column name
   */
  const ACLAZZ = 'object_class';
  
  /**
   * flags column name
   */
  const AFLAGS = 'flags';
  
  /**
   * min column name
   */
  const AMIN = 'min_length';
  
  /**
   * max column name
   */
  const AMAX = 'max_length';
  
  /**
   * pattern column name
   */
  const APATTERN = 'pattern';
  
  /**
   * behavior class column name
   */
  const ABEHAVIOR = 'behavior';
  
  /**
   * caption column name
   */
  const ACAPTION = 'caption';
  
  /**
   * code column name
   */
  const ACODE = 'code';
  
  /**
   * Config column name 
   */
  const ACONFIG = 'config';
  
  /**
   * Tag column name 
   */
  const TAG = 'tag';
  
  /**
   * Attribute behavior factory instance 
   * @var IBehaviorFactory 
   */
  private $behaviorFactory;
  
  /**
   * Property flags class name.
   * Should be an implemention of IPropertyFlags
   * @var string
   */
  private $flagsClass;
  
  
  public function __construct( string $flagsClass = SPropertyFlags::class, ?IBehaviorFactory $factory = null, INamedPropertyBehavior ...$propertyBehavior )
  {
    parent::__construct( ...$propertyBehavior );
    $this->flagsClass = $flagsClass;
    $this->behaviorFactory = $factory;    
    $this->addBeforeSave( function( IAttribute $attr ) {
      //..All attributes require the subconfig flag?
      $attr->getFlags()->add( IPropertyFlags::SUBCONFIG );
      
      //..Attributes MUST NOT contain the primary flag
      $attr->getFlags()->remove( IPropertyFlags::PRIMARY );
      
      if ( empty( $attr->getCaption()))
        $attr->setCaption( $attr->getCode());
    });
  }
  
  
  /**
   * Retrieve the attribute id column name 
   * @return string column name 
   */
  public function getId() : string
  {
    return self::AID;
  }
  
  
  /**
   * Retrieve the attribute type column name 
   * @return string column name 
   */
  public function getType() : string
  {
    return self::ATYPE;
  }
  
  
  /**
   * Retrieve the attribute default value column name 
   * @return string column name 
   */
  public function getDefault() : string
  {
    return self::ADEFAULT;
  }
  
  
  /**
   * Retrieve the attribute data class name column name 
   * @return string column name 
   */
  public function getClass() : string
  {
    return self::ACLAZZ;
  }
  
  
  /**
   * Retrieve the attribute flags column name 
   * @return string column name 
   */
  public function getFlags() : string
  {
    return self::AFLAGS;
  }
  
  
  /**
   * Retrieve the attribute min value/length column name 
   * @return string column name 
   */
  public function getMin() : string
  {
    return self::AMIN;
  }
  
  
  /**
   * Retrieve the attribute max value/length column name 
   * @return string column name 
   */
  public function getMax() : string
  {
    return self::AMAX;
  }
  
  
  /**
   * Retrieve the attribute validation regex pattern column name 
   * @return string column name 
   */
  public function getPattern() : string
  {
    return self::APATTERN;
  }
  
  
  /**
   * Retrieve the attribute additional behavior class name column name 
   * @return string column name 
   */
  public function getBehavior() : string
  {
    return self::ABEHAVIOR;
  }
  
  
  /**
   * Retrieve the attribute caption column name 
   * @return string column name 
   */
  public function getCaption() : string
  {
    return self::ACAPTION;
  }
  
  
  /**
   * Retrieve the attribute code column name 
   * @return string column name 
   */
  public function getCode() : string
  {
    return self::ACODE;
  }
  
  
  /**
   * Retrieve the config column naem 
   * @return string name 
   */
  public function getConfigColumn() : string
  {
    return self::ACONFIG;
  }
  
  
  /**
   * Retrieve the tag column name 
   * @return string name 
   */
  public function getTagColumn() : string
  {
    return self::TAG;
  }


  /**
   * Retrieve the model property set configuration array
   * @return array data
   */
  protected function createConfig() : array
  {
    $behavior = [self::TYPE => IPropertyType::TSTRING, self::VALUE => '', self::FLAGS => [IPropertyFlags::NO_INSERT]];

    //..Not sure what this does...
    if ( $this->behaviorFactory != null )
    {
      $behavior[self::INIT] = function( IModel $model, IProperty $property, $value ) {
        return $this->behaviorFactory->getInstance( $value );
      };
    }

    $self = $this;
    $options = [];
    
    return [
      self::AID => [
        self::TYPE => IPropertyType::TINTEGER,
        self::FLAGS => [IPropertyFlags::PRIMARY, IPropertyFlags::REQUIRED, IPropertyFlags::NO_INSERT],
        self::VALUE => 0
      ],

      self::ATYPE => [
        self::TYPE => IPropertyType::TENUM,
        self::FLAGS => [IPropertyFlags::REQUIRED, IPropertyFlags::NO_UPDATE],
        self::CLAZZ => EPropertyType::class,
        self::VALUE => EPropertyType::TSTRING()
      ],

      self::ADEFAULT => [
        self::TYPE => IPropertyType::TSTRING,
        self::FLAGS => [],
        self::VALUE => '',
        self::HTMLINPUT => function( IModel $model, IProperty $prop, string $name, string $id, $value ) use($self,&$options) : IElement {
          //..This is hacky, but it swaps the default value input out for a dropdown box if the data type supports it.
          $c = $model->getValue( self::ACLAZZ );
          
          //..Switch this out in favor of reading ATYPE.
          
          //..This needs some thought.  All of this code is going to be a duplicate of what's in DefaultComponentMap.
          /*
          switch( $model->getValue( self::ATYPE )->value())
          {
            case IPropertyType::TBOOLEAN:
              
            case IPropertyType::TENUM:
              
            case IPropertyType::TFLOAT:
            case IPropertyType::TINTEGER:
            case IPropertyType::TMONEY:
            case IPropertyType::
          }
          */
          
          //..Fine for now, move the fuck on...
          //..I've paid twice for this nonsense... Next time you see this shit, MOVE THE CODE.
          if ( !empty( $c ) && class_exists( $c ))
          {
            $o = new $c();
            if ( $o instanceof IEnum ) 
            {
              if ( empty( $options ))
              {
                foreach( $o->getEnumValues() as $o )
                {
                  $options[$o] = $o;
                }
              }
              
              return new SelectElement($name, $id, (string)$value, $options );            
            }
            else if ( $o instanceof ISet )
            {
              if ( empty( $options ))
                $options = $o->getMembers();
              
              return new SelectElement( $name, $id, (string)$value, $options,['multiple' => 'multiple', 'size' => 10]);
            }
          }
          
          return new InputElement( 'text', $name, $id, $value );
        }          
      ],

      self::ACLAZZ => [
        self::TYPE => IPropertyType::TSTRING,
        self::VALUE => ''
      ],
      self::AFLAGS => [
        self::TYPE => IPropertyType::TSET,
        self::CLAZZ => $this->flagsClass,
        self::VALUE => ''
      ],

      self::AMIN => [
        self::TYPE => IPropertyType::TINTEGER,
        self::VALUE => -1,
      ],

      self::AMAX => [
        self::TYPE => IPropertyType::TINTEGER,
        self::VALUE => -1,
      ],

      self::APATTERN => [
        self::TYPE => IPropertyType::TSTRING,
        self::VALUE => ''
      ],

      self::ABEHAVIOR => $behavior,

      self::ACAPTION => [
        self::TYPE => IPropertyType::TSTRING,
        self::FLAGS => [IPropertyFlags::REQUIRED],
        self::VALUE => ''
      ],

      self::ACODE => [
        self::TYPE => IPropertyType::TSTRING,
        self::FLAGS => [IPropertyFlags::REQUIRED, IPropertyFlags::NO_UPDATE],
        self::VALUE => ''
      ],

      self::ACONFIG => [
        self::TYPE => IPropertyType::TARRAY,
        self::FLAGS => [IPropertyFlags::USE_NULL],
        self::VALUE => [],
        self::MSETTER => function( IModel $model, IProperty $prop, $value ) {
     
          if ( empty( $value ))
            $value = null;
          else if( !is_array( $value ))
          {
            //..This is stupid.  Figure this out...
            $c = substr( trim( $value ), 0, 1 );
            if ( $c == '{' || $c == '[' )
              $value = json_decode( $value );
            else if ( strpos( $value, ',' ) !== false )                    
              $value = explode( ',', $value );
            else
            {
              $value = explode( "\n", str_replace( "\r", '', $value ));
            }
          }
          
          return $value;
        },
        
        self::HTMLINPUT => function( IModel $model, IProperty $prop, string $name, string $id, $value ) use($self,&$options) : IElement {      
          $val = $model->getValue( self::ACONFIG );
          if ( $val === null )
            $val = '';
          else
            $val = implode( "\n", $val );
      
          return new TextAreaElement( self::ACONFIG, '', $val );
        }
      ],
              
      self::TAG => self::FSTRING
    ];
  }
}
