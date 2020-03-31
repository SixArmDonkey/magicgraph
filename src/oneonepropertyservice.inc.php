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

namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertySvcConfig;
use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\persist\IRunnable;
use buffalokiwi\magicgraph\persist\RecordNotFoundException;
use InvalidArgumentException;


/**
 * Contains some of the base programming for a property service backed by a single 
 * repository and model.
 */
class OneOnePropertyService extends AbstractOneOnePropertyService implements IModelPropertyProvider
{  
  /**
   * Repo 
   * @var IRepository 
   */
  private $repo;
  
  /**
   * Create a new property service 
   * @param IPropertyConfig $cfg
   * @param string $name
   * @param IRepository $repo
   * @param string $modelPropertyName Optional model property name. 
   */
  public function __construct( IPropertySvcConfig $cfg, IRepository $repo )
  {
    parent::__construct( $cfg );
    $this->repo = $repo;
  }
  
  
  protected function onSave( IModel $model ) : array
  {
    return $this->repo->getSaveFunction( null, null, $model );
  }
  
  
  /**
   * Loads an item from somewhere by id.
   * If $id == 0, then this must return an empty model.
   * @param int $id Id 
   * @return IModel Model 
   */
  protected function loadById( int $id ) : IModel
  {
    if ( empty( $id ))
      return $this->repo->create( [] );
    else
      return $this->repo->get((string)$id );
  }
}
