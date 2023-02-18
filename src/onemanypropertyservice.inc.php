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
  
  private array $conditions = [];
  private int $limit = 100;
  
  
  /**
   * @param IOneManyPropSvcCfg Property Service config 
   */
  public function __construct() {
    $args = func_get_args();
    $num = func_num_args();
    
    if ( empty( $args ))
      throw new InvalidArgumentException( 'Constructor accepts one or three arguments' );
        
    $arg0 = ( $args[0] instanceof IOneManyPropSvcCfg ) && ( !isset( $args[1] ) || is_array( $args[1] ));
    $arg1 = ( isset( $args[1] ) && is_array( $args[1] ));
           
    if ( $arg0 || ( $arg0 && $arg1 ))
      $this->__constructnew( ...$args );
    else
      $this->__constructold( ...$args );
  }
  
  
  public function __constructnew( IOneManyPropSvcCfg $cfg, array $conditions = [], int $limit = 100 )
  {
    parent::__construct( $cfg );
    
    $this->repo = $cfg->getRepository();
    $this->foreignKey = $cfg->getForeignKey();
    
    if ( $limit > 0 )
      $this->limit = $limit;
    
    $props = $this->repo->createPropertySet();
    
    foreach( $conditions as $k => $v )
    {
      if ( !$props->isMember( $k ))
        throw new \InvalidArgumentException( $k . ' is not a valid member of this property set' );
      
      $this->conditions[$k] = $v;
    }
  }
  
  
  /**
   * Create a new property service 
   * @param IPropertyConfig $cfg
   * @param string $name property name containing the model
   * @param IRepository $repo Linked model repository
   * @param string $foreignKey Property name from supplied IRepository that is 
   * queried against IPropertySvcConfig::getPropertyName();
   * @param string $modelPropertyName Optional model property name. 
   * @deprecated To be removed when I update composition root
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
    $cond = $this->conditions;
    $cond[$this->foreignKey] = (string)$parentId;
    
    return $this->repo->findByProperties( $cond, $this->limit );
  }
}
