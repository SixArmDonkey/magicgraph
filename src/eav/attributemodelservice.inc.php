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

use buffalokiwi\magicgraph\DBException;
use buffalokiwi\magicgraph\eav\search\IAttributeSearch;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\persist\IRunnable;
use buffalokiwi\magicgraph\persist\ITransactionFactory;
use buffalokiwi\magicgraph\persist\RecordNotFoundException;
use buffalokiwi\magicgraph\persist\RepositoryProxy;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertyFlags;
use buffalokiwi\magicgraph\property\QuickPropertyConfig;
use buffalokiwi\magicgraph\search\ISearchQueryBuilder;
use buffalokiwi\magicgraph\search\ISearchQueryGenerator;
use buffalokiwi\magicgraph\search\ISearchResults;
use buffalokiwi\magicgraph\search\SearchException;
use buffalokiwi\magicgraph\ValidationException;
use Exception;
use InvalidArgumentException;


/**
 * Attribute service is responsible for attribute model crud, and assembling
 * complete instances (with service providers) of IModel.
 * 
 * @todo Turn this into a ProxyRepo and wrap the model repo.
 * It should be simple to add this shit to any model.
 * 
 * @todo Test this in more detail.  There are a few quirks that need to be sorted out.
 */
class AttributeModelService extends RepositoryProxy implements IAttributeModelService
{
  /**
   * Attribute repository 
   * @var IAttributeRepo
   */
  private IAttributeRepo $attrRepo;
  
  /**
   * A factory for creating ITransaction instances from IRunnable 
   * @var ITransactionFactory 
   */
  private ITransactionFactory $tfact;
  
  /**
   * Attribute config cache.
   * Building config arrays from IAttribute is expensive.
   * @var array 
   */
  private array $attrConfigCache = [];
  
  /**
   * Search 
   * @var IAttributeSearch|null
   */
  private ?IAttributeSearch $search;

  private ?IAttributeModelStrategyProcessor $saveStrategy;
  
  /**
   * Entity repo
   * @var IRepository  
   */
  private IRepository $entityRepo;
  
  
  /**
   * AttributeModelService 
   * @param IRepository $repo
   * @param IAttributeRepo $attrRepo
   * @param ITransactionFactory $tFact
   * @param IAttributeSearch $search
   */
  public function __construct( IRepository $repo, IAttributeRepo $attrRepo, ITransactionFactory $tFact, ?IAttributeSearch $search = null, ?IAttributeModelStrategyProcessor $saveStrategy = null )
  {
    parent::__construct( $repo );
    $this->attrRepo = $attrRepo;
    $this->tfact = $tFact;
    $this->entityRepo = $repo;
    $this->search = $search;    
    $this->saveStrategy = $saveStrategy;
  }
  
  
  public function create( array $data = [], bool $readOnly = false ) : IModel
  {
    return $this->buildModel( parent::create( $data, $readOnly ));
  }
  
  
  /**
   * Retrieve user records by page.
   * @param int $page Page number  
   * @param int $size page size 
   * @return array IModel[] Users 
   */
  public function getPage( int $page, int $size = 25, string $orderBy = '' ) : array
  {
    $out = [];
    foreach( parent::getPage( $page, $size, $orderBy ) as $p )
    {
      $out[] = $this->buildModel( $p );
    }
    return $out;
  }
  
  
  public function get( string ...$id ) : IModel
  {    
    return $this->buildModel( parent::get( ...$id ));
  }
  
  
  public function getAll( array $idList ) : array
  { 
    $out = [];
    foreach( parent::getAll( $idList ) as $m )
    {
      $out[] = $this->buildModel( $m );
    }
    
    return $out;
  }
    
  
  
  /**
   * Save some record.
   * If the primary key value is specified, this is considered to be an update.
   * Otherwise, this is considered to be an insert.
   * 
   * @param IModel $model Model to save 
   * @param bool $validate Validate the model prior to save 
   * @throws DBException For DB errors 
   * @throws ValidationException if the model fails to validate 
   */
  public function save( IModel $model, bool $validate = true ) : void
  {
    $this->tfact->execute( ...$this->getSaveTasks( $model ));
  }
  
  
  /**
   * Saves a batch of records.
   * All records are first validated, then saved sequentially.
   * Validation exceptions will be thrown prior to any saves happening.
   * 
   * If the primary key value is specified, this is considered to be an update.
   * Otherwise, this is considered to be an insert.
   * 
   * @param IModel $model Model to save 
   * @throws DBException For DB errors 
   * @throws ValidationException if the model fails to validate 
   */
  public function saveAll( IModel ...$model ) : void
  {  
    $tasks = [];
    foreach( $model as $m )
    {
      foreach( $this->getSaveTasks( $m ) as $t )
      {
        $tasks[] = $t;
      }
    }
    
    $this->tfact->execute( ...$tasks );
  }
  
  
  
