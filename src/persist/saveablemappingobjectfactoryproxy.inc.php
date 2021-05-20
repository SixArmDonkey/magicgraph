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
use buffalokiwi\magicgraph\ValidationException;


/**
 * A proxy/decorator base class for the ISaveableObjectFactory interface 
 */
class SaveableMappingObjectFactoryProxy extends ObjectFactoryProxy implements ISaveableObjectFactory
{
  /**
   * Factory 
   * @var ISaveableObjectFactory 
   */
  private $factory;
  
  /**
   * Create a new proxy instance 
   * @param IObjectFactory $factory
   */
  public function __construct( ISaveableObjectFactory $factory )
  {
    parent::__construct( $factory );
    $this->factory = $factory;
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
    $this->factory->save( $model );
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
    $this->factory->saveAll( ...$model );
  }
   
  
  /**
   * Removes a model.  
   * @param IModel $model Model to remove 
   * @throws DBException For db errors
   * @throws RecordNotFoundException if the primary key is missing or the record
   * could not be found.
   */
  public function remove( IModel $model ) : void
  {
    $this->factory->remove( $model );
  }
  
  
  /**
   * Remove an entry by id.  This does not work for compound keys.
   * @param string $id id 
   * @return void
   * @throws DBException
   */
  public function removeById( string $id ) : void
  {
    $this->factory->removeById( $id );
  }
}
