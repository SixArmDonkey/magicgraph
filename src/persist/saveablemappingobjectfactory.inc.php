<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\persist;

use buffalokiwi\magicgraph\DBException;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\ValidationException;
use Closure;


abstract class SaveableMappingObjectFactory extends MappingObjectFactory implements ISaveableObjectFactory
{
  /**
   * What to do when saving the model.
   * ie: commit to some database or whatever.
   * @param IModel $model Model to save
   * @throws DBException
   */
  protected abstract function saveModel( IModel $model );
  
  
  /**
   * Save some record.
   * If the primary key value is specified, this is considered to be an update.
   * Otherwise, this is considered to be an insert.
   * 
   * @param IModel $model Model to save 
   * @throws DBException For DB errors 
   * @throws ValidationException if the model fails to validate 
   */
  public function save( IModel $model ) : void
  {
    $this->test( $model );
        
    //..Do stuff before validation 
    $this->beforeValidate( $model );
    
    //..Validate the model 
    $model->validate();
    
    //..Do stuff before the save operation 
    $this->runBeforeSave( $model );    
    
    //..Save the model
    $this->saveModel( $model );
    
    //..Do stuff after saving the model 
    $this->runAfterSave( $model );
  }      
  
  
  /**
   * Saves a batch of records.
   * All records are first validated, then saved sequentially.
   * Validation exceptions will be thrown prior to any saves happening.
   * 
   * If the primary key value is specified, this is considered to be an update.
   * Otherwise, this is considered to be an insert.
   * 
   * This is split into 3 batches (iterations of supplied models):
   * 
   * 1) beforeValidate, validate and beforeSave
   * 2) saveModel
   * 3) afterSave 
   * 
   * 
   * 
   * @param IModel $model Model to save 
   * @throws DBException For DB errors 
   * @throws ValidationException if the model fails to validate 
   */
  public function saveAll( IModel ...$model ) : void
  {
    $this->test( ...$model );
    
    foreach( $model as $m )
    {
      $this->beforeValidate( $m );
      $m->validate();      
      
      //..Do stuff before the save operation 
      $this->runBeforeSave( $m );    
    }
    
    foreach( $model as $m )
    {
      //..Save the model
      $this->saveModel( $m );
    }
    
    
    foreach( $model as $m )
    {
      //..Do stuff after saving the model 
      $this->runAfterSave( $m );
    }
  }


  /**
   * Called before the repo save call is made.
   */
  protected function beforeSave( IModel $model ) : void
  {
    //..do nothing 
  }
  
  
  /**
   * Called after the repo save call is made 
   */
  protected function afterSave( IModel $model ) : void
  {
    //..do nothing 
  }
  
  
  /**
   * Called before the validate call is made 
   */
  protected function beforeValidate( IModel $model ) : void
  {
    //..do nothing 
  }
  
  
  /**
   * Retrieve an IRunnable instance to be used with some ITransaction instance.
   * This runnable will execute the supplied function prior to saving the model.
   *
   * @param Closure $beforeSave What to run prior to saving f( IRepository, ...IModel )
   * @param Closure $afterSave What to run after saving f( IRepository, ...IModel )
   * @param IModel $models One or more models to save 
   * @return \Closure Function
   */
  protected final function getSaveClosure( ?Closure $beforeSave, ?Closure $afterSave, IModel ...$models ) : Closure
  {
    $this->test( ...$models );
    $self = $this;
    return function() use($beforeSave,$afterSave, $models,$self) {
      if ( $beforeSave != null && $beforeSave( $self, ...$models ) === true )
        return;
      
      foreach( $models as $model )
      {
        $self->save( $model );
      }
      
      if ( $afterSave != null )
        $afterSave( $self, ...$models );
    };
  }
  
  
  private function runBeforeSave( IModel $model ) : void
  {
    $this->beforeSave( $model );
    
    //..Run the property before save methods 
    foreach( $model->getPropertySet()->getConfigObjects() as $c )
    {
      /* @var $c \buffalokiwi\magicgraph\property\IPropertyConfig */
      $c->beforeSave( $model );
    }
  }
  
  
  private function runAfterSave( IModel $model ) : void
  {
    $this->afterSave( $model );
    
    //..Run the property before save methods 
    foreach( $model->getPropertySet()->getConfigObjects() as $c )
    {
      /* @var $c \buffalokiwi\magicgraph\property\IPropertyConfig */
      $c->afterSave( $model );
    }
  }
}
