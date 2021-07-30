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

namespace buffalokiwi\magicgraph\eav\search;

use buffalokiwi\magicgraph\eav\IAttrGroupLinkCols;
use buffalokiwi\magicgraph\eav\IAttributeCols;
use buffalokiwi\magicgraph\eav\IAttributeGroupPropertiesCols;
use buffalokiwi\magicgraph\eav\IAttrValueCols;
use buffalokiwi\magicgraph\pdo\IDBConnection;
use buffalokiwi\magicgraph\persist\ISQLRepository;
use buffalokiwi\magicgraph\property\IBoundedProperty;
use buffalokiwi\magicgraph\property\IProperty;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\property\IPropertyType;
use buffalokiwi\magicgraph\search\ESQLJoinType;
use buffalokiwi\magicgraph\search\IQueryBuilderOutput;
use buffalokiwi\magicgraph\search\ISearchQueryBuilder;
use buffalokiwi\magicgraph\search\ISearchQueryGenerator;
use buffalokiwi\magicgraph\search\ISQLJoinFilter;
use buffalokiwi\magicgraph\search\MySQLQueryBuilderOutput;
use buffalokiwi\magicgraph\search\SearchException;
use InvalidArgumentException;


/**
 * A simple search query generator for mysql.
 * This is to be used with the magicgraph\eav (eav) package.
 * This is a very rough prototype, and will be revised in a future release.
 * 
 * @todo This barely functions, but it does function.  Mostly at night, mostly.
 */
class MySQLEAVSearchQueryGenerator implements ISearchQueryGenerator
{
  /**
   * Database connection 
   * @var IDBConnection 
   */
  private IDBConnection $dbc;
  
  /**
   * Entity repository 
   * @var ISQLRepository 
   */
  private ISQLRepository $entityRepo;
  
  /**
   * Attribute repo 
   * @var ISQLRepository
   */
  private ISQLRepository $attrRepo;
  
  /**
   * Attribute group repo 
   * @var ISQLRepository
   */
  private ISQLRepository $attrGroupRepo;
  
  /**
   * Attribute group members repository 
   * @var ISQLRepository
   */
  private ISQLRepository $attrGroupLinkRepo;
  
  /**
   * Attribute value repo
   * @var ISQLRepository
   */
  private ISQLRepository $attrValueRepo;
  
  /**
   * Entity properties.
   * Everything you ever wanted to know about an entity.
   * @var IPropertySet
   */
  private IPropertySet $entityProps;

  
  /**
   * Attribute columns 
   * @var IAttributeCols
   */
  private IAttributeCols $attrCols;
  
  /**
   * Attribute group columns 
   * @var IAttributeGroupPropertiesCols 
   */
  private IAttributeGroupPropertiesCols $attrGroupCols;
  
  /**
   * Attribute group link columns 
   * @var IAttrGroupLinkCols
   */
  private IAttrGroupLinkCols $attrGroupLinkCols;
  
  /**
   * Attribute value columns
   * @var IAttrValueCols
   */
  private IAttrValueCols $attrValueCols;
  
  /**
   * Join filter list 
   * @var ISQLJoinFilter[] 
   */
  private array $joinFilterList = [];
  
