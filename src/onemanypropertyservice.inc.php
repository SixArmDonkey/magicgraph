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
   * Create a new property service 
   * @param IPropertyConfig $cfg
   * @param string $name
   * @param IRepository $repo
   * @param string $foreignKey Property name from supplied IRepository that is 
   * queried against IPropertySvcConfig::getPropertyName();
   * @param string $modelPropertyName Optional model property name. 
   */
  public function __construct( IPropertySvcConfig $cfg, IRepository $repo, string $foreignKey )
  {
    parent::__construct( $cfg );
    $this->repo = $repo;
    $this->foreignKey = $foreignKey;
  }
  
  
  protected function loadModels( int $parentId ) : array
  {
    return $this->repo->getForProperty( $this->foreignKey, (string)$parentId );
  }
}
