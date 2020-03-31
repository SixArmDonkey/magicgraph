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

use buffalokiwi\magicgraph\property\BasePropSvcCfg;
use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\persist\ISaveFunction;
use Closure;
use Exception;
use UI\Exception\InvalidArgumentException;


/**
 * A property service configuration for a one to many relationship.
 * 
 * The parent model is expected to have an array property named $arrayProperty and be bound to instances 
 * of the linked model.
 * The linked model must contain a property with the parent id 
 * The linked model must contain a single primary key property 
 * 
 * Use this with a OneManyPropertyService 
 * 
 * 
 */
class OneManyPropSvcCfg extends BasePropSvcCfg 
{
  /**
   * Parent Id property name
   * @var string 
   */
  private $parentIdProperty;
  
  /**
   * Parent array property name 
   * @var string
   */
  private $arrayProperty;
  
  /**
   * Linked model entity id property name 
   * @var string
   */
  private $entityProperty;
  
  /**
   * Linked model property name
   * @var string
   */
  private $idProperty;
  
  /**
   * Address repo 
   * @var IRepository
   */
  private $repo;
  
  /**
   * Retrieve the save function 
   * f( IModel ) : ISaveFunction 
   * @var Closure
   */
  private $saveFunc;
  
  private $beforeSave;
  private $afterSave;
  
  
  /**
   * Create a new OneManyPropSvcCfg instance 
   * 
   * NOTE: There is zero point to having the behavior classes tacked on to the end of this.
   * This property service has absolutely no way to utilize the behavior as behaviors need to be built along with the 
   * property set config when creating IPropertySet instances.  The behavior argument will be removed when I get time.
   * 
   * @param IRepository $repo Linked model repository 
   * @param string $parentIdProperty The parent model primary key property name.
   * @param string $arrayProperty The parent model property name for the array of linked models 
   * @param string $linkEntityProperty A linked model property that contains the parent id 
   * @param string $idProperty A linked model property containing the unique id of the linked model
   * @throws InvalidArgumentException
   */
  public function __construct( IRepository $repo, string $parentIdProperty, string $arrayProperty, string $linkEntityProperty, string $idProperty, ?\Closure $beforeSave = null, ?\Closure $afterSave = null, \buffalokiwi\magicgraph\property\INamedPropertyBehavior ...$behavior  )
  {
    parent::__construct( ...$behavior );
    if ( empty( $arrayProperty ))
      throw new InvalidArgumentException( 'arrayProperty must not be empty' );
    else if ( empty( $linkEntityProperty ))
      throw new InvalidArgumentException( 'linkEntityProperty must not be empty' );
    else if ( empty( $idProperty ))
      throw new InvalidArgumentException( 'idProperty must not be empty' );
    
    $this->repo = $repo;
    $this->parentIdProperty = $parentIdProperty;
    $this->arrayProperty = $arrayProperty;
    $this->entityProperty = $linkEntityProperty;
    $this->idProperty = $idProperty;
    $this->beforeSave = $beforeSave;
    $this->afterSave = $afterSave;
  }
  
  
  /**
   * Retrieve the property name used to load the backing model for a property service 
   * @return string name
   */
  public function getPropertyName() : string
  {
    return $this->parentIdProperty;
  }
  
  
  /**
   * Retrieve the property name used for the backing model for some property service.
   * @return string name
   */
  public function getModelPropertyName() : string
  {
    return $this->arrayProperty;
  }
  
  
  /**
   * Retrieve the mfg prop svc config array 
   * @return array config 
   */
  protected function createConfig() : array
  {
    return [];
  }
  
  
  
  /**
   * Retrieve a save function to be used with some transaction.
   * @param IModel $parent Model this provider is linked to 
   * @return array IRunnable[] Something the saves data 
   * @throws Exception
   */
  protected function createSaveFunction( IModel $parent ) : array
  {
    return (new OneManyPropertySaveFunction(
        $parent, 
        $this->repo, 
        $this->arrayProperty,
        $this->entityProperty,
        $this->idProperty,
        $this->beforeSave,
        $this->afterSave
    ))->getSaveFunction();
  }
}
