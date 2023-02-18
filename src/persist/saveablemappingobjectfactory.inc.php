<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */


namespace buffalokiwi\magicgraph\persist;

use buffalokiwi\magicgraph\DBException;
use buffalokiwi\magicgraph\IModel;
use buffalokiwi\magicgraph\IModelMapper;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertySet;
use buffalokiwi\magicgraph\ValidationException;
use Closure;


abstract class SaveableMappingObjectFactory extends MappingObjectFactory implements ISaveableObjectFactory
{
  private ESaveState $saveState;
  
  
  /**
   * What to do when saving the model.
   * ie: commit to some database or whatever.
   * @param IModel $model Model to save
   * @throws DBException
   */
  protected abstract function saveModel( IModel $model );
  
  
  public function __construct( IModelMapper $mapper, ?IPropertySet $properties = null )
  {
    parent::__construct( $mapper, $properties );
    $this->saveState = new ESaveState();
  }
  
  
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
    try {
      $this->test( $model );

      if ( $this->saveState->AFTER_SAVE())
      {
        //..If the model has been modified after calling after save, then we save again!
        if ( $model->hasEdits())
        {
          $model->validate();
          $this->saveModel( $model );
          $model->clearEditFlags();
        }
        return;
      }


      //..Do stuff before validation 
      $this->beforeValidate( $model );


      $this->saveState->VALIDATE;
      //..Validate the model 
      $model->validate();

      $this->beginTransaction();

      try {
        //..Do stuff before the save operation 
        $this->saveState->BEFORE_SAVE;
        
        //..Do stuff before the save operation 
        $bsRes = $this->runBeforeSave( $model );    

        //..Saves can be overridden in beforeSave
        //..Weird.
        if ( $bsRes === true )
          return;
        else if ( is_array( $bsRes ))
        {
          foreach( $bsRes as $r )
          {
            $bsModels[] = $r;
          }

          if ( !empty( $bsModels ))
          {
            array_unshift( $model );
            $this->saveState->NONE;
            //..Obviously, this can cause shenanigans.  
            $this->saveAll( ...$bsModels );
            return;
          }
        }        
        
        

        $this->saveState->SAVE;
        //..Save the model
        $this->saveModel( $model );

        //..Clear any edit flags after saving the model 
        $model->clearEditFlags();

        $this->saveState->AFTER_SAVE;
        //..Do stuff after saving the model 
        
        $this->runAfterSave( $model );
        
        

        
        //..If the model has been modified after calling after save, then we save again!
        if ( $model->hasEdits())
        {
          $model->validate();
          $this->saveModel( $model );
        }

        $this->commitTransaction();
      } catch( \Exception $e ) {
        $this->rollbackTransaction();
        throw $e;
      }
    } finally {
      $this->saveState->NONE;
    }
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
    try {
      $this->test( ...$model );

      if ( $this->saveState->AFTER_SAVE())
      {
        foreach( $model as $m )
        {
          //..If the model has been modified after calling after save, then we save again!
          if ( $m->hasEdits())
          {
            $m->validate();
            $this->saveModel( $m );
            $m->clearEditFlags();
          }
        }
      }    


      $this->beginTransaction();

      try {
        $this->saveState->VALIDATE;
        foreach( $model as $m )
        {
          $this->beforeValidate( $m );
          $m->validate();      
        }

        
        $bsModels = [];
        $this->saveState->BEFORE_SAVE;
        foreach( $model as $m )
        {
          //..Do stuff before the save operation 
          $bsRes = $this->runBeforeSave( $m );    
          
          //..Saves can be overridden in beforeSave
          //..Weird.
          if ( $bsRes === true )
            return;
          else if ( is_array( $bsRes ))
          {
            foreach( $bsRes as $r )
            {
              $bsModels[] = $r;
            }
            
            if ( !empty( $bsModels ))
            {
              array_unshift( $model );
              $this->saveState->NONE;
              //..Obviously this can cause shenanigans.  
              $this->saveAll( ...$bsModels );
              return;
            }
          }
        }

        $this->saveState->SAVE;
        foreach( $model as $m )
        {
          //..Save the model
          $this->saveModel( $m );
        }

        $this->saveState->AFTER_SAVE;
        foreach( $model as $m )
        {
          //..Do stuff after saving the model 
          $this->runAfterSave( $m );

          if ( $m->hasEdits())
          {
            $m->validate();
            $this->saveModel( $m );
          }      
        }

        $this->commitTransaction();
      } catch( \Exception $e ) {
        $this->rollbackTransaction();
        throw $e;
      }
    } finally {
      $this->saveState->NONE;
    }
  }


  /**
   * Called before the repo save call is made.
   * If this returns a bool and it is true, this will return and NOT save.
   * If this returns an array of IModel, the models will be validated, run through before save, then added to the list of models to save.
   */
  protected function beforeSave( IModel $model ) : null|bool|IModel
  {
    //..do nothing 
    return null;
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
   * @param Closure $beforeSave What to run prior to saving f( IRepository, ...IModel ) : void|bool|IModel[] 
   * 
   * If this returns a bool and it is true, this will return and NOT save.
   * If this returns an array of IModel, the models will be validated, run through before save, then added to the list of models to save.
   * 
   * 
   * @param Closure $afterSave What to run after saving f( IRepository, ...IModel )
   * @param IModel $models One or more models to save 
   * @return \Closure Function
   */
  protected final function getSaveClosure( ?Closure $beforeSave, ?Closure $afterSave, IModel ...$models ) : Closure
  {
    $this->test( ...$models );
    $self = $this;
    return function() use($beforeSave,$afterSave, $models,$self) {
      
      $bsResult = ( $beforeSave != null ) ? $beforeSave( $self, ...$models ) : null;
      
      if ( $bsResult === true )
        return null;
      else if ( is_array( $bsResult ))
      {
        foreach( $bsResult as $r )
        {
          if ( !( $r instanceof IModel ))
            throw new ValidationException( 'When returning an array from the beforeSave callback, all elements must be instances of ' . IModel::class );
          
          $models[] = $r;
        }
      }
      
      
      foreach( $models as $model )
      {
        $self->save( $model );
      }
      
      if ( $afterSave != null )
        $afterSave( $self, ...$models );
    };
  }
  
  
  /**
   * Retrieve an IRunnable instance to be used with some ITransaction instance.
   * This runnable will execute the supplied function prior to saving the model.
   *
   * @param Closure $beforeSave What to run prior to saving f( IRepository, ...IModel ) : void|bool|IModel[] 
   * 
   * If this returns a bool and it is true, this will return and NOT save.
   * If this returns an array of IModel, the models will be validated, run through before save, then added to the list of models to save.
   * 
   * @param Closure|null $afterSave What to run after saving f( IRepository, ...IModel )
   * @param Closure $getModels f() : IModel[]  Retrieve a list of models to save 
   * @return \Closure Function
   */
  protected final function getLazySaveClosure( ?Closure $beforeSave, ?Closure $afterSave, Closure $getModels ) : Closure
  {
    $self = $this;
    return function() use($beforeSave,$afterSave, $getModels ,$self) {
      $models = $getModels();
      $this->test( ...$models );
            
      $bsResult = ( $beforeSave != null ) ? $beforeSave( $self, ...$models ) : null;
      
      if ( $bsResult === true )
        return null;
      else if ( is_array( $bsResult ))
      {
        foreach( $bsResult as $r )
        {
          if ( !( $r instanceof IModel ))
            throw new ValidationException( 'When returning an array from the beforeSave callback, all elements must be instances of ' . IModel::class );
          
          $models[] = $r;
        }
      }
      
      foreach( $models as $model )
      {
        $self->save( $model );
      }
      
      if ( $afterSave != null )
        $afterSave( $self, ...$models );
    };
  }  
  
  
  protected function beginTransaction() : void
  {
    //..do nothing
  }
  
  
  protected function commitTransaction() : void
  {
    //..do nothing
  }
  
  
  protected function rollbackTransaction() : void
  {
    //..do nothing 
  }
  
  
  
  private function runBeforeSave( IModel $model ) : null|bool|array
  {
    $bsRes = $this->beforeSave( $model );
    
    if ( $bsRes === true )
      return null;
    else if ( is_array( $bsRes ))
    {
      foreach( $bsRes as $r )
      {
        if ( !( $r instanceof IModel ))
          throw new ValidationException( 'When returning an array from the beforeSave callback, all elements must be instances of ' . IModel::class );
      }
    }
    
    
    //..Run the property before save methods 
    foreach( $model->getPropertySet()->getConfigObjects() as $c )
    {
      /* @var $c IPropertyConfig */
      $psRes = $c->beforeSave( $model );
      if ( $psRes === true )
        return null;
      
      if ( is_array( $psRes ))
      {
        foreach( $psRes as $r )
        {
          if ( !( $r instanceof IModel ))
            throw new ValidationException( 'When returning an array from the beforeSave callback, all elements must be instances of ' . IModel::class );

          $bsRes[] = $r;
        }      
      }
    }
    
    return ( empty( $bsRes )) ? null : $bsRes;
  }
  
  
  private function runAfterSave( IModel $model ) : void
  {
    $this->afterSave( $model );
    
    //..Run the property before save methods 
    foreach( $model->getPropertySet()->getConfigObjects() as $c )
    {
      /* @var $c IPropertyConfig */
      $c->afterSave( $model );
    }
  }
}