  /**
   * Linked property sets
   * @var IPropertySet[] 
   */
  private array $linkedPropertySets = [];
      
  
  /**
   * MySQLSearchQueryBuilder 
   * @param ISQLRepository $entityRepo Entity repository 
   * @param ISQLRepository $attrRepo Attribute repository 
   * @param ISQLRepository $attrGroupRepo Attribute group repository 
   * @param ISQLRepository $attrGroupLinkRepo Attribute group members repository 
   * @param ISQLRepository $attrValueRepo Attribute value repository 
   */
  public function __construct( 
    IDBConnection $dbc,
    ISQLRepository $entityRepo,
    ISQLRepository $attrRepo, 
    ISQLRepository $attrGroupRepo, 
    ISQLRepository $attrGroupLinkRepo,
    ISQLRepository $attrValueRepo,
    ISQLJoinFilter ...$joinFilterList )
  {
    $this->dbc = $dbc;
    $this->entityRepo = $entityRepo;
    $this->attrRepo = $attrRepo;
    $this->attrGroupRepo = $attrGroupRepo;
    $this->attrGroupLinkRepo = $attrGroupLinkRepo;
    $this->attrValueRepo = $attrValueRepo;
    $this->entityProps = $entityRepo->createPropertySet();
    
    $this->attrCols = $this->attrRepo->createPropertySet()->getPropertyConfig( IAttributeCols::class );
    $this->attrGroupCols = $this->attrGroupRepo->createPropertySet()->getPropertyConfig( IAttributeGroupPropertiesCols::class );
    $this->attrGroupLinkCols = $this->attrGroupLinkRepo->createPropertySet()->getPropertyConfig( IAttrGroupLinkCols::class );
    $this->attrValueCols = $this->attrValueRepo->createPropertySet()->getPropertyConfig( IAttrValueCols::class );
    
    
    
    foreach( $joinFilterList as $filter )
    {      
      $this->joinFilterList[$filter->getPropertyName()] = $filter;
      $this->linkedPropertySets[$filter->getPropertyName()] = $filter->getPropertySet();
    }
  }
  
  
  public function getFilter( string $propertyName ) : ISQLJoinFilter
  {
    if ( empty( $propertyName ) || !isset( $this->joinFilterList[$propertyName] ))
      throw new \InvalidArgumentException( 'The supplied property name does not utilize a join filter' );
    
    return $this->joinFilterList[$propertyName];
  }  
  
  
  /**
   * Retrieve the property set used when searching linked types (Join Filters).
   * @param string $name Trigger property name (What the IJoinFilter was registered as )
   * @return IPropertySet Property set created by the host repo attached to the join filter.
   * @throws InvalidArgumentException
   */
  public function getJoinedPropertySet( string $name ) : IPropertySet
  {
    if ( !isset( $this->joinFilterList[$name] ))
      throw new InvalidArgumentException( $name . ' is not a registered join filter' );
    
    return $this->joinFilterList[$name]->getHostRepo()->createPropertySet();
  }  
  
  
  /**
   * Retrieve the schema 
   * @return array schema 
   */
  public function getSchema() : array
  {
    $out = $this->entityProps->getSchema();
    
    foreach( $this->joinFilterList as $name => $filter )
    {
      $s = $filter->getPropertySet()->getSchema();
      
      foreach( $s as $k => $v )
      {
        $out[$name . '.' . $k] = $v;
      }
    }
    
    return $out;
  }
  
  
  /**
   * Builds a MySQL query used to search an Search system.
   * 
   * Currently this is limited to where conditions like:
   * 
   * ( cond1=x and cond2=y ) or cond3=z or cond4=z1
   * 
   * The "in" conditions can do things like this
   * 
   * ( cond1=x and cond2 in (y,z))
   * 
   * It is worth noting that an attribute must be unique to the type of condition, but can be used in multiple conditions:
   * 
   * ( cond1=x and cond1 like '%foo%' and cond1 in (y,z)) or cond1=x or cond1 like '%foo%' or cond1 in (y,z)
   * 
   * The above is valid, and obviously would return nothing unless x and y (and/or z) equal 'foo'.
   * 
   * @param ISearchQueryBuider $builder input 
   * @param bool $returnCount When true, this returns a single column: "count" containing the total number of results.
   * @return IQueryBuilderOutput SQL and bindings 
   */
  public function createQuery( ISearchQueryBuilder $builder, bool $returnCount = false ) : IQueryBuilderOutput
  {
    //..The entity table alias 
    $entityAlias = 'e';
    
    //..Validate the query against the entity column definitions 
    $builder->validate( $this->entityProps );
      
    
    //..Table join sql fragments.  There is one entry per attribute being searched.
    $joins = [];
    
    //..Where conditions joined by "and"
    $andWhere = [];
    
    //..Where conditions 
    $orWhere = [];
    
    //..Where condition for entity columns
    $entityAndWhere = [];
    
    //..Where condition for entity columns 
    $entityOrWhere = [];
    
    //..Used by sql filter objects
    $filterJoin = [];
    
    //..Used by sql filter objects
    $filterWhere = [];
    
    //..Variable param index 
    $varIndex = 0;
    
    //..Values/bindings 
    $values = [];
    
    $groupBy = [];
    
    
    
    $codes = [];
    foreach( $this->entityProps->getPropertiesByFlag( IPropertyFlags::SUBCONFIG ) as $prop )
    {      
      $codes[] = $prop->getName();
    }

    
    //..Get the list of attribute ids 
    $aidList = $this->getAttributeIdListByCodes( $codes );    
    
    
    /*
    if ( $builder->isWild())
    {
      $codes = [];
      foreach( $this->entityProps->getPropertiesByFlag( IPropertyFlags::SUBCONFIG ) as $prop )
      {
        $codes[] = $prop->getName();
      }
      
      $aidList = $this->getAttributeIdListByCodes( $codes );
    }
    else
    {
      $aidList = $this->getAttributeIdListByCodes( $builder->getAttributes());
    }
    */
    
    
    //..A map of keys that have been used 
    $usedKeys = [];
    
    //..A list of attrbutes that may be using the tvalue column in thhe attribue_value table.
    $maybeText = [];
    
    //..Used as a suffix when duplicate attribute codes are used in different conditions.
    $index = 1;
    
    $first = '';
    
    
    //..Properties attached to the eav system 
    $coreProps = [];
    
    //..Property names from joined tables    
    $joinedProps = [];
    
    //..Build the columns to select 
    $select = [];
    
    //..A list of left joins keyed by alias. 
    //..This is required to prevent inner joins from overwriting left joins 
    $leftJoins = [];
    
    $joinAndWhere = [];
    $joinOrWhere = [];
    
    
    //..Build the various condition blocks     
    foreach( $builder->getConditions() as $andOr => $conditionGroups )
    {
      foreach( $conditionGroups as $operator => $conditions )
      {
        
        foreach( $conditions as $code => $value )
        {
          $codeParts = explode( '.', $code );
          if ( sizeof( $codeParts ) > 2 )
            throw new SearchException( 'Properties may only be nested one level.  ie: prop.subprop is ok.  prop.subprop.subsubprop is not ok.  This restriction will be removed in a future release.' );
          $code = $codeParts[0];
          $subProp = $codeParts[1] ?? null;
          
          //..Get the property for this condition 
          if ( $subProp == null )
            $prop = $this->entityProps->getProperty( $code );
          else if ( isset( $this->linkedPropertySets[$code] ))
            $prop = $this->linkedPropertySets[$code]->getProperty( $subProp );
          else
            throw new SearchException( 'Invalid property name' );
                      
          if ( is_bool( $value ))
            $value = ( $value ) ? '1' : '';
          
          
          if ( isset( $this->joinFilterList[$code] ))
          {
            $name = $subProp ?? $prop->getName();
            
            $filter = $this->joinFilterList[$code];
            /* @var $filter ISQLJoinFilter */
            //..This is a search on a joined table 
            
            if ( $filter->isForeign())
            {
              if ( !isset( $leftJoins[$code] ))
              {
                if ( $value === 'null' )
                  $value = null;
                
                $leftJoins[$code] = true;
                $filterJoin[$code] = $filter->getJoin( $this->entityProps->getPrimaryKey()->getName(), $entityAlias, ( $value === null ) ? ESQLJoinType::LEFT() : ESQLJoinType::INNER());              
              }             
            }
            
            $cond = $filter->prepareColumn( $name ) . $this->buildConditionOperand( $operator, $value, $varIndex, $values );
            
            
            //..Add to either and or or 
            switch( $andOr )
            {
              case 'and':
                $joinAndWhere[] = $cond;
              break;

              case 'or':
                $joinOrWhere[] = $cond;
              break;

              default:
                throw new SearchException( 'Operator must equal "and" or "or".' );
            }
          }
          else if ( $prop->getFlags()->hasVal( IPropertyFlags::SUBCONFIG ))
          {
            //..Subconfig properties are in the attribute_value table.
            $key = ' v' . $code;

            //..Only include the first variation of some attribute code.
            //..The repeats are not needed, and the $used array keeps track of that.
            $used = false;
            while ( isset( $usedKeys[$key] ))
            {
              $key = ' v' . $code . ( ++$index );
              $used = true;
            }

            $usedKeys[$key] = ( $used ) ? '' : $code;

            if ( empty( $first ))
              $first = $key;
            
            //..The condition where the attribute must equal some id and also equal some value 
            $cond = $key . '.' . $this->attrValueCols->getAttributeId() . '=:VAR' . (++$varIndex) . ' and ';          
            $values[':VAR' . $varIndex] = $aidList[$code];
            
            //..If the property is a bounded property and has a max length greater than 255.
            $isBounded255 = (( $prop instanceof IBoundedProperty ) && $prop->getMax() > 255 );

            //..Add the value part of the condition 
            if ( $prop->getType()->is( IPropertyType::TSTRING ) && $isBounded255 )
            {
              //..Add this to the maybeText array, because we need to use coalesce function to pull the data.
              $maybeText[$code] = true;
              //..Test against BOTH value and tvalue properties.  This might be greater than 255 characters 
              $cond .= '(' . $key . '.' . $this->attrValueCols->getValue() . $this->buildConditionOperand( $operator, $value, $varIndex, $values )
                . ' or ' . $key . '.' . $this->attrValueCols->getTextValue() . $this->buildConditionOperand( $operator, $value, $varIndex, $values ) . ')';
            }
            else 
            {
              //..Add the attribute_value.value condition.  tvalue is not needed.
              $cond .= $key . '.' . $this->attrValueCols->getValue() . $this->buildConditionOperand( $operator, $value, $varIndex, $values );
            }
            
            //..Add the join and value data 
            if ( empty( $joins ))
              $joins[] = $this->attrValueRepo->getTable() . $key;
            else 
              $joins[] = $this->attrValueRepo->getTable() . $key . ' on (' . $first . '.' . $this->attrValueCols->getEntityId() . '=' . $key . '.' . $this->attrValueCols->getEntityId() . ') ';

            
            //..Add to either and or or 
            switch( $andOr )
            {
              case 'and':
                $andWhere[] = $cond;
              break;

              case 'or':
                $orWhere[] = $cond;
              break;

              default:
                throw new SearchException( 'Operator must equal "and" or "or".' );
            }            
          }
          else if ( !$prop->getFlags()->hasVal( IPropertyFlags::PRIMARY ))
          {
            //..These are entity properties.  Primary key searches are disallowed.
            $cond = $entityAlias . '.' . $code . $this->buildConditionOperand( $operator, $value, $varIndex, $values );
            
            //..Add to either and or or 
            switch( $andOr )
            {
              case 'and':
                $entityAndWhere[] = $cond;
              break;

              case 'or':
                $entityOrWhere[] = $cond;
              break;

              default:
                throw new SearchException( 'Operator must equal "and" or "or".' );
            }            
          }
        }
      }
    }
    
    
    if ( !$builder->isWild())
    {
      foreach( $builder->getAttributes() as $attr )
      {
        if ( strpos( $attr, '.' ) !== false )
        {
          $joinedProps[] = $attr;
          $codeParts = explode( '.', $attr );
          $code = $codeParts[0];
          $subProp = $codeParts[1] ?? null;
          
          if ( isset( $this->joinFilterList[$code] ) && $subProp != null )
          {
            $filter = $this->joinFilterList[$code];          
            /* @var $filter ISQLJoinFilter */
            
            if ( $filter->isForeign())
            {
              if ( !isset( $leftJoins[$code] ))
              {
                $leftJoins[$code] = true;
                $filterJoin[$code] = $filter->getJoin( $this->entityProps->getPrimaryKey()->getName(), $entityAlias, ( isset( $filterJoin[$code] )) ? ESQLJoinType::INNER() : ESQLJoinType::LEFT());
              }

              $select[] = $filter->getHostRepo()->getTable() . '.' . $subProp . ' as `' . $code . '.' . $subProp . '` ';
            }
          }
        }
        else
          $coreProps[] = $attr;
      }
      
      $builder->addAttribute( ...$joinedProps );
    }    
    
    
    if ( !$builder->isWild() && !$this->entityProps->isMember( ...$coreProps ))
    {
      throw new SearchException( 'One of the supplied attributes does not belong to the associated property set' );
    }    
    
    $attrSearch = true;
    //..At least one attribute search condition must exist 
    if ( empty( $andWhere ) && empty( $orWhere ))
    {
      if ( empty( $entityAndWhere ) && empty( $entityOrWhere ) && empty( $filterWhere ))
        throw new SearchException( 'At least one condition is required.  Cannot query Search.' );
      
      $attrSearch = false;
    }
    
    
    
    //..Attributes to select 
    $attributes = [];
    
    if ( !$builder->isWild())
    {
      foreach( $builder->getAttributes() as $code )
      {
        //..Get the property for this condition 
        list( $prop, $isEntity ) = $this->getProperty( $code );
        //$prop = $this->entityProps->getProperty( $code );

        //..If the property is a bounded property and has a max length greater than 255.
        //..Add the value part of the condition 
        if ( $prop->getType()->is( IPropertyType::TSTRING ) && ( $prop instanceof IBoundedProperty ) && $prop->getMax() > 255 )
          $maybeText[$code] = true;

        $attributes[$code] = true;
      }
    }
    
    
    //..Attribute names used as a filter in the query 
    $attrColFilter = [];
    
    //..Go through the used keys and add them 
    $walk = array_keys( $usedKeys );
    

    array_walk( $walk, function( &$item, $key ) use ($usedKeys,&$select,$maybeText,&$attributes,&$attrColFilter) {
      if ( !empty( $usedKeys[$item] ) && isset( $attributes[$usedKeys[$item]] ))
      {
        //..Selecting this as part of the join 
        $attrColFilter[] = $usedKeys[$item];
        unset( $attributes[$usedKeys[$item]] );
      }
    });
    
           
    $attrSelect = [];
    
    //..Add any entity attribute to select 
    array_walk( $attributes, function( $unused, $key ) use (&$select,$entityAlias,&$attrSelect,&$groupBy) {
      //$prop = $this->entityProps->getProperty( $key );
      list( $prop, $isEntity ) = $this->getProperty( $key );
      
      /* @var $prop IProperty */
      
      if ( $prop->getType()->is( IPropertyType::TARRAY, IPropertyType::TMODEL, IPropertyType::TOBJECT )
       || $prop->getFlags()->hasVal( IPropertyFlags::NO_INSERT )) //..The NO_INSERT means the property IS NOT REAL.
      {
        return;
      }
      
      if ( $prop->getFlags()->hasVal( IPropertyFlags::SUBCONFIG ))
      {
        //..This needs to come from the attribute alias 
        $attrSelect[] = ( $isEntity ) ? $key : $prop->getName();
      }
      else if ( $isEntity )
      {        
        $select[] = $entityAlias . '.' . $key;
      }
      else
      {
        throw new SearchException( $key . '.' . $prop->getName() . ' cannot be selected via an eav search yet (not implemented).' );
      }
    });    
    
    
    
    
    //..Get the primary key names 
    $priKeys = [];    
    foreach( $this->entityProps->getPrimaryKeyNames() as $name )
    {      
      $priKeys[] = $entityAlias . '.' . $name;
    }
    
    if ( empty( $priKeys ))
      throw new SearchException( 'At least one primary key must be defined on the entity property set' );
    
    
    $page = $builder->getPage();
    $size = $builder->getResultSize();
            
    
    
    if ( $page < 1 )
      $page = 1;
    
    if ( $size < 1 )
      $size = 1;
    
    $offset = ( $page - 1 ) * $size;
    
    /**
     * Ends up being something like this:
     * 
select p.id, p.title,a1.code, a1.caption, v1.value, v1.tvalue
from
product p
join (
  select vis_map.link_entity from 
  product_attribute_value vis_map 
  where (( vis_map.link_attribute=34 and vis_map.value= '') ) 
  group by vis_map.link_entity) idList on (p.id=idList.link_entity)
left join product_attribute_value v1 on (v1.link_entity=p.id)
left join product_attribute a1 on (a1.id=v1.link_attribute)
where a1.code in ('test','link_mfg','price','case_qty');   
     */
    
    
    
    
    $entityWhere = [];
    
    
    if ( !empty( $attrColFilter ))
    {
      $parts = [];
      foreach( $attrColFilter as $v )
      {
        $values[':VAR' . (++$varIndex)] = $v;
        $parts[] = ':VAR' . ( $varIndex );
      }

      $entityAndWhere[] = ' a1.' . $this->attrCols->getCode() . ' in (' . implode( ',', $parts ) . ') ';
    }
    
    
    
    //..Build the and section 
    if ( !empty( $entityAndWhere ))
      $entityAnd = ' (' . implode( ' and ', $entityAndWhere ) . ') ';
    else
      $entityAnd = '';
    
    
    //..Build the or section 
    $entityOr = implode( ' or ', $entityOrWhere );
    
    ///..If both and and or exist, then add "or" as a prefix to the or string 
    //if ( !empty( $entityAndWhere ) && !empty( $entityOrWhere ))
      //$entityOr = ' or ' . $entityOr;    
    
    
   
    if ( !empty( $entityAnd ))
    {      
      $entityWhere[] = $entityAnd;
    }
    
    if ( !empty( $entityOr ))
      $entityWhere[] = $entityOr;
    

    if ( empty( $select ) || $builder->isWild())
      $select = $entityAlias . '.*,';
    else 
      $select = implode( ',', $select ) . (( !empty( $select )) ? ',' : '' );
    
    if ( !empty( $filterJoin ))
      $filterJoin = ' ' . implode( ' join ', $filterJoin );
    else
      $filterJoin = '';
    
    

    //..Build the attribute and section 
    if ( !empty( $andWhere ))
      $and = ' (' . implode( ' and ', array_merge( $andWhere )) . ') ';
    else
      $and = '';
    
    
    //..Build the or section 
    $or = implode( ' or ', $orWhere );
    
    ///..If both and and or exist, then add "or" as a prefix to the or string 
    if ( !empty( $andWhere ) && !empty( $orWhere ))
      $or = ' or ' . $or;    
    
    
    
    

    //..Build the and section 
    if ( !empty( $joinAndWhere ))
      $joinAnd = ' (' . implode( ' and ', $joinAndWhere ) . ') ';
    else
      $joinAnd = '';
    
    //..Build the or section 
    $joinOr = implode( ' or ', $joinOrWhere );
    
    ///..If both and and or exist, then add "or" as a prefix to the or string 
    if ( !empty( $joinAndWhere ) && !empty( $joinOrWhere ))
      $joinOr = ' or ' . $joinOr;    
    
    
   
    if ( !empty( $joinAnd ))
    {      
      $joinWhere[] = $joinAnd;
    }
    
    if ( !empty( $joinOr ))
      $joinWhere[] = $joinOr;
        
    
    
    
    
    if ( !empty( $joinWhere ))
      $joinWhere = ' where ' . implode( ' ', $joinWhere );
    else
      $joinWhere = '';
        
    
    if ( $attrSearch )
    {
      
      
      $filterWhere = array_merge( $filterWhere, $entityWhere );
      

      if ( !empty( $filterWhere ))
      {
        $filterWhere = (( empty( $joinWhere )) ? ' where ' : ' and ' ) . implode( ' and ', $filterWhere );
      }
      else
        $filterWhere = '';
      
      if ( $returnCount )
      {
        $sql = "select count(distinct %7\$s.%13\$s) as `count` "
          . 'from %6$s %7$s join ('
          .    'select %8$s.%9$s from %10$s join %6$s %7$s on (%7$s.%13$s=%8$s.%9$s) where %11$s %12$s group by %8$s.%9$s '
          . ') idList on (%7$s.%13$s=idList.%9$s) '
          . ' %20$s '//'join product_category_link cl on (cl.link_parent=e.id)'
          . ' join %14$s v1 on (v1.%9$s=%7$s.%13$s) '
          . ' join %15$s a1 on (a1.%16$s=v1.%17$s) %18$s'
          . ' %21$s ';//' where cl.link_target in (9)';        
      }
      else
      {
        $sql = "select %19\$s %1\$s, a1.%2\$s, a1.%3\$s, coalesce(nullif(v1.%4\$s,''), v1.%5\$s, '') as %5\$s "
          . 'from %6$s %7$s join ('
          .    'select %8$s.%9$s from %10$s join %6$s %7$s on (%7$s.%13$s=%8$s.%9$s) where %11$s %12$s group by %8$s.%9$s limit ' . $offset . ',' . $size
          . ') idList on (%7$s.%13$s=idList.%9$s) '
          . ' %20$s '//'join product_category_link cl on (cl.link_parent=e.id)'
          . ' join %14$s v1 on (v1.%9$s=%7$s.%13$s) '
          . ' join %15$s a1 on (a1.%16$s=v1.%17$s) %18$s'
          . ' %21$s ';//' where cl.link_target in (9)';
      }
      

      $sql = sprintf(
        $sql,
        implode( ',', $priKeys ),
        $this->attrCols->getCode(),
        $this->attrCols->getCaption(),
        $this->attrValueCols->getTextValue(),
        $this->attrValueCols->getValue(),
        $this->entityRepo->getTable(),
        $entityAlias,
        $first,
        $this->attrValueCols->getEntityId(),
        implode( ' join ', $joins ), //..10
        $and,
        $or,
        $this->entityProps->getPrimaryKey()->getName(),
        $this->attrValueRepo->getTable(),
        $this->attrRepo->getTable(),
        $this->attrCols->getId(),
        $this->attrValueCols->getAttributeId(),
        $joinWhere,//$entityWhere,
        $select, // 19
        $filterJoin, //..20 
        $filterWhere ); 
    }
    else
    {
      
      if ( !empty( $attrSelect ))
      {
        $parts = [];
        foreach( $attrSelect as $v )
        {
          $values[':VAR' . (++$varIndex)] = $v;
          $parts[] = ':VAR' . ( $varIndex );
        }

        $filterWhere[] = 'a1.' . $this->attrCols->getCode() . ' in (' . implode( ',', $parts ) . ') ';
      }
      
      if ( !empty( $filterWhere ))
        $filterWhere = (( empty( $entityWhere )) ? ' where ' : ' and ' ) . implode( ' and ', $filterWhere );
      else
        $filterWhere = '';      
      
      
      $s = explode( ',', $select );
      if ( !empty( $attrSelect ))
      {
         $extraSql = 'left join %10$s v1 on (v1.%8$s=%4$s.%9$s) '
           . 'left join %11$s a1 on (a1.%12$s=v1.%13$s) ';
         $s[] = 'a1.' . $this->attrCols->getCode();
         $s[] = 'a1.' . $this->attrCols->getCaption();
         $s[] = "coalesce(nullif(v1.%14\$s,''), v1.%15\$s, '') as `value`"; 
      }
      else
      {
        $extraSql = '';
      }

      foreach( $priKeys as $k )
      {
        $s[] = $k;
      }


      foreach( $s as $k => $v )
      {
        if ( empty( $v ))
          unset( $s[$k] );
      }

      $select = implode(',', $s );      
      
      if ( $returnCount )
      {
        $sql = 'select count(distinct %4$s.%9$s) as `count`  from %3$s %4$s %5$s '
          . $extraSql 
          . ' %6$s %7$s limit ' . $offset . ',' . $size;        
      }
      else
      {

        
        $sql = 'select ' . $select . ' from %3$s %4$s %5$s '
          . $extraSql 
          . ' %6$s %7$s limit ' . $offset . ',' . $size;
      }
      
      
      $sql = sprintf( $sql,
        '',
        '',
        $this->entityRepo->getTable(),
        $entityAlias,
        $filterJoin,
        $entityWhere,
        $filterWhere,
        $this->attrValueCols->getEntityId(),
        $this->entityProps->getPrimaryKey()->getName(), 
        $this->attrValueRepo->getTable(), 
        $this->attrRepo->getTable(),  
        $this->attrCols->getId(), 
        $this->attrValueCols->getAttributeId(),
        $this->attrValueCols->getTextValue(),
        $this->attrValueCols->getValue()        
      );
      
      
      
    }
    /*
      var_dump( $values );
      echo $sql;
      die;
      */
    return $this->createQueryBuilderOutput( $builder, $this->entityProps->getPrimaryKey()->getName(), $sql, $values );
    
    
    /**
     * This searches fine.  The problem is the need to pivot the attributes and how entity data should be pulled once and not
     * once for each attribute.  So, after the attribute query, an entity query needs to happen.  This can be a simple getAll() maybe.
     * The entity property names to select are in $select.  Since this is simply generating a sql query, the entity select
     * will need to happen in the controlling class.
     */
 
  }
  
  
  
