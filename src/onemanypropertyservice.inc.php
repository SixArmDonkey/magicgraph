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

use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\property\IPropertyConfig;
use buffalokiwi\magicgraph\property\IPropertySvcConfig;
use InvalidArgumentException;


/**
 * Contains some of the base programming for a property service backed by a single 
 * repository and an array property full of IModel instances.
 * 
 * @todo Consider adding the ability to add search functionality to this.
 */
class OneManyPropertyService extends AbstractOneManyPropertyService
{  
  /**
   * Repo 
   * @var IRepository 
   */
  private $repo;
  
  /**
   * Property name from repo that this queries
   * @var string
   */
  private $foreignKey;
  
  
  /**
   * @param IOneManyPropSvcCfg Property Service config 
   */
  public function __construct() {
    $args = func_get_args();
    $num = func_num_args();
    
    if ( $num == 1 )
      $this->__constructnew( ...$args );
    else if ( $num == 3 )
      $this->__constructold( ...$args );
    else
      throw new InvalidArgumentException( 'Constructor accepts one or three arguments' );
  }
  
  
  public function __constructnew( IOneManyPropSvcCfg $cfg )
  {
    parent::__construct( $cfg );
    
    $this->repo = $cfg->getRepository();
    $this->foreignKey = $cfg->getForeignKey();
  }
  
  
  /**
   * Create a new property service 
   * @param IPropertyConfig $cfg
   * @param string $name property name containing the model
   * @param IRepository $repo Linked model repository
   * @param string $foreignKey Property name from supplied IRepository that is 
   * queried against IPropertySvcConfig::getPropertyName();
   * @param string $modelPropertyName Optional model property name. 
   */
  public function __constructold( IPropertySvcConfig $cfg, IRepository $repo, string $foreignKey )
  {
    parent::__construct( $cfg );
    $this->repo = $repo;
    $this->foreignKey = $foreignKey;
  }
  
  
  /**
   * If this relationship provider is backed by a repository, it will be returned here.
   * @return IRepository|null
   */
  public function getRepository() : ?IRepository
  {
    return $this->repo;
  }
  
  
  
  protected function create( array $data ) : IModel
  {
    return $this->repo->create( $data );
  }  
  
  
  protected function loadModels( int $parentId, IModel $parent ) : array
  {
    return $this->repo->getForProperty( $this->foreignKey, (string)$parentId );
  }
}
