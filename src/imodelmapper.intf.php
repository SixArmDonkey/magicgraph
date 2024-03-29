<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2019 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */

namespace buffalokiwi\magicgraph;

use buffalokiwi\magicgraph\property\IPropertySet;
use Exception;


/**
 * Defines an object that maps raw results from some data source to 
 * an IModel instance 
 */
interface IModelMapper
{
  /**
   * Maps a map of raw data (attribute => value) to an IModel instance.
   * @param array $data data to map
   * 
   * Optionally accepts an IPropertySet instance.  Supplied if passed to the IModelMapper
   * constructor.
   * f( ?IPropertySet $propertySet ) : IModel
   * 
   * @param \buffalokiwi\magicgraph\IPropertySet|null $propertySet An optional property set.
   * If this is passed, then the supplied IPropertySet is passed to the $createModel
   * Closure as the first argument.
   * 
   * @return IModel model instance 
   * @throws Exception if the create model callback does not return an instance of IModel 
   * 
   * @todo Create some code that can disable or remove properties in the produced model that are not 
   * fetched when the model is built.  Think about this a bit...
   */
  public function createAndMap( array $data, ?IPropertySet $propertySet = null ) : IModel;
  
  
  
  /**
   * Map some data to properties in some model.
   * Invalid properties are silently ignored.
   * @param IModel $model Model to push data into
   * @param array $data data to push
   */
  public function map( IModel $model, array $data ) : void;
  
  
  /**
   * Using the supplied IModelMap instances, this will convert
   * a model to an array, then convert the array property names based on the supplied mapping.
   * If no mappings are supplied, this simply returns IModel::toArray()
   * @param IModel $model
   * @return array
   */
  public function mapToArray( IModel $model ) : array;
  
  
  /**
   * Convert array keys from model to persistence or from persistence to model.
   * @param array $data
   * @param bool $isFromDB Set to true if keys in $data are from the persistence layer.  If keys are from the model, 
   * set to false.
   * @return array $data with converted keys 
   */
  public function convertArrayKeys( array $data, bool $isFromDB ) : array;
  
  
  /**
   * Retrieve the class or interface name this mapper works with.
   * @return string name 
   */
  public function getClass() : string;


  /**
   * Test that the supplied model implements the interface or class name returned
   * by getClass().
   * @param IModel ...$models One or more models to test
   * @return void
   * @throws Exception if the model does not implement the interface.
   */
  public function test( IModel ...$models ) : void;  
}