  /**
   * Retrieve the search query generator 
   * @return ISearchQueryGenerator generator 
   */
  public function getSearchQueryGenerator() : ISearchQueryGenerator
  {
    return $this->search->getSearchQueryGenerator();
  }
  
  
  /**
   * Search for things.
   * An attribute search will search for entity or attribute values, and return a map of attribute code => value.
   * These results can be used to build models.
   * @param ISearchQueryBuilder $builder Search builder
   * @return ISearchResults results 
   */
  public function search( ISearchQueryBuilder $builder ) : ISearchResults
  {
    if ( $this->search == null )
      throw new SearchException( 'Not implemented.  Pass an instance of ' . IAttributeSearch::class . ' to the ' . static::class . ' constructor to enable searching.' );
    
    //..If no filters are present, the search generator will throw an exception
    //  Here we simply return a page without using the search query generator
    if ( empty( $builder->getConditionAttributes()))
    {
      return new \buffalokiwi\magicgraph\search\MySQLSearchResults( $builder->getPage(), $builder->getResultSize(), function() {
        return $this->count( true );
      }, ...$this->getPage( $builder->getPage(), $builder->getResultSize()));
    }
    
    return $this->search->search( $builder );
  }
  
  
  /**
   * Retrieve a list of tasks for saving attributes.
   * @param IAttributeModel $model
   * @return IRunnable[] tasks 
   */
  public function getSaveTasks( IModel $model ) : array
  {
    if ( !( $model instanceof IAttributeModel ))
      throw new InvalidArgumentException( 'model must be an instance of ' . IAttributeModel::class );
        
    $saveStrategy = $this->saveStrategy;    
    
    $attrValues = [];
    $names = [];
    
    $getNames = function() use (&$names) {
      return $names;
    };
    
    //..This feels a bit better
    
    //..Tasks are executed in the order they are entered, so this should work.
    //..The idea is to take the edited column names from the before save event and cache them
    //..The edit flags are cleared prior to after save being called so we use the cache to determine which attribute
    //  values to save 
    
    $tasks = array_merge(
      $this->getSaveFunction( 
        function( IRepository $repo, IAttributeModel ...$models ) use($saveStrategy) {
          //..Do we want to do this?
          foreach( $models as $model )
          {
            if ( $model->getAttrGroupId() < 1 )
              $model->setAttrGroupId( $this->attrRepo->getDefaultAttributeGroupId());
          }
          
          $saveStrategy?->processBeforeSaveAttributeModel( $repo, ...$models );          
        }, 
        function( IRepository $repo, IAttributeModel ...$models ) use(&$attrValues,&$names,$saveStrategy) {
          foreach( $models as $model )
          {


            foreach( $this->getAttrValuesToSave( $model ) as $k => $v )
            {
              $attrValues[$k] = $v;
              $names[$k] = $k;
            }
          }
          
          $saveStrategy?->processAfterSaveAttributeModel( $repo, ...$models );
        }, 
        $model 
      ),

      //..This the second set of save operations that occur after the product is saved 
      //..The lazy save function lets us determine which models to save AFTER before save and the save event have occurred
      $this->attrRepo->getValueRepo()->getLazySaveFunction( 
        function(IRepository $repo, IAttrValue ...$attrValues ) use($getNames,$model,$saveStrategy) {
          $names = $getNames();
          $attrsByName = $this->attrRepo->getAttributesByNameList( ...array_values( $names ));


          foreach( $attrValues as $k => $val )
          {
            /* @var $val IAttrValue */
            //..This really should not be necessary.  Seems to be a different issue...
            if ( $val->getEntityId() == 0 )
              $val->setEntityId( $model->getId());

            if ( $val->getAttributeId() < 1 )
            {
              $name = $names[$k];
              if ( isset( $attrsByName[$name] ))
                $attr = $attrsByName[$name];
              else
                $attr = $this->attrRepo->getAttributeByName( $name );

              $val->setAttributeId( $attr->getId());
            }          
          }
          
          $saveStrategy?->processBeforeSaveAttributeValue( $repo, ...$attrValues );
        }, 
                
        function(IRepository $repo, IAttrValue ...$attrValues ) use($saveStrategy) {
          $saveStrategy?->processAfterSaveAttributeValue( $repo, ...$attrValues );
        },      
                
        function() use(&$attrValues) { return $attrValues; } 
      )
    );
      
      
    return $tasks;
  }
  
  
  /**
   * Retrive the attribute repository 
   * @return IAttributeRepo Attribute repo
   */
  public final function getAttributeRepo() : IAttributeRepo
  {
    return $this->attrRepo;
  }
  
  
  private function getAttributes( int $id )
  {
    foreach( $this->attrRepo->getAttributeValues( $id ) as $data )
    {
      return $data;
    }
    
    return [];
  }
  
  
  private function getAttrValuesToSave( IModel $model )
  {
    $attrValues = [];
    
    $set = $model->getPropertySet();
    
    foreach( $model->getPropertyNameSet()->getMembers() as $name )
    {
      $prop = $set->getProperty( $name );
      /* @var IProperty $prop */
      
      
      
      if ( !$prop->getFlags()->hasVal( IPropertyFlags::SUBCONFIG ))
      {
        continue;
      }
      
      
      $val = $this->attrRepo->getValueRepo()->create( [] );
      if ( !( $val instanceof IAttrValue ))
        throw new Exception( 'Attribute Value Repo must return instances of IAttrValue' );
      
      /* @var $val IAttrValue */
      
      $val->setAttributeId( $prop->getId());
      $val->setEntityId( $model->getId());
      $val->setAttrValue((string)$model->getValue( $name ));
      
      $attrValues[$prop->getName()] = $val;      
    }
    
    
    return $attrValues;
  }
  
  
  /**
   * This must be called for any product being returned from the service.
   * @param IAttributeModel $model
   * @param array string[] $attrWhiteList A list of additional attribute names to include in the returned model. 
   * @return IModel
   * @todo There is most likely a more efficient way to do this.  Profile this section.
   */
  protected final function buildModel( IAttributeModel $model, array $attrWhiteList = [] ) : IModel
  {
    if ( empty( $model->getAttrGroupId()))
      return $model; //..Nothing to do.
    
    //..Load the attributes based on the current attribute group id.
    try {
      $attributes = $this->attrRepo->getAttributesForGroup( $model->getAttrGroupId());
    } catch( RecordNotFoundException $e ) {
      //..Is this message really necessary?
      //  This is potential log spam, and probably not a big deal.
      //trigger_error( $e->getMessage(), E_USER_WARNING );
      $attributes = [];
    }
    
    
    //..Attribute configuration array 
    $ps = $model->getPropertySet();
    
    //..Get the base configuration 
    $baseConfig = [];
    
    foreach( $ps->getConfigObjects() as $c )
    {
      /* @var $c IPropertyConfig */
      foreach( $c->getConfig() as $k => $v )
      {
        $baseConfig[$k] = $v;
      }
    }
    
    //..Build the dynamic configuration, but merge with base config if the properties exist 
    $config = [];
    
    //..Build the dynamic attribute config 
    foreach( $attributes as $a )
    {      
      /* @var $a IAttribute */
      //if ( !$ps->isMember( $a->getCode()))
      //..If the profiler catches this, i'll optimize it.
      if ( !empty( $attrWhiteList ) && !in_array( $a->getCode(), $attrWhiteList ))
        continue;
      
      if ( !isset( $this->attrConfigCache[$a->getCode()] ))
        $this->attrConfigCache[$a->getCode()] = $a->toConfigArray();
      
      $acfg = $this->attrConfigCache[$a->getCode()];
      
      foreach( $acfg as $name => $data )
      {
        if ( isset( $baseConfig[$name] ))
          $config[$name] = array_merge( $baseConfig[$name], $data );
        else
          $config[$name] = $data;        
      }
    }

    
    //..Add the new properties to the model 
    $model->getPropertySet()->addPropertyConfig( new QuickPropertyConfig( $config ));
    
    
    foreach( $this->getAttributes( $model->getId()) as $code => $value )
    {
      $model->setValue( $code, $value );
    }
    
    return $model;
  }
}
