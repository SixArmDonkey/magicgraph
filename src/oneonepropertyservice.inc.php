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

namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertySvcConfig;
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
  
  
  public function __construct()
  {
    $args = func_get_args();
    $num = func_num_args();
    
    if ( $num == 1 )
      $this->__constructnew( ...$args );
    else if ( $num == 2 )
      $this->__constructold( ...$args );
    else
      throw new InvalidArgumentException( 'Constructor accepts one or two arguments' );
  }
  
  
  public function __constructnew( IOneOnePropSvcCfg $cfg )
  {
    parent::__construct( $cfg );
    $this->repo = $cfg->getRepository();
  }
  
  
  /**
   * Create a new property service 
   * @param IPropertyConfig $cfg
   * @param string $name
   * @param IRepository $repo
   * @param string $modelPropertyName Optional model property name. 
   */
  public function __constructold( IPropertySvcConfig $cfg, IRepository $repo )
  {
    parent::__construct( $cfg );
    $this->repo = $repo;
  }
  
  /**
   * If this relationship provider is backed by a repository, it will be returned here.
   * @return IRepository|null
   */
  public function getRepository() : ?IRepository
  {
    return $this->repo;
  }
  
  
 
  
  protected function onSave( IModel $model ) : array
  {
    return $this->repo->getSaveFunction( null, null, $model );
  }
  
  
  /**
   * Loads an item from somewhere by id.
   * If $id == 0, then this must return an empty model.
   * @param mixed $id Id 
   * @return IModel Model 
   */
  protected function loadById( mixed $id ) : IModel
  {
    if ( empty( $id ))
      return $this->repo->create( [] );
    else
      return $this->repo->get((string)$id );
  }
}
