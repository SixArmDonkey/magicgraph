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
use buffalokiwi\magicgraph\ValidationException;


interface ISaveableObjectFactory extends IObjectFactory
{
  /**
   * Save some record.
   * If the primary key value is specified, this is considered to be an update.
   * Otherwise, this is considered to be an insert.
   * 
   * @param IModel $model Model to save 
   * @throws DBException For DB errors 
   * @throws ValidationException if the model fails to validate 
   */
  public function save( IModel $model ) : void;
  
  
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
  public function saveAll( IModel ...$model ) : void;
  
  
  /**
   * Removes a model.  
   * @param IModel $model Model to remove 
   * @throws DBException For db errors
   * @throws RecordNotFoundException if the primary key is missing or the record
   * could not be found.
   */
  public function remove( IModel $model ) : void;  
  
  /**
   * Remove an entry by id.  This does not work for compound keys.
   * @param string $id id 
   * @return void
   * @throws DBException
   */
  public function removeById( string $id ) : void;  
}


