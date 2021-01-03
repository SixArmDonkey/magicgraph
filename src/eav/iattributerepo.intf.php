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

namespace buffalokiwi\magicgraph\eav;

use buffalokiwi\magicgraph\DBException;
use buffalokiwi\magicgraph\persist\IRepository;
use buffalokiwi\magicgraph\persist\RecordNotFoundException;
use buffalokiwi\magicgraph\search\ISearchQueryBuilder;
use InvalidArgumentException;


/**
 * Defines an attribute repo for working with attributes and attribute groups.
 */
interface IAttributeRepo
{ 
  /**
   * Retrieve a list of all enabled attribute codes keyed by attribute id.
   * @return array [id => code]
   */
  public function getAttributeCodes() : array;  
  
  
  /**
   * Retrieve an attribute by code/name
   * @param string $name code/name 
   * @return IAttribute Attribute
   */
  public function getAttributeByName( string $name ) : IAttribute;
  
  
  /**
   * Retrieve a list of attributes by a name list.
   * @param string $nameList name list 
   * @return array attributes
   */    
  public function getAttributesByNameList( string ...$nameList ) : array;
  
  
  /**
   * Retrieves the attribute group id with the lowest id value.
   * This is the default.
   * @return int
   */
  public function getDefaultAttributeGroupId() : int;
  
  /**
   * Access the attribute repo for individual attribute CRUD.
   * @return IRepository
   */
  public function getAttributeRepo() : IRepository;
  
  
  /**
   * Retrieve the attribute value repository 
   * @return IRepository Repo
   */
  public function getValueRepo() : IRepository;
  
  
  /**
   * Retrieve the attribute group repo 
   * @return IRepository repo 
   */
  public function getGroupRepo() : IRepository;
  
  
  /**
   * Retrieve the attribute group link repo 
   * @return IRepository repo 
   */
  public function getGroupLinkRepo() : IRepository;
  
  
  /**
   * Create a new and empty attribute group record for creating new attribute
   * groups.
   * @return IAttributeGroup
   */
  public function createEmptyAttributeGroup() : IAttributeGroup;
  
  /**
   * Retrieve some attribute group by id.  
   * @param int $id group id 
   * @return IAttributeGroup 
   * @throws RecordNotFoundException if the group does not exist 
   */
  public function getAttributeGroup( int $id ) : IAttributeGroup;
  
  
  /**
   * Retrieve the attributes for some group by group id.
   * @param int $id Attribute group id 
   * @return array IAttribute[] attributes belonging to that group
   * @throws RecordNotFoundException if the group does not exist 
   */
  public function getAttributesForGroup( int $id ) : array;
  
  
  /**
   * Retrieve a map of attribute group id => attributes for any supplied
   * attribute group ids 
   * @param array $ids Attribute group id list 
   * @return array [group id] => IAttributeGroup 
   * The returned attribute group model will have a list of IAttribute models
   * attached to it via IAttributeGroup::attachAttributes().
   * @throws InvalidArgumentException if any elements in $ids is not an integer 
   */
  public function getAttributesForGroupIdList( array $ids ) : array;
  
  
  /**
   * Saves some attribute group and associated links.
   * @param IAttributeGroup $group
   * @return void
   * @throws DBException
   */
  public function saveAttributeGroup( IAttributeGroup $group ) : void;
  
  
  /**
   * Retrieve a list of attribute values for some list of entity id's.
   * @param int $entityId One or more entity ids 
   * @return array [entity id => [attr id => value]]
   * @throws InvalidArgumentException 
   */
  public function getAttributeValues( int ...$entityId ) : array;   
  
  
  /**
   * Test to see if some attribute exists.
   * @param string $code Attribute code
   * @return bool exists
   */
  public function attributeExists( string $code ) : bool;
  
  
  /**
   * Given some list of codes find out of each exists or not.
   * @param string $codes List of codes
   * @return array [code => bool exists] results
   */
  public function existsReport( string ...$codes ) : array;
}