  private function getProperty( string &$code ) : array
  {      
    $codeParts = explode( '.', $code );
    if ( sizeof( $codeParts ) > 2 )
      throw new SearchException( 'Properties may only be nested one level.  ie: prop.subprop is ok.  prop.subprop.subsubprop is not ok.  This restriction will be removed in a future release.' );

    $code = $codeParts[0];
    $subProp = $codeParts[1] ?? null;
    
    //..Get the property for this condition 
    $isEntity = true;
    if ( $subProp == null )
      $prop = $this->entityProps->getProperty( $code );
    else if ( isset( $this->linkedPropertySets[$code] ))
    {
      $prop = $this->linkedPropertySets[$code]->getProperty( $subProp );
      $isEntity = false;
    }
    else
      throw new SearchException( 'Invalid property name' );

    return [$prop, $isEntity];
  }
  
  
  /**
   * Creates the query builder output object
   * @param string $sql sql statement 
   * @param array $values binding values 
   * @return IQueryBuilderOutput output 
   */
  protected function createQueryBuilderOutput( ISearchQueryBuilder $builder, string $uniqueId, string $sql, array $values ) : IQueryBuilderOutput
  {
    return new MySQLQueryBuilderOutput( $builder, $uniqueId, $sql, $values );
  }
  
  
  private function buildConditionOperand( string $type, $value, int &$varIndex, array &$values ) : string
  {
    if ( is_array( $value ) && $type != 'in' )
      throw new \InvalidArgumentException( 'Cannot bind array value when operator is not "in".' );
    else if ( $value === null )
      return ' is null ';
    
    
    switch( $type )
    {
      case '=':
        $values[':VAR' . (++$varIndex)] = $value;
        return ' = :VAR' . ( $varIndex );
    
      case 'in':
        if ( !is_array( $value ))
          throw new SearchException( 'Value must be an array when using "in" conditions' );
        
        $parts = [];
        foreach( $value as $v )
        {
          $values[':VAR' . (++$varIndex)] = $v;
          $parts[] = ':VAR' . ( $varIndex );
        }
        
        return ' in (' . implode( ',', $parts ) . ') ';
        
        //return ' in ' . $this->dbc->prepareIn( $value, false );
      
    
      case 'like':
        $values[':VAR' . (++$varIndex)] = $value;
        return ' like :VAR' . ( $varIndex );
        
      case '>':
        $values[':VAR' . (++$varIndex)] = $value;
        return ' > :VAR' . ( $varIndex );
        
      case '>=':
        $values[':VAR' . (++$varIndex)] = $value;
        return ' >= :VAR' . ( $varIndex );
        
      case '<':
        $values[':VAR' . (++$varIndex)] = $value;
        return ' < :VAR' . ( $varIndex );
        
      case '<=':
        $values[':VAR' . (++$varIndex)] = $value;
        return ' <= :VAR' . ( $varIndex );

      default:
        throw new SearchException( 'Invalid operator' );
    }
  }
  
  
  /**
   * Retrieve a list of attribute id's for a list of attribute codes.
   * This is probably faster than doing extra table joins.
   * @param array $codes
   * @return array
   * @throws InvalidArgumentException
   */
  private function getAttributeIdListByCodes( array $codes ) : array
  {
    if ( empty( $codes ))
      return [];
    
    $sql = sprintf( 'select %s,%s from %s where %s in ' . $this->dbc->prepareIn( $codes, false ),
      $this->attrCols->getId(),
      $this->attrCols->getCode(),
      $this->attrRepo->getTable(),
      $this->attrCols->getCode());
    
    $out = [];
    foreach( $this->dbc->select( $sql, $codes ) as $row )
    {
      $out[$row[$this->attrCols->getCode()]] = $row[$this->attrCols->getId()];
    }
    
    /*
    //..Need to test this.
    foreach( $codes as $c )
    {
      if ( !isset( $out[$c] ))
        throw new \InvalidArgumentException( 'One of the supplied attribute codes does not exist' );
    }
    */
    
    return $out;    
  }
  
  
  private function hasSubconfig( string ...$propertyName ) : bool
  {
    foreach( $this->entityProps->getProperties( ...$propertyName ) as $prop )
    {
      /* @var $prop IProperty */
      if ( $prop->getFlags()->hasVal( IPropertyFlags::SUBCONFIG ))
      {
        return true;
      }
    }    
    
    return false;
  }
}
