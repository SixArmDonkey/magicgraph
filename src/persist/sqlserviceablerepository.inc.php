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

namespace buffalokiwi\magicgraph\persist;

use buffalokiwi\magicgraph\DBException;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\IModelPropertyProvider;
use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\ValidationException;
use Closure;


class SQLServiceableRepository extends SQLRepositoryProxy
{ 
  /**
   * Transaction factory 
   * @var ITransactionFactory
   */
  private ITransactionFactory $tfact;
  
  /**
   * Property providers 
   * @var IModelPropertyProvider[]
   */
  private array $providers = [];
  
  /**
   * Repo 
   * @var IRepository
   */
  private IRepository $repo;
  
  
  public function __construct( IRepository $repo, ITransactionFactory $tfact, IModelPropertyProvider ...$providers )
  {
    parent::__construct( $repo );
    
    $this->repo = $repo;
    $this->tfact = $tfact;    
    $this->addModelPropertyProvider( ...$providers );
  }
  
  
  protected final function & getProviders() : array
  {
    return $this->providers;
  }
  
  
  /**
   * Adds model property providers to the serviceable repository.
   * Limited to one provider per property name and based on getModelPropertyName() 
   * @param IModelPropertyProvider $providers Providers to add
   * @throws \InvalidArgumentException if the provider already exists
   * @final
   */
  public final function addModelPropertyProvider( IModelPropertyProvider ...$providers )
  {
    foreach( $providers as $p )
    {
      if ( isset( $this->providers[$p->getModelServiceConfig()->getModelPropertyName()] ))
        throw new \InvalidArgumentException( 'The list of providers may only contain a single provider per model property.  Model property ' . $p->getModelServiceConfig()->getModelPropertyName() . ' is already defined' );
      
      $this->providers[$p->getModelServiceConfig()->getModelPropertyName()] = $p;
    }
  }
  
  
  /**
   * Retrieve an IRunnable instance to be used with some ITransaction instance.
   * This runnable will execute the supplied function prior to saving the model.
   *
   * @param Closure $beforeSave What to run prior to saving f( IRepository, ...IModel )
   * @param Closure $afterSave What to run after saving f( IRepository, ...IModel )
   * @param IModel $models One or more models to save 
   * @return IRunnable
   */
  public function getSaveFunction( ?Closure $beforeSave, ?Closure $afterSave, IModel ...$models ) : array
  {
    $tasks = $this->repo->getSaveFunction( $beforeSave, $afterSave, ...$models );
    foreach( $this->providers as $p )    
    {
      foreach( $models as $model )
      {        
        foreach( $p->getSaveFunction( $model ) as $t )
        {          
          $tasks[] = $t;
        }
      }
    }
    
    return $tasks;
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
    $this->repo->save( $model, $validate );
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
    $this->tfact->execute( ...$this->getSaveFunction( null, null, ...$model ));
  }
}
