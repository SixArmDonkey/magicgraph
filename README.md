# BuffaloKiwi Magic Graph
  
**Behavioral-based object modeling, mapping and persistence library for PHP 8**  
  
OSL 3.0 License
  
---

## Table of Contents

[Generated Documentation](https://sixarmdonkey.github.io/magicgraph/)

Documentation is a work in progress.

1. [Overview](#overview)
2. [Installation](#installation)
3. [Dependencies](#dependencies)
4. [Definitions](#definitions)
5. [Getting Started](#getting-started)
    1. [Hello Model](#hello-model)
    2. [Basic Database and Repository Setup](#basic-database-and-repository-setup)
6. [Property Configuration](#property-configuration)
    1. [Property Configuration Array Attributes](#property-configuration-array-attributes)
    2. [Property Data Types](#property-data-types)
    3. [Property Flags](#property-flags)
    4. [Property Behavior](#property-behavior)
    5. [Quick Models](#quick-models)
    6. [Annotations](#annotations)
7. [Repositories](#repositories)
    1. [Mapping Object Factory](#mapping-object-factory)
    2. [Saveable Mapping Object Factory](#saveable-mapping-object-factory)
    3. [SQL Repository](#sql-repository)
    4. [Decorating Repositories](#decorating-repositories)
    5. [Serviceable Repository](#serviceable-repository)
    6. [Composite Primary Keys](#composite-primary-keys)
8. [Transactions](#transactions)
    1. [Overview](#transactions-overview)
    2. [Creating a Transaction](#creating-a-transaction)
    3. [Transaction Factory](#transaction-factory)
9. [Model service providers](#model-relationship-providers)
    1. [Serviceable Model](#serviceable-model)
    2. [Serviceable Repository](#serviceable-repository)
10. [Relationships](#relationships)
    1. [One to One](#one-to-one)
    2. [One to Many](#one-to-many)
    3. [Many to Many](#many-to-many)
    4. [Nested Relationship Providers](#nested-relationship-providers)
    5. [How Editing and Saving Works](#how-editing-and-saving-works)
11. [Extensible Models](#extensible-models)
    1. [Property configuration interface](#property-configuration-interface)
    2. [Property configuration implementation](#property-configuration-implementation)
    3. [Using multiple property configurations](#using-multiple-property-configurations)
    4. [Model interface](#model-interface)
    5. [Model implementation](#model-implementation)
12. [Behavioral Strategies ](#behavioral-strategies)
13. [Database Connections](#database-connections)
    1. [PDO](#pdo)
    2. [MySQL PDO](#mysql-pdo)
    3. [Connection Factories](#connection-factories)
14. [Working with Currency](#working-with-currency)
15. [Creating HTML elements](#creating-html-elements)
16. [Magic Graph Setup](#magic-graph-setup)
17. [Entity-Attribute-Value (EAV)](#entity-attribute-value)
18. Searching
19. Extending Magic Graph 
    1. [The Config Mapper](#the-config-mapper)
20. Tutorial

---
  

## Overview

Magic graph is an object mapping and persistence library written in pure PHP.  Magic Graph makes it easy to design 
and use rich hierarchical domain models, which may incorporate various independently designed and tested behavioral 
strategies.  
  
  
**Persistence**

Persistence is optional, and it's possible to create object factories without using the persistence package.

Magic Graph persistence uses the repository and unit of work patterns.  Currently Magic Graph includes MySQL/MariaDB adapters 
out of the box, and additional adapters will be added in future releases.

All examples in this documentation will assume that you want to use the persistence package.  

---

## Installation

```
composer require buffalokiwi/magicgraph
```
  

---
  
  
## Dependencies

Magic Graph requires one third party and three BuffaloKiwi libraries.

1. [BuffaloKiwi/buffalotools_ioc](https://github.com/SixArmDonkey/buffalotools_ioc) - A service locator 
2. [BuffaloKiwi/buffalotools_types](https://github.com/SixArmDonkey/buffalotools_types) - Enum and Set support
3. [BuffaloKiwi/buffalotools_date](https://github.com/SixArmDonkey/buffalotools_date) - DateTime factory/wrappers 
4. [MoneyPHP/Money](https://github.com/moneyphp/money) - PHP implementation of Fowler's Money pattern
  

---
  
  
## Definitions

### What is a Model?

Magic Graph models are extensible and self-contained programs.  They are designed to encapsulate all properties and behavior 
associated with any single source of data, but the models have zero knowledge of how to load or persist data.  Don't worry 
too much about how these components work under the hood, we'll go over that in a future chapter.
  

Magic Graph models are composed of 4 main components:

1. Property Definitions and base behavior 
2. Properties bundled into a Property Set 
3. The Model object
4. Behavioral Strategies 


**Properties**
  
At the core of every Magic Graph model, you will find a series of properties.  Much like a standard class property, 
Magic Graph properties have a name, a data type and a value.  Unlike standard class properties, Magic Graph properties
are first class objects.  They fully encapsulate all behavior associated with their data type, are extensible, reusable, 
self-validating and have configurable behaviors.
  

**Property Set**

The model properties are bundled into a [Set-backed](https://github.com/SixArmDonkey/buffalotools_types#set) object called a [Property Set](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IPropertySet.html) .
The property set provides methods for accessing property objects, their meta data, flags, configuration 
data and the ability to add and remove properties at run time.
  
  
**Model Objects**

All models must implement the [IModel](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-IModel.html) interface.  Magic Graph models
are essentially wrappers for the property set, and they expose properties within the set as if they were public members of the model class.  Adding getter and 
setter methods are optional, but recommended.  In addition to providing access to properties, models keep track of new and/or edited properties, have their own 
validation method, and can have additional behavioral strategy objects coupled to them.  

  
**Behavioral Strategies**

Strategies are programs that modify the behavior of a model or property, and implement the [INamedPropertyBehavior](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-INamedPropertyBehavior.html) interface  Strategies are passed to the model during object construction, 
and models will call the strategy methods.  For example, say you had an order object, and you 
wanted to send the customer a receipt after they submit an order.  A strategy could be created that sends an email after
the order is successfully created and saved.  Both IModel and INamedPropertyBehavior can be extended to add additional 
events as necessary.
  
---
  

## Getting Started
  
### Hello Model

This is one of many ways to write models in Magic Graph.  As you read through the documentation, we will gradually 
shift towards writing more robust and extensible models.  The following model example is used to illustrate the internal 
structure of models.

For now, let's take a look at some basic model creation code.

In this example, the following objects are used:  
[buffalokiwi\magicgraph\DefaultModel](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-DefaultModel.html)  
[buffalokiwi\magicgraph\property\DefaultIntegerProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-DefaultIntegerProperty.html)  
[buffalokiwi\magicgraph\property\DefaultStringProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-DefaultStringProperty.html)  
[buffalokiwi\magicgraph\property\PropertyListSet](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-PropertyListSet.html)  
  
  
First step is to decide the names and data types of the properties to be included within the model.  In our example, we 
will add two properties:  An integer property named "id", and a string property named "name".  We will use 
DefaultIntegerProperty, and DefaultStringProperty.  To create the model, each property is passed to the PropertyListSet 
constructor, which is then passed to DefaultModel.  


```php
$model = new DefaultModel(                //..Create the model
  new PropertyListSet(                    //..Create the property set 
    new DefaultIntegerProperty( 'id' ),   //..Add the id property
    new DefaultStringProperty( 'name' )   //..Add the name property 
));
```

A model with two properties has now been created.  The properties are now available as public class properties.

```php
//..Set the id and name property values 

$model->id = 1;       
$model->name = 'Hello Model';

//..Get the id and property values 
var_dump( $model->id ); //..Outputs: "int 1"
var_dump( $model->name ); //..Outputs: "string 'Hello Model' (length=11)"
```

Now, what happens if we try to assign a value of the wrong type to one of the properties?  An exception is thrown!
The following code will result in a [ValidationException](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-ValidationException.html) being thrown with the message: "Value foo for property id must be an integer. Got string.".

```php
$model->id = 'foo'; //..id is not a string.
```

Models are self-validating, and ValidationException will be thrown immediately when attempting to set an invalid value.  There are many validation 
options attached to the various default properties included with Magic Graph, which we will cover in the [Validation](#) chapter.
  
---

### Basic Database and Repository Setup
  
So, what if we want to persist this data in a MySQL database?  Without going into too much detail, we can create a 
SQL repository, which doubles as an object factory for the above-defined model.

The following objects are used:

[buffalokiwi\magicgraph\pdo\IConnectionProperties](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-pdo-IConnectionProperties.html)  
Defines connection properties used to establish a database connection  
  
[buffalokiwi\magicgraph\pdo\IDBConnection](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-pdo-IDBConnection.html)  
Defines a generic database connection  
  
[buffalokiwi\magicgraph\pdo\MariaConnectionProperties](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-pdo-MariaConnectionProperties.html)  
MariaDB/MySQL connection properties  
  
[buffalokiwi\magicgraph\pdo\MariaDBConnection](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-pdo-MariaDBConnection.html)  
A database connection and statement helper library for MariaDB/MySQL  
  
[buffalokiwi\magicgraph\pdo\PDOConnectionFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-pdo-PDOConnectionFactory.html)  
A factory for creating database connection instances    
  
  
First step is to create a database connection.

```php
$dbFactory = new PDOConnectionFactory(         //..A factory for managing and sharing connection instances 
  new MariaConnectionProperties(  //..Connection properties for MariaDB / MySQL
    'localhost',                  //..Database server host name 
    'root',                       //..User name
    '',                           //..Password
    'testdatabase' ),             //..Database 
  //..This is the factory method, which is used to create database connection instances
  //..The above-defined connection arguments are passed to the closure.
  function( IConnectionProperties $args  ) { 
    //..Return a MariaDB connection 
    return new MariaDBConnection( $args );
  });
```

Next step is to create a table for our test model:
  
```sql
CREATE TABLE `inlinetest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 
```
  
Finally, we create an instance of InlineSQLRepo, which is a repository for handling model construction, loading and saving data.
You may notice that we are now using a PrimaryIntegerProperty instead of an IntegerProperty for id.  This is because
repositories require at least one property to be flagged as a primary key, and PrimaryIntegerProperty automatically sets that flag.  
  

```php
$repo = new InlineSQLRepo( 
  'inlinetest',                       //..Database table name 
  $dbFactory->getConnection(),        //..Database connection
  //..Model properties follows 
  PrimaryIntegerProperty( 'id' ),     //..Primary id property 
  DefaultStringProperty( 'name' ));   //..Optional string property 
```

Now we create and save!

Create a new model from our new repository like this:

```php
$model = $repo->create();
```
  
We can also initialize properties with the create method:
  
```php
$model = $repo->create(['name' => 'foo']));
```
  
Set the property values

```php
$model->name = 'foo';
```
  
Since id is defined as a primary key, we do not want to set that value.  The repository will take care of assigning that for us.
Save the model by passing it to the repository save() method.  

```php
$repo->save( $model );

echo $model->id;  //..Prints 1 
```

When saving, the repository first validates the model by calling the validate() method attached to the model.  Then, on a successful
save, the repository will assign the id (automatically generated by the database) to the id property.


Assuming the id of the newly created record was 1, we can retrieve the model:

```php
$model = $repo->get('1');
```

The getting started section shows the most basic way of working with Magic Graph.  While that's nice and all, 
it's pretty useless for anything other than a simple program.  The next several chapters will detail how to use Magic Graph
in larger applications.

---
  
## Property Configuration

Property configuration files are a way to define properties and property-specific behavior, and must implement the [IPropertyConfig](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IPropertyConfig.html) interface.
The configuration objects are similar to PHP traits, where we define partial objects.  These objects can be assigned to IModel
instances and define the property set (properties used within) and behavior of the associated model.

In the following example, we will create a sample property set with two properties: "id" and "name".  

Id will be an integer property, have a default value of zero, be flagged as a primary key, and will read only if the value is non-zero.  
Name will be a string property, have a default value of an empty string and be flagged as required.

In this example, these additional classes and interfaces are used:  
  
The base property configuration is the base class used when defining property configurations.  It provides
constants, common property configurations and several methods for working with behaviors.
[buffalokiwi\magicgraph\property\BasePropertyConfig](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-BasePropertyConfig.html)  
  
IPropertyFlags defines various flags available to properties.  This interface can be extended to add additional flags and functionality.
[buffalokiwi\magicgraph\property\IPropertyFlags](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IPropertyFlags.html)  
  
IPropertyType defines the available property types.  Each type maps to a property object via the [IConfigMapper](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IConfigMapper.html) interface.  
[buffalokiwi\magicgraph\property\IPropertyType](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IPropertyType.html)  
  
StandardPropertySet uses the default IConfigMapper and [IPropertyFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IPropertyFactory.html) implementations to provide an 
easy way to instantiate IPropertySet instances when creating IModel instances.
[buffalokiwi\magicgraph\property\StandardPropertySet](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-StandardPropertySet.html)  
  
  
```php
class SamplePropertyConfig extends BasePropertyConfig
{
  //..Returns an array detailing the properties to add
  protected function createConfig() : array
  {
    //..A map of property name to configuration 
    return [
      //..The Id Property 
      'id' => [
        self::TYPE => IPropertyType::TINTEGER,     //..The data type
        self::FLAGS => [IPropertyFlags::PRIMARY],  //..Flags 
        self::VALUE => 0                           //..Default value 
      ],
        
      'name' => [
        self::TYPE => IPropertyType::TSTRING,
        self::FLAGS => [IPropertyFlags::REQUIRED],
        self::VALUE => ''
      ]        
    ];
  }
}
```

A property configuration object descends from BasePropertyConfig and/or implements the IPropertyConfig instance.
Only a single method createConfig() is required to be implemented in the descending class, and must return an 
array with zero or more property definitions.
  
createConfig() returns a map of property name to property configuration data.  When defining the property confuration, 
'type' (BasePropertyConfig::TYPE) is the only required attribute.  
  
BasePropertyConfig::FLAGS maps to an array, which contains constants from IPropertyFlags.  Zero or more flags may be supplied, and each will
modify how a property is validated.   

Default values can be set with the BasePropertyConfig::VALUE attribute, and assigning the value as the desired default value.
  
  
After creating the property definitions, we can then assign them to a property set, which is assigned to a model.  Multiple
IPropertyConfig instances can be passed to a StandardPropertySet.
  
```php  
$model = new DefaultModel( new StandardPropertySet( new SamplePropertyConfig()));
```
  
BasePropertyConfig contains a few helper constants, which can be used to simplify the creation of property configuration objects.
For example, the previous example could be rewritten as:
  
```php
class SamplePropertyConfig extends BasePropertyConfig
{
  //..Returns an array detailing the properties to add
  protected function createConfig() : array
  {
    //..A map of property name to configuration 
    return [
      //..The Id Property 
      'id' => self::FINTEGER_PRIMARY,
      'name' => self::FSTRING_REQUIRED
    ];
  }
}
```
  
FINTEGER_PRIMARY will create an integer property, flagged as a primary key, with a default value of zero  
FSTRING_REQUIRED will create a string property, flagged as required, with a default value of an empty string.  
  
  
---
  
    
### Property Configuration Array Attributes  
  
The BasePropertyConfig class contains a series of constants used within the array returned by createConfig() 
to create properties for models.  Certain attributes are for specific data types, and using them with other types will have no effect.
  
  
#### Caption
Property caption/label to be used at the application level.  
Magic Graph does not read this value for any specific purpose.  
```
BasePropertyConfig::CAPTION = 'caption'
```   
  
#### Id 
An optional unique identifier for some property.  This is simply a tag, and is to be used at the application level.
Magic Graph does not read this value for any specific purpose.
```
BasePropertyConfig::ID = 'id'
```   
  
#### Default Value 
Default value.  
If no value is supplied during model construction, or if the IProperty::reset() method is called, property value will be 
assigned to the default value listed in the property configuration object.
```
BasePropertyConfig::VALUE = 'value'
```  
  
#### Setter Callback
When a property value is set, any supplied setters will be called in the order in which they were defined.  
Each property can define a single setter within the configuration array, but multiple setters can be added by 
supplying property behavior objects to the property configuration object constructor.  
  
Setter callbacks are called by IProperty::setValue(), and can be used to modify an incoming value prior to 
validation.  When chaining setters, the result of the previous setter is used as the value argument for the subsequent 
setter.  
  
```
f( IProperty, mixed $value ) : mixed  
BasePropertyConfig::SETTER = 'setter'
```  
  
#### Getter Callback
When a property value is retrieved, any supplied getters will be called in the order in which they were defined. 
Each property can define a single getter within the configuration array, but multiple getters can be added by 
supplying property behavior objects to the property configuration object constructor.  
  
Getter callbacks are called by IProperty::getValue(), and can be used to modify a value prior to being returned by
getValue().  When chaining getters, the result of the previous getter is used as the value argument for the subsequent
getter.  
```
f( IProperty, mixed $value ) : mixed   
BasePropertyConfig::GETTER = 'getter'
```  
  
#### Model Setter Callback 
Model setters are the same as property setters, but they are called at the model level.  The difference
between a model setter and a property setter is that model setters have access to other properties, and property 
setters do not.  Since full model validation is only called on save, this can be used to validate state within an 
object, and prevent any modifications by throwing a ValidationException.  
  
1. When calling IModel::setValue (or setting a value via IModel::__set()), model setters are called in the order in which they were defined.  
2. Model setters are called prior to property setters and prior to property validation.
3. When chaining model setters, the result of the previous setter is used as the value argument for the subsequent model setter.
  
```
f( IModel, IProperty, mixed $value ) : mixed  
BasePropertyConfig::MSETTER = 'msetter'
```  
  
#### Model Getter Callback 
Model getters are the same as property getters, but they are called at the model level.  The difference
between a model getter and a property getter is that model getters have access to other properties, and property 
getters do not.  
  
1. When calling IModel::getValue (or getting a value via IModel::__get()), model getters are called in the order in which they were defined.  
2. Model getters are called after property getters.
3. When chaining model getters, the result of the previous getter is used as the value argument for the subsequent model getter.
  
```
f( IModel, IProperty, mixed $value ) : mixed   
BasePropertyConfig::MGETTER = 'mgetter'
```  
  
#### Property Data Type
This must map to a valid value of [IPropertyType](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IPropertyType.html).
For more information see the [Property Data Types](#property-data-types) section.  
```
BasePropertyConfig::TYPE = "type"  
```  
    
#### Property Flags 
This must map to a comma-delimited list of valid [IPropertyFlags](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IPropertyFlags.html) values.  
For more information see the [Property Flags](#property-flags) section.  
```
BasePropertyConfig::FLAGS = "flags"  
```  
  
#### Class name for properties returning objects 
When using properties backed by a descendant of ObjectProperty, the clazz attribute must be used.  The value should be a 
fully namespaced class name.  
```
BasePropertyConfig::CLAZZ = "clazz"
```  
For example, when the property type is defined as Enum or Set, clazz would equal some enum class name.
```php
//..Sample enum class
class SampleEnum extends Enum {} 

//..Property configuration
'enum_property' => [
  'type' => 'enum',
  'clazz' => SampleEnum::class
]
```  
  
  
#### Initialize Callback  
When IProperty::reset() is called, this function is called with the default value.  This is a way to modify the default
value prior to it being assigned as the initial property value.  The value returned by the init callback is the 
new default value.  
```
f( mixed $defaultValue ) : mixed  
BasePropertyConfig::INIT = "initialize"
```  
  
#### Minimum value/length  
This is used with both Integer and String properties, and is the minimum value or minimum string length.
```
BasePropertyConfig::MIN = "min"
```    
  
#### Maximum value/length  
This is used with both Integer and String properties, and is the maximum value or minimum string length.
```
BasePropertyConfig::MAX = "max"
```  
  
#### Validation
Validate callbacks are for validating individual property values prior to save or when IProperty::callback() is called. 
Validate callbacks are called prior to the backing property object validation call, and can either return a boolean representing
validity, or throw a ValidationException.  Returning false will automatically throw a ValidationException with an appropriate message.    
```
[bool is valid] = function( IProperty, [input value] )  
BasePropertyConfig::VALIDATE = "validate"
```  
  
#### Regular Expressions
When using string properties, the "pattern" attribute can be used to supply a regular expression, which will be used during
property validation.  Only values matching the supplied pattern can be committed to the property.
```
BasePropertyConfig::PATTERN = "pattern"
```  
  
#### Custom configuration data
A config array.  This is implementation specific, and is currently only used with Runtime Enum data types (IPropertyType::RTEnum). 
This can be used for whatever you want within your application.
```
BasePropertyConfig::CONFIG = "config"
```  
  
#### Embedded model prefix 
A prefix used by the default property set, which can proxy a get/set value call to a nested IModel instance.
For example, say you had a customer model, and wanted to embed an address inside.  Instead of copy/pasting properties or 
linking the customer to addresses, you can assign a prefix to a property named 'address' in the customer configuration, and
add a CLAZZ property containing the class name of the address model.  The customer model will then embed the address model
inside of the customer model, and all address model functionality will be included.  Furthermore, each address property 
will appear to be a member of the customer model, and have the defined prefix.
```
BasePropertyConfig::PREFIX = 'prefix'

//..Example configuration entry:
'address' => [
  'type' => IPropertyType::TMODEL,
  'clazz' => Address::class,
  'prefix' => 'address_'
]  
```  
  
#### On change event  
After a property value is successfully set, change events will be called in the order in which they were supplied. 
```
f( IProperty, oldValue, newValue ) : void   
BasePropertyConfig::CHANGE = 'onchange'
```  
  
For a given property, create an [htmlproperty\IElement](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-htmlproperty-IElement.html) instance used as an html form input.
Basically, generate an html input for a property and return that as a string, which can be embedded in some template.  
```
f( IModel $model, IProperty $property, string $name, string $id, string $value ) : IElement   
BasePropertyConfig::HTMLINPUT = 'htmlinput'
```  

#### Empty check  
This is an optional callback that can be used to determine if a property can be considered "empty".  The result 
of the supplied function is the result of an empty check.
```
f( IProperty, value ) : bool  
BasePropertyConfig::IS_EMPTY = 'isempty'
```
  
#### Tagging 
An optional tag for the attribute.  
This can be any string, and is application specific.  Nothing in Magic Graph will operate on this value by default.  
```
BasePropertyConfig::TAG = 'tag'
```  
  
---
  
  
### Property Data Types
  
Property data type definitions define which data type object a property is backed by.  All of the available definitions
are within the the [buffalokiwi\magicgraph\property\IPropertyType](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IPropertyType.html) interface.   
  
Here is a list of the built in property types that ship with Magic Graph:
  
#### Boolean  
The 'bool' property type will be backed by an instance of [IBooleanProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IBooleanProperty.html). 
Unless specified as null, boolean properties will have a default value of false.
```
IPropertyType::TBOOLEAN = 'bool'
```
  
#### Integer  
Backed by [IIntegerProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IIntegerProperty.html)

```
IPropertyType::TINTEGER = 'int'
```
  
#### Decimal  
Backed by [IFloatProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IFloatProperty.html)
```
IPropertyType::TFLOAT = 'float'
```   
  
#### String  
Backed by [IStringProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IStringProperty.html)
```
IPropertyType::TSTRING = 'string'
```  
  
#### Enum  
Backed by [IEnumProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IEnumProperty.html)
Column must list a class name implementing the IEnum interface in the 'clazz' attribute.  For more information see [BuffaloKiwi Types](https://github.com/SixArmDonkey/buffalotools_types).
```
IPropertyType::TENUM = 'enum'
```  
  
#### Runtime Enum  
Backed by [IEnumProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IEnumProperty.html)
Enum members are configured via the "config" property and is backed by a RuntimeEnum instance.  Runtime Enum instances
do not use the "clazz" attribute. For more information see [BuffaloKiwi Types](https://github.com/SixArmDonkey/buffalotools_types).
```
IPropertyType::TRTENUM = 'rtenum' 
```  
  
#### Array  
Backed by [ArrayProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-ArrayProperty.html)
Array properties are mostly used by Magic Graph relationship providers.  While it's possible to define array properties for 
arbitrary data, it is recommended to create a relationship or model service provider to manage the data contained within 
array properties.  
Array properties can read the "clazz" argument to restrict the array members to objects of the specified type.
```
IPropertyType::TARRAY = 'array'
```  
  
#### Set  
Set properties are backed by [ISetProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-ISetProperty.html), and 
will read/write instances of ISet (or descendants of ISet as specified by the "clazz" attribute).  For more information see [BuffaloKiwi Types](https://github.com/SixArmDonkey/buffalotools_types).
```
IPropertyType::TSET = 'set'
```  
  
#### Date/Time  
Backed by [IDateProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IDateProperty.html),
 and can be used to represent a date and/or time.  This would commonly be used with timestamp or DateTime SQL column types.  
```
IPropertyType::TDATE = 'date'
```  
  
#### Currency.
A property backed by [IMoneyProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IMoneyProperty.html), 
containing an object implementing the [IMoney](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-money-IMoney.html) interface.
This property type requires use of an service locator and have the MoneyPHP/Money dependency installed.  
```
IPropertyType::TMONEY = 'money'
```  
  
#### IModel  
Backed by [IModelProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-IModel.html) and contains an object
implementing the IModel interface.  Model properties are commonly managed by a [OneOnePropertyService](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-OneOnePropertyService.html).
```
IPropertyType::TMODEL = 'model'
```  

#### Object   
A property that only accepts instances of a specified object type.  
It is recommended to extend the ObjectProperty class to create properties that handle specific object types instead of
using the generic ObjectProperty object.  In the future, I may mark ObjectProperty as abstract to prevent direct instantiation.  
```
IPropertyType::TOBJECT = 'object'
```  
  
---
  
  
### Property Flags 
  
Property Flags are a series of modifiers for properties.  Zero or more flags may be assigned to any property, and each 
will modify the validation strategy used within the associated model.  Each flag is a constant defined within the 
[buffalokiwi\magicgraph\property\IPropertyFlags](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IPropertyFlags.html) interface.  
  
  
  
#### No Insert
This property may never be inserted
```
IPropertyFlags::NO_INSERT = 'noinsert';
```  
  
#### No Update
This property may never be updated.  
This can also be considered as "read only".  
```
IPropertyFlags::NO_UPDATE = 'noupdate'
```  
  
#### Required 
This property requires a value  
```
IPropertyFlags::REQUIRED = 'required'
```  
  
#### Allow Null
Property value may include null  
```
IPropertyFlags::USE_NULL = 'null'
```  
  
#### Primary Key 
Primary key (one per property set)  
```
IPropertyFlags::PRIMARY = 'primary'
```  
  
#### Sub config
Magic Graph does not use this flag, but it is here in case some property is loaded from some sub/third 
party config and you want to do something with those.  For example, this is used in Retail Rack to identify properties
loaded from configurations stored within a database.
```
IPropertyFlags::SUBCONFIG = 'subconfig'
```  

#### Write Empty   
Calling setValue() on the model will throw a ValidationException if the stored value is not empty.  
```
IPropertyFlags::WRITE_EMPTY = 'writeempty'
```  
  
#### No Array Output
Set this flag to prevent the property from being printed during a call to IModel::toArray().  toArray() is used 
to copy and save models, and not all properties should be read.  ie: the property connects to some api on read and the 
returned value should not be saved anywhere.  
```
IPropertyFlags::NO_ARRAY_OUTPUT = 'noarrayoutput'
```  
  
  
---
  
  
#### Property Behavior

Each property has a series of callbacks as previously defined in [Property Configuration Array Attributes](#property-configuration-array-attributes). 
When creating instances of objects descending from [BasePropertyConfig](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-BasePropertyConfig.html), 
it is possible to pass instances of [INamedPropertyBehavior](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-INamedPropertyBehavior.html) 
to the constructor.  
  
The purpose of this is to create different strategies for an object.  Strategies are independent, self-contained, and
testable programs.  Zero or more strategies may be attached to a property configuration object, may modify 
properties of the associated model, and may cause side effects.  
  
For example, say you wanted to add a debug message to a log file when a model was saved in your development environment. 
We can create a class that extends [GenericNamedPropertyBehavior](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-GenericNamedPropertyBehavior.html), 
and overrides the getAfterSaveCallback() method.  

  
```php
/**
 * Attach this strategy to any model to add a debug log message when the model is saved.
 */
class DebugLogSaveStrategy extends GenericNamedPropertyBehavior
{
  /**
   * The log 
   * @var LoggerInterface
   */
  private LoggerInterface $log;
  
  
  /**
   * @param LoggerInterface $log
   */
  public function __construct( LoggerInterface $log )
  {
    //..Since this is a save event, we simply pass the name of the class as the property name.
    //..Save events are called regardless of the supplied name.
    parent::__construct( static::class );   
    $this->log = $log;
  }
  
  /**
   * Retrieve the after save function  
   * @return Closure|null function 
   */
  public function getAfterSaveCallback() : ?Closure
  {
    return function( IModel $model ) : void {
      //..Get the primary key value from the model
      $priKey = $model->getValue( $model->getPropertySet()->getPrimaryKey()->getName());
      
      //..Add the log message 
      $this->log->debug( 'Model with primary key value: ' . $priKey . ' successfully saved.' );
    };
  }  
}
```
  
After creating our strategy, we can attach it to a model via it's property configuration object.  
  
```php
//..Create the property config object and attach the strategy 
$config = new SamplePropertyConfig( new DebugLogSaveStrategy( new LoggerInterfaceImpl()));

//..Create a model using the configuration 
$model = new DefaultModel( new StandardPropertySet( $config ));
```
  
When the model is saved via some [IRepository](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-IRepository.html) instance, the 
after save callback will be executed in the strategy, and the log message will be added.
  
There are several callbacks, which together can be used to create rich models by using decoupled strategy objects.  
See [Property Configuration Array Attributes](#property-configuration-array-attributes) for details.

  
Adding behavioral strategies for individual properties is the same process as the above, except we would expose the 
"name" argument from the GenericNamedPropertyBehavior constructor.  
  
```php
/**
 * Attach this strategy to a model to print a log message when a value was set 
 */
class DebugSetterStrategy extends GenericNamedPropertyBehavior
{
  /**
   * The log 
   * @var LoggerInterface
   */
  private LoggerInterface $log;
  
  
  /**
   * @param string $name Property name 
   * @param LoggerInterface $log
   */
  public function __construct( string $name, LoggerInterface $log )
  {
    //..Pass the property name 
    parent::__construct( $name );   
    $this->log = $log;
  }
  
  
  /**
   * Callback used to set a value.
   * This is called prior to IProperty::validate() and the return value will 
   * replace the supplied value.
   * 
   * f( IProperty, value ) : mixed
   * 
   * @return Closure callback
   */
  public function getSetterCallback() : ?Closure
  {
    return function( IProperty $prop, $value ) {
      //..Add the log message
      $this->log->debug( $prop->getName() . ' changed to ' . (string)$value );

      //..Return the unmodified value.
      //..Setters can modify this value if desired
      return $value;
    };
  }  
}
```
  
Then to use the strategy:  
  
```php
//..Create the property config object and attach the strategy for the "name" property
$config = new SamplePropertyConfig( new DebugSetterStrategy( 'name', new LoggerInterfaceImpl()));

//..Create a model using the configuration 
$model = new DefaultModel( new StandardPropertySet( $config ));
```
  
Now any time the "name" property is set, the debug log will show "[Property name] changed to [new value]"
  
  

---
  
  
#### Quick Models
  
[Quick models](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-QuickModel.html) can be useful 
when you need to create a temporary model, or if you need to quickly create a mock model.
Quick models accept standard configuration arrays, and can do anything a standard model can do.  In the following example,
we create a model with two properties: "id" and "name", and we add some behavior to the name property. 
When setting the name property, "-bar" is appended to the incoming value.  When retrieving the name property, 
"-baz" is appended to the outgoing value.  
  

```php
//..Create a new quick model 
$q = new \buffalokiwi\magicgraph\QuickModel([
  //..Id property, integer, primary key
  'id' => [
    'type' => 'int',
    'flags' => ['primary']
  ],
    
  //..Name property, string 
  'name' => [
    'type' => 'string',
      
    //..Append -bar to the name property value when seting 
    'setter' => function( IProperty $prop, string $value ) : string {
      return $value . '-bar';
    },
            
    //..Append -baz to the name property value when retrieving 
    'getter' => function( IProperty $prop, string $value ) : string {
      return $value . '-baz';
    }
  ]
]);

//..Set the name attribute
$q->name = 'foo';

echo $q->name; //..Outputs "foo-bar-baz"
```
  

#### Annotations

PHP 8 added a wonderful new feature called attributes.  These snazzy things let us tag properties with things like the 
backing object type, default values, flags, etc.  If you are willing to allow Magic Graph to make some assumptions, you can 
skip making property sets and configuration arrays/files.  An annotated model would look something like this:


```php


use buffalokiwi\magicgraph\AnnotatedModel;
use buffalokiwi\magicgraph\property\annotation\IntegerProperty;
use buffalokiwi\magicgraph\property\annotation\BooleanProperty;
use buffalokiwi\magicgraph\property\annotation\DateProperty;
use buffalokiwi\magicgraph\property\annotation\ArrayProperty;
use buffalokiwi\magicgraph\property\annotation\EnumProperty;
use buffalokiwi\magicgraph\property\annotation\FloatProperty;
use buffalokiwi\magicgraph\property\annotation\SetProperty;
use buffalokiwi\magicgraph\property\annotation\StringProperty;
use buffalokiwi\magicgraph\property\annotation\USDollarProperty;


class Test extends AnnotatedModel
{  
  #[IntegerProperty]
  private int $id;
  
  #[BooleanProperty]
  private bool $b;
  
  #[DateProperty('d', '1/1/2020')]
  private IDateTime $d;  
  
  #[ArrayProperty('a','\stdClass')]
  private array $a;
  
  #[EnumProperty('e','\buffalokiwi\magicgraph\property\EPropertyType','int')]
  private \buffalokiwi\magicgraph\property\EPropertyType $e;
  
  #[FloatProperty]
  private float $f;
  
  #[SetProperty('set','\buffalokiwi\magicgraph\property\SPropertyFlags',['noinsert','noupdate'])]
  private \buffalokiwi\buffalotools\types\ISet $set;
  
  #[USDollarProperty]
  private buffalokiwi\magicgraph\money\IMoney $money;
  
  #[StringProperty]
  private string $str;
  
  public \buffalokiwi\magicgraph\property\IIntegerProperty $pubProp;
  
  public function __construct()
  {
    $this->pubProp = new buffalokiwi\magicgraph\property\DefaultIntegerProperty( 'pubProp', 10 );    

    parent::__construct( new \buffalokiwi\magicgraph\property\QuickPropertySet([
       'name' => [
           'type' => 'string',
           'value' => 'qp string'
       ]
    ]));
  }
}


$a = new Test();

$aa = $a->a;
$aa[] = new \stdClass();
$a->a = $aa;

$a->id = 22;
$a->b = true;
$a->d = '10/10/2020';
$a->f = 1.123;
$a->set->add( 'primary' );
$a->str = 'foo';
$a->e->setValue( 'string' );
$a->pubProp->setValue( 11 );
$a->money = '3.50';

var_dump( $a->toArray(null, true, true));

Outputs:

array (size=11)
  'name' => string 'qp string' (length=9)
  'id' => int 22
  'b' => int 1
  'd' => 
    object(DateTimeImmutable)[644]
      public 'date' => string '2020-10-10 00:00:00.000000' (length=26)
      public 'timezone_type' => int 3
      public 'timezone' => string 'UTC' (length=3)
  'a' => 
    array (size=1)
      0 => 
        object(stdClass)[701]
  'e' => string 'string' (length=6)
  'f' => float 1.123
  'set' => string 'primary,noupdate,noinsert' (length=25)
  'money' => string '3.50' (length=4)
  'str' => string 'foo' (length=3)
  'pubProp' => int 11


```

The above example mixes php attributes, a configuration array and a public IProperty intance.  All three ways can be 
used to create models if you extend the AnnotatedModel class.

In a future release, the annotations package will be extended to include all available property configuration options and 
to configure relationships.


---
 
  
## Repositories 
  
Magic Graph repositories are an implementation of the [Repository Pattern](https://martinfowler.com/eaaCatalog/repository.html).  
Repositories are an abstraction that encapsulates the logic for accessing some persistence layer.  Similar to a collection, 
repositories provide methods for creating, saving, removing and retrieving IModel instances.  Repositories are object factories, and are designed to 
produce a single object type.  However, in a SQL setting, repositories may work with multiple tables from a database to produce a single model instance.
When creating aggregate repositories, it's your choice if you wish to create a single repository per table, or a single 
repository that access several tables.  It's worth noting that a repository may reference other repositories that access 
different persistence layers.
  
Repositories implement the [IRepository](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-IRepository.html) interface.
  
  
### Mapping Object Factory
  
Data mappers map data retrieved from some persistence layer to an IModel instance, and implement the [IModelMapper](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-IModelMapper.html) interface. 
In Magic Graph, data mappers also double as object factories.  
  
The [MappingObjectFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-MappingObjectFactory.html) is normally the base class
for all repositories, and implements the [IObjectFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-IObjectFactory.html) interface. 
The mapping object factory is responsible for holding a reference to a data mapper and a property set defining some model, and using those references can create 
instances of a single IModel implementation.  The create() method accepts raw data from the persistence layer, creates a new IModel instance, and maps the supplied
data to the newly created model.  

IObjectFactory implementations should not directly access any persistence layer.  Instead, extend this interface and define
an abstraction for accessing a specific type of persistence layer.  For example, in Magic Graph, there is a SQLRepository for 
working with a MySQL database.  
  
Note: If you simply want an object factory for creating models, MappingObjectFactory can be directly instantiated.  
  
  
  
### Saveable Mapping Object Factory
  
[SaveableMappingObjectFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-SaveableMappingObjectFactory.html) is an abstract class extending IObjectFactory, 
implements [ISaveableObjectFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ISaveableObjectFactory.html), and 
adds the ability to save an IModel instance.  All repositories in Magic Graph extend this class.  The saveable mapping 
object factory adds the save() and saveAll() methods, which outlines the repository save event as follows:  
  
1. Test the supplied model matches the implementation of IModel managed by the repository.  This prevents a model of an incorrect type from being saved.
2. Call the protected beforeValidate() method.  This can be used to prepare a model for validation in extending repositories.
3. Validate the model by calling IModel::validate()
4. Call the protected beforeSave() method.  This can be used to prepare a model for save.
5. Call the protected saveModel() method.  This is required to be implemented in all extending repositories 
6. Call the protected afterSave() method.  This can be used to clean up anything after a save.  This method should not have side effects.
  
  
Calling saveAll() is a bit different than the save method.  After testing the model types, the save process is split into three parts:  
  
1. For each supplied model, call beforeValidate(), validate() and beforeSave().
2. For each supplied model, call saveModel()
3. For each supplied model, call afterSave()
  
  
  
### SQL Repository
  
The [SQLRepository](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-SQLRepository.html) is the 
[IRepository](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-IRepository.html), used for 
working with MariaDB/MySQL databases.  The SQLRepository also extends the [ISQLRepository](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ISQLRepository.html) 
interface, which adds additional methods for working with SQL databases.
  
In the following example, we will be using the same table and database connection outlined in [Basic Database and Repository Setup](#basic-database-and-repository-setup).  
  
Instantiating a SQLRepository:  
  
```php
$testSQLRepo = new SQLRepository(                            //..Create the SQL Repository
  'inlinetest',                                              //..Table Name
  new DefaultModelMapper( function( IPropertySet $props ) {  //..Create a data mapper 
    return new DefaultModel( $props );                       //..Object factory 
  }, IModel::class ),                                        //..Type of model being returned 
  $dbFactory->getConnection(),                               //..SQL database connection 
  new QuickPropertySet([                                     //..Property set defining properties added to the model 
    //..Id property, integer, primary key      
    'id' => [                                                //.."id" is a property
      'type' => 'int',                                       //..Id is an integer
      'flags' => ['primary']                                 //..Id is the primary key
    ], 

    //..Name property, string 
    'name' => [                                              //.."name" is a property 
      'type' => 'string',                                    //..Name is a string 
    ]
  ])
);
```
  
The above-setup is similar to the InlineSQLRepo, but it allows us much more fine-grained control over which components are 
used.  Here, we are able to define which data mapper is used and how the property set is created.  For more information, please
see [Extensible Models](#extensible-models).  
  
  
  
### Decorating Repositories
  
Magic Graph provides several proxy classes, which can be used as base classes for repository decorators.  
  
1. [ObjectFactoryProxy](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ObjectFactoryProxy.html)
2. [RepositoryProxy](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-RepositoryProxy.html)
3. [SaveableMappingObjectFactoryProxy](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-SaveableMappingObjectFactoryProxy.html)
4. [ServiceableRepository](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ServiceableRepository.html)
5. [SQLRepositoryProxy](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-SQLRepositoryProxy.html)
6. [SQLServiceableRepository](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-SQLServiceableRepository.html)

Each of the above-listed proxy classes accept the an associated repository instance as a constructor argument, and will map the method
calls to the supplied repository instance.  The proxy classes should be extended to provide additional functionality to a repository.  
ServiceableRepository and SQLServiceableRepository are implementations of proxy classes, and provide ways to further extend functionality of 
repositories.  These are discussed in the next section.  
  
I plan on adding more decorators in a future Magic Graph release, and currently there is a single decorator included in the 
current version:

[CommonObjectRepo](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-CommonObjectRepo.html) extends the 
RepositoryProxy class, and is used to prevent multiple database lookups.  Each time a model is retrieved from the repository, it is cached in memory, and 
any subsequent retrieval calls will return the cached version of the model.  
  
Here's an example of how to use a decorator:

```php
$testSQLRepo = new CommonObjectRepo( new SQLRepository(      //..Create the SQL Repository and add the caching decorator 
  'inlinetest',                                              //..Table Name
  new DefaultModelMapper( function( IPropertySet $props ) {  //..Create a data mapper 
    return new DefaultModel( $props );                       //..Object factory 
  }, IModel::class ),                                        //..Type of model being returned 
  $dbFactory->getConnection(),                               //..SQL database connection 
  new QuickPropertySet([                                     //..Property set defining properties added to the model 
    //..Id property, integer, primary key      
    'id' => [                                                //.."id" is a property
      'type' => 'int',                                       //..Id is an integer
      'flags' => ['primary']                                 //..Id is the primary key
    ], 

    //..Name property, string 
    'name' => [                                              //.."name" is a property 
      'type' => 'string',                                    //..Name is a string 
    ]
  ])
));
```  
  
Decorating repositories is easy and fun!
  
  

### Serviceable Repository 
  
The serviceable repositories are used for repository relationships, which are discussed in the [Relationships](#relationships) section.  
  
Essentially, serviceable repositories accept [ITransactionFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ITransactionFactory.html) 
and zero or more IModelPropertyProvider instances, which are used to extend the functionality of certain properties.  
  
For example, say you have a model A with an IModel property B.  By default the repository for model A does not know 
repository B or model B exists.  A IModelPropertyProvider instance could add a lazy loading scheme for retrieving 
model B when the property is accessed from model A.  Additionally, the model property provider could provide a way to 
save edits to model B when model A is saved.  
  
  
### Composite Primary Keys
  
Magic Graph fully supports composite primary keys, and certain methods of IRepository and ISQLRepository will contain variadic id arguments for passing multiple primary key values.
Composite primary keys are assigned via the IPropertyFlags::PRIMARY attribute as follows:

```php
[                                                          
  //..Id property, integer, primary key      
  'id' => [                                                //.."id" is a property
    'type' => 'int',                                       //..Id is an integer
    'flags' => ['primary']                                 //..Id is the primary key
  ], 

  'id2' => [                                               //.."id2" is a property
    'type' => 'int',                                       //..Id2 is an integer
    'flags' => ['primary']                                 //..Id2 is the other primary key
  ], 
]
```
  
Note: when supplying primary key values to repository methods, they are accepted in the order in which they were defined.
I will create a way to not have to depend on the order of arguments in a future release.
  
  
---
  

## Transactions

### Transactions Overview 
Transactions represent some unit of work, and are typically used to execute save operations against some persistence layer.  Similar to a database transaction, 
Magic Graph transactions will:

1. Start a transaction in the persistence layer when available
2. Execute arbitrary code against the persistence layer
3. Commit the changes
4. Roll back the changes on failure

Transactions are based on a single interface [ITransaction](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ITransaction.html), 
and can have multiple implementations used to support various persistence layers.  Magic Graph fully supports using 
multiple, and different, persistence layers concurrently.  Transactions can be considered an adapter, which 
executes persistence-specific commands used to implement the required commit and rollback functionality.

Currently, Magic Graph ships with a single transaction type: [MySQLTransaction](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-MySQLTransaction.html)


### Creating a Transaction

At the heart of any transaction is the code to be executed.  In Magic Graph, the interface [IRunnable](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-IRunnable.html)
is used to define the code to be executed within a transaction.  This type exists, because each persistence type will require a subclass of IRunnable to be created.
These types are used to group transactions by persistence type, and to expose persistence-specific methods that may be required
when working with the transactions.  For example, [ISQLRunnable](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ISQLRunnable.html) is used 
for persistence layers that utilize SQL.  ISQLRunnable adds a single method getConnection(), which can be used to access the underlying database connection.  

In it's simplest form, a transaction is a function passed to a Transaction object.  The supplied function is executed 
when Transaction::run() is called.  It is worth noting that Transaction can accept multiple functions.  Transaction::run()
will call each of the supplied functions in the order in which they were received.


```php

//..Get some data, model, etc.
$data = 'This represents a model or some other data being saved';

//..Create a new transaction. and write the contents of $data to a file when Transaction::run() is executed.
$transaction = new buffalokiwi\magicgraph\persist\Transaction( new buffalokiwi\magicgraph\persist\Runnable( function() use($data) {
  file_put_contents( 'persistence.txt', $data );
}));

```


Executing the transaction 

```php

//..Start a new transaction inside of the persistence layer 
$transaction->beginTransaction();

try {
  //..Execute the code 
  $transaction->run();

  //..Commit any changes in the persistence layer 
  $transaction->commit();

} catch( \Exception $e ) {
  //..OH NO!  An Error!
  //..Revert any changes in the persistence layer
  $transaction->rollBack();
}

```

The default Transaction object shipped with Magic Graph does not connect to any specific persistence layer, and the implementations
of beginTransaction, commit and rollBack do nothing.


Since we want methods to actually do things, this is an example of how to run a transaction against MySQL/MariaDB.  A MySQL 
transaction is passed an instance of [ISQLRunnable](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ISQLRunnable.html).  Currently, 
there is a single implementation of ISQLRunnable, and that is [MySQLRunnable](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-MySQLRunnable.html).  
MySQL runnable differs from the Transaction object used in the previous example by adding a constructor argument that accepts an instance of ISQLRepository.  The repository is used to 
obtain an instance of [buffalokiwi\magicgraph\pdo\IDBConnection](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-pdo-IDBConnection.html), which is used to execute 
the transaction.

The following is a full example of how to execute a transaction against a SQLRepository instance:

```php

//..Create a repository
$testSQLRepo = new SQLRepository(
  'inlinetest',
  new DefaultModelMapper( function( IPropertySet $props ) {
    return new DefaultModel( $props );
  }, IModel::class ),
  $dbFactory->getConnection(),
  new QuickPropertySet([
    //..Id property, integer, primary key
    'id' => [
      'type' => 'int',
      'flags' => ['primary']
    ],

    //..Name property, string 
    'name' => [
      'type' => 'string',
    ]
  ])
);   
   
//..Create a new model and assign some property values
$model = $testSQLRepo->create([]);
$model->name = 'test';
  
//..Create a transaction 
$transaction = new \buffalokiwi\magicgraph\persist\MySQLTransaction(
  new \buffalokiwi\magicgraph\persist\MySQLRunnable(
    $testSQLRepo,
    function() use( $testSQLRepo, $model ) {
      $testSQLRepo->save( $model );
    }
));


//..Start a new transaction inside of the persistence layer
$transaction->beginTransaction();

try {
  //..Execute the code 
  $transaction->run();

  //..Commit any changes in the persistence layer 
  $transaction->commit();

} catch( \Exception $e ) {
  //..OH NO!  An Error!
  //..Revert any changes in the persistence layer
  $transaction->rollBack();
}

```

While transactions against a single persistence engine could be more simply coded directly against the database, 
the Magic Graph Transaction abstraction provides a way for us to run transactions against multiple database connections, 
or even different persistence engines.  


### Transaction Factory

The transaction factory generates instances of some subclass of ITransaction.  The idea is to pass ITransactionFactory::createTransactions() a list of 
IRunnable instances, and the transaction factory will then group them by persistence type (registered subclass).  
Transactions are executed as follows:

1. Begin transaction is executed for each ITransaction instance
2. Run is called for each ITransaction instance
3. Commit is called for each ITransaction Instance
4. If an exception is thrown at any time, rollback is called for each ITransaction instance and the exception is rethrown.


```php

//..Create a database connection factory for some MySQL database
$dbFactory = new PDOConnectionFactory( 
  new MariaConnectionProperties( 
    'localhost',    //..Host
    'root',         //..User
    '',             //..Pass
    'retailrack' ), //..Database 
  function(IConnectionProperties $args  ) {
    return new MariaDBConnection( $args );
});


//..Create a quick test repository for a table named "inlinetest", with two columns id (int,primary,autoincrement) and name(varchar).
$repo = new InlineSQLRepo( 
  'inlinetest', 
  $dbFactory->getConnection(),
  new PrimaryIntegerProperty( 'id' ),
  new DefaultStringProperty( 'name' )
);

//..Create a new model and set the name property value to "test"
$model = $repo->create([]);
$model->name = 'test';


//..Create a new transaction factory
//..The supplied map is used within the TransactionFactory::createTransactions() method, and will generate ITransaction
//  instances of the appropriate type based on a predefined subclass of IRunnable 
//..Instances passed to TransactionFactory must be ordered so that the most generic IRunnable instances are last.
$tf = new TransactionFactory([
  //..Supplying ISQLRunnable instances will generate instaces of MySQLTransaction
  ISQLRunnable::class => function( IRunnable ...$tasks ) { return new MySQLTransaction( ...$tasks ); },
  //..Supplying instances of IRunnable will generate a Transaction instance
  IRunnable::class => function( IRunnable ...$tasks ) { return new Transaction( ...$tasks ); }
]);

//..Execute a mysql transaction
//..This will use a database transaction to save the model
//..If any exceptions are thrown by the supplied closure, then rollback is called.  Otherwise, commit is called 
//..upon successful completion of the closure
$tf->execute( new MySQLRunnable( $repo, function() use($repo, $model) {
  $repo->save( $model );  
}));

```


If $repo->save() were to throw an exception in the previous example, then the transaction would have been rolled back.  If
the following code is executed, then you will see how the row is never added to the database due to rollback being called
when the exception is thrown.

```php        
$tf->execute( new MySQLRunnable( $repo, function() use($repo, $model) {
  $repo->save( $model );  
  throw new \Exception( 'No save for you' );
}));

```


---


## Model Relationship Providers 

Similar to a foreign key in a relational database, relationships allow us to create associations between domain objects.
In Magic Graph, a model (IModel) may contain zero or more properties that reference a single or list of associated IModel objects.
The parent model may contain [IModelProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-IModel.html)
and/or [ArrayProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-ArrayProperty.html) properties, 
which can hold referenced model objects.

For example, the following configuration array contains a model property and an array property.

```php

[
  'one' => [
      'type' => 'model',
      'flags' => ['noinsert','noupdate'],
      'clazz' => \buffalokiwi\magicgraph\DefaultModel::class
  ]

  'many' => [
      'type' => 'array',
      'flags' => ['noinsert','noupdate'],
      'value' => [],
      'clazz' => \buffalokiwi\magicgraph\DefaultModel::class
  ]
]

```

Both Model and Array properties must include the "clazz" configuration property, which must equal the class name of the 
object or objects in the array.  This is used to determine which object to instantiate within the relationship provider, and to 
ensure that only objects of the specified type are accepted when setting the property value.

Notice that both properties are marked with "noinsert" and "noupdate".  This is required for both model and array properties, 
and will prevent the properties from being used in insert and update database queries.  If these values are omitted, IModel 
properties will persist as IModel::__toString() and ArrayProperty will be encoded as json.  

Assigning the values to the parent model goes something like this:

```php
  //..Assuming $model was created using the above config and that $ref1 and $ref2 are both instances of DefaultModel

  //..Ok
  $model->one = $ref1;

  //..Throws exception
  $model->one = 'foo';

  //..Multiple models can be added as an array
  $model->many = [$ref1, $ref2];
```


Once we have some model or array of models property, we may want to automate the loading and saving of those models.  For example,
when accessing a model property, we can load the model from the database and return it.  We could also save any edits to the referenced
model when the parent model is saved.  This behavior is accomplished through implementing 
the [IModelPropertyProvider](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-IModelPropertyProvider.html) interface.

The IModelPropertyProvider defines several methods for initialization of a model, retrieving the value, setting the value and persisting the value.  
Model property providers must be used with supporting models and repositories.  


```php
//..A sample child model.  This uses a unique class name instead of QuickModel because IModelProperty will attempt
//  to instantiate an instance of the model when assigning the default value, and quick model is generic.
class ChildModel extends buffalokiwi\magicgraph\QuickModel {
  public function __construct() {
    parent::__construct([
      'name' => [
         'type' => 'string',
         'value' => 'child model'
       ]
    ]);    
  }
}
  
//..The parent model includes a property "child", which is backed by an IModelProperty, and will contain 
//  an instance of ChildModel
$parent = new buffalokiwi\magicgraph\QuickModel([
   'name' => [
       'type' => 'string',
       'value' => 'parent model'
   ],
    
   'child' =>  [
       'type' => 'model',
       'clazz' => ChildModel::class
   ]
]);


//..Models are converted to arrays when using toArray()
var_dump( $parent->toArray( null, true, true ));

Outputs:
array (size=2)
  'name' => string 'parent model' (length=12)
  'child' => 
    array (size=1)
      'name' => string 'child model' (length=11)


```



### Serviceable Model

A [Serviceable Model](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-ServiceableModel.html) extends the 
[DefaultModel](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-DefaultModel.html), and modifies the DefaultModel constructor 
to accept zero or more IModelPropertyProvider instances.  The passed providers are associated with properties defined within the parent model configuration, and 
will handle loading and saving of the associated model(s).  


### Serviceable Repository


[Serviceable Repository](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ServiceableRepository.html) 
and [SQL Serviceable Repository](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-SQLServiceableRepository.html) (for SQL repositories)
are repository decorators, which add support for IModelPropertyProviders.  When models are created in the repository object factory, the model property provider 
instances are passed to the ServiceableModel constructor.  Additionally, when the repository save method is called, the model property providers save functions will be included 
as part of the save transaction.



The next section will describe the model property providers included with Magic Graph.


---


## Relationships

See [Model service providers](#model-relationship-providers) for information about model properties and IModelPropertyProvider.

The following tables are used in the One to One and One to Many example sections:

```sql
-- Parent Table
create table table1 (
  id int not null primary key auto_increment,
  name varchar(20) not null,
  childid int not null
) engine=innodb;


-- Child / linked table 
create table table2 (
  id int not null auto_increment,
  link_table1 int not null,
  name varchar(20) not null,
  primary key (link_table1, id),
  key id(id)
) engine=innodb;

--Insert the parent model record
insert into table1 (name,childid) values ('Parent',1);

--insert the child model records
insert into table2 (link_table1, name) values(last_insert_id(),'Child 1'),(last_insert_id(),'Child 2');
```



### One to One

The [OneOnePropertyService](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-OneOnePropertyService.html) provides
the ability to load, attach, edit and save a single associated model.  


```php

//..When using model property providers / relationships, models MUST extend ServiceableModel.  ServiceableModel 
//  extends DefaultModel, and adds the required functionality for relationships.
//..Table1 Model
class Table1Model extends \buffalokiwi\magicgraph\ServiceableModel {};

//..Table2 Model
class Table2Model extends \buffalokiwi\magicgraph\ServiceableModel {};


//..Create a SQL Database connection 
$dbFactory = new buffalokiwi\magicgraph\pdo\PDOConnectionFactory( //..A factory for managing and sharing connection instances 
  new buffalokiwi\magicgraph\pdo\MariaConnectionProperties(       //..Connection properties for MariaDB / MySQL
    'localhost',                  //..Database server host name 
    'root',                       //..User name
    '',                           //..Password
    'retailrack' ),               //..Database 
  //..This is the factory method, which is used to create database connection instances
  //..The above-defined connection arguments are passed to the closure.
  function( buffalokiwi\magicgraph\pdo\IConnectionProperties $args  ) { 
    //..Return a MariaDB connection 
    return new buffalokiwi\magicgraph\pdo\MariaDBConnection( $args );
  }
);


//..Create the transaction factory
$tFact = new \buffalokiwi\magicgraph\persist\DefaultTransactionFactory();


//..Table2 Repository
//..This must be initialized prior to Table1Repo because Table1Repo depends on Table2Repo
$table2Repo = new buffalokiwi\magicgraph\persist\DefaultSQLRepository(
  'table2', 
  $dbFactory->getConnection(),
  Table2Model::class,
  $table2Properties
);

//..Create properties for Table1Model
$table1Properties = new buffalokiwi\magicgraph\property\QuickPropertySet([
  //..Primary key
  'id' => [
      'type' => 'int',
      'flags' => ['primary']
  ],
    
  //..A name 
  'name' => [
      'type' => 'string'      
  ],
    
   //..Property containing the primary key for a Table2Model 
  'childid' => [
      'type' => 'int',
      'value' => 0
  ],
    
  //..Child model property.
  //..A model from Table2Repository is pulled by the id defined in the "childid" property
  'child' => [
    'type' => 'model',
    'flags' => ['noinsert','noupdate','null'],  //..Since Table2Model requires constructor arguments, we'll pass null here.
    'clazz' => Table2Model::class
  ]
]);


$table1Repo = new \buffalokiwi\magicgraph\persist\DefaultSQLServiceableRepository(
    'table1', //..SQL table name 
    $dbFactory->getConnection(), //..SQL database connection 
    Table1Model::class, //..The Table1Model class name used for the object factory 
    $table1Properties,  //..Properties used to create Table1Model instances
    $tFact, //..Transaction factory used to handle saving across multiple model property providers 
    new \buffalokiwi\magicgraph\OneOnePropertyService( new \buffalokiwi\magicgraph\OneOnePropSvcCfg(
      $table2Repo,
      'childid',
      'child'
)));        

//..Get the only record in table1
$model = $table1Repo->get('1');

//..Print the model contents with related child models 
var_dump( $model->toArray( null, true, true ));


Outputs:
array (size=4)
  'id' => string '1' (length=1)
  'name' => string 'Parent' (length=6)
  'childid' => string '1' (length=1)
  'child' => 
    array (size=3)
      'id' => string '1' (length=1)
      'link_table1' => string '1' (length=1)
      'name' => string 'Child 1' (length=7)
```



### One to Many

The [OneManyPropertyService](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-OneManyPropertyService.html) 
provides the ability to load, attach, edit and save multiple associated models.  


```php

//..When using model property providers / relationships, models MUST extend ServiceableModel.  ServiceableModel 
//  extends DefaultModel, and adds the required functionality for relationships.
//..Table1 Model
class Table1Model extends \buffalokiwi\magicgraph\ServiceableModel {};

//..Table2 Model
class Table2Model extends \buffalokiwi\magicgraph\ServiceableModel {};


//..Model properties for Table1
$table1Properties = new buffalokiwi\magicgraph\property\QuickPropertySet([
  'id' => [
      'type' => 'int',
      'flags' => ['primary']
  ],
    
  'name' => [
      'type' => 'string'      
  ],
    
  'children' => [
    'type' => 'array',
    'flags' => ['noinsert','noupdate'],
    'clazz' => Table2Model::class
  ]
]);


//..Model properties for table 2 
$table2Properties = new buffalokiwi\magicgraph\property\QuickPropertySet([
  'id' => [
      'type' => 'int',
      'flags' => ['primary']
  ],
   
  'link_table1' => [
      'type' => 'int',
      'value' => 0
  ],
    
  'name' => [
      'type' => 'string'      
  ]
]);

//..Create a SQL Database connection 
$dbFactory = new buffalokiwi\magicgraph\pdo\PDOConnectionFactory( //..A factory for managing and sharing connection instances 
  new buffalokiwi\magicgraph\pdo\MariaConnectionProperties(       //..Connection properties for MariaDB / MySQL
    'localhost',                  //..Database server host name 
    'root',                       //..User name
    '',                           //..Password
    'retailrack' ),               //..Database 
  //..This is the factory method, which is used to create database connection instances
  //..The above-defined connection arguments are passed to the closure.
  function( buffalokiwi\magicgraph\pdo\IConnectionProperties $args  ) { 
    //..Return a MariaDB connection 
    return new buffalokiwi\magicgraph\pdo\MariaDBConnection( $args );
  }
);


//..Create the transaction factory
$tFact = new \buffalokiwi\magicgraph\persist\DefaultTransactionFactory();


//..Table2 Repository
//..This must be initialized prior to Table1Repo because Table1Repo depends on Table2Repo
$table2Repo = new buffalokiwi\magicgraph\persist\DefaultSQLRepository(
  'table2', 
  $dbFactory->getConnection(),
  Table2Model::class,
  $table2Properties
);

//..Table1 Repository
//..A sql database repository that can include model property providers used for relationships
$table1Repo = new \buffalokiwi\magicgraph\persist\DefaultSQLServiceableRepository( 
    'table1', //..SQL table name 
    $dbFactory->getConnection(), //..SQL database connection 
    Table1Model::class, //..The Table1Model class name used for the object factory 
    $table1Properties,  //..Properties used to create Table1Model instances
    $tFact, //..Transaction factory used to handle saving across multiple model property providers 
    new buffalokiwi\magicgraph\OneManyPropertyService( //..This handles loading and saving related models 
      new buffalokiwi\magicgraph\OneManyPropSvcCfg( //..Configuration 
        $table2Repo,    //..Linked model repository //$parentIdProperty, $arrayProperty, $linkEntityProperty, $idProperty)
        'id',           //..The parent model primary key property name.
        'children',     //..The parent model property name for the array of linked models
        'link_table1',  //..A linked model property that contains the parent id
        'id' )          //..A linked model property containing the unique id of the linked model
));


//..Get the only record in table1
$model = $table1Repo->get('1');

//..Print the model contents with related child models 
var_dump( $model->toArray( null, true, true ));


Outputs:

array (size=3)
  'id' => string '1' (length=1)
  'name' => string 'Parent' (length=6)
  'children' => 
    array (size=2)
      0 => 
        array (size=3)
          'id' => string '1' (length=1)
          'link_table1' => string '1' (length=1)
          'name' => string 'Child 1' (length=7)
      1 => 
        array (size=3)
          'id' => string '2' (length=1)
          'link_table1' => string '1' (length=1)
          'name' => string 'Child 2' (length=7)
```


### Many to Many 

Sometimes we have lots of things that can map to lots of other things.  For example, in can ecommerce setting, 
products may map to multiple categories, and categories may contain multiple products.  In this instance, we would 
require a junction table to store those mappings.  Thankfully, this is fairly easy in Magic Graph.


First, we start with a standard junction table.  If we use the following table definition, we can use the built in 
models for a junction table.

```sql

CREATE TABLE `product_category_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link_parent` int(11),
  `link_target` int(11),
  PRIMARY KEY (`link_parent`,`link_target`),
  KEY `id` (`id`)
) ENGINE=InnoDB 

```

1. "id" contains the primary key
2. "link_parent" is the id of the parent model.  ie: a product id
3. "link_target" is the id of the target model.  ie: a category id


Now we create two other tables.  One for parent and the other one for the target.  For fun, we'll add a name column to both.


```sql 

create table `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) not null,
  primary key (id)
) ENGINE=InnoDB;


insert into product (name) values ('product1');
insert into product (name) values ('product2');

```


The category table 

```sql 

create table `product_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) not null,
  primary key (id)
) ENGINE=InnoDB;


insert into product_category (name) values ('category1');
insert into product_category (name) values ('category2');

```

Now we add product1 to category1, and product2 to category2

```sql

insert into product_category_link (link_parent,link_target) values (1,1),(2,2);

```




```php


//..Define the category model
class CategoryModel extends \buffalokiwi\magicgraph\ServiceableModel {}

//..Create the category model property configuration 
//..In this instance, we are using QuickJunctionPropertyConfig because we want to use this model as a junction table target
//..QuickJunctionPropertyConfig implements IJunctionTargetProperties, which exposes the primary id property name and is used 
//  to generate database queries.
$cProps = new \buffalokiwi\magicgraph\property\QuickPropertySet( new \buffalokiwi\magicgraph\junctionprovider\QuickJunctionPropertyConfig([
    'id' => [
        'type' => 'int',
        'flags' => ['primary']
    ],

    'name' => [
        'type' => 'string'      
    ],

    //..This is the list of products contained within a category
    'products' => [
      'type' => 'array',
      'flags' => ['noinsert','noupdate'],
      'clazz' => ProductModel::class
    ]        
  ], 
  'id' //..Primary key property name used as the junction link target 
));


//..Define the product model 
class ProductModel extends \buffalokiwi\magicgraph\ServiceableModel {}



//..Create the product model property configuration 
$pProps =  new \buffalokiwi\magicgraph\property\QuickPropertySet( new \buffalokiwi\magicgraph\junctionprovider\QuickJunctionPropertyConfig([
    'id' => [
        'type' => 'int',
        'flags' => ['primary']
    ],

    'name' => [
        'type' => 'string'      
    ],

    //..The list of categories containing some product
    'categories' => [
      'type' => 'array',
      'flags' => ['noinsert','noupdate'],
      'clazz' => CategoryModel::class
    ]        
  ],
  'id' //..Primary key property name used as the junction link target 
));


//..Create the transaction factory
$tFact = new \buffalokiwi\magicgraph\persist\DefaultTransactionFactory();
    

//..Create a SQL Database connection 
$dbFactory = new buffalokiwi\magicgraph\pdo\PDOConnectionFactory( //..A factory for managing and sharing connection instances 
  new buffalokiwi\magicgraph\pdo\MariaConnectionProperties(       //..Connection properties for MariaDB / MySQL
    'localhost',                  //..Database server host name 
    'root',                       //..User name
    '',                           //..Password
    'retailrack' ),               //..Database 
  //..This is the factory method, which is used to create database connection instances
  //..The above-defined connection arguments are passed to the closure.
  function( buffalokiwi\magicgraph\pdo\IConnectionProperties $args  ) { 
    //..Return a MariaDB connection 
    return new buffalokiwi\magicgraph\pdo\MariaDBConnection( $args );
  }
);


//..Create the repository for the junction table 
$jRepo = new buffalokiwi\magicgraph\junctionprovider\DefaultMySQLJunctionRepo(
  'product_category_link',
  $dbFactory->getConnection()
);
  

//..Create the product repository 
$pRepo = new buffalokiwi\magicgraph\persist\DefaultSQLServiceableRepository(
  'product',
  $dbFactory->getConnection(),
  ProductModel::class,
  $pProps,
  $tFact
);


//..Create the category repository 
$cRepo = new buffalokiwi\magicgraph\persist\DefaultSQLServiceableRepository(
  'product_category',
  $dbFactory->getConnection(),
  CategoryModel::class,
  $cProps,
  $tFact
);


//..Since we want both models to reference each other, we cannot instantiate the junction providers until
//  both parent and target repositories have been created.
//..There is a handy method for adding these: addModelPropertyProvider()
//
//..If we were only referencing the target models in the parent repository or vice versa, we would have passed the junction
//..model instance directly to the serviceable repository constructor 


//..Add the junction model property provider 
$pRepo->addModelPropertyProvider(
  new buffalokiwi\magicgraph\junctionprovider\MySQLJunctionPropertyService( 
    new buffalokiwi\magicgraph\junctionprovider\JunctionModelPropSvcCfg(
      'id',
      'categories' ),
    $jRepo,
    $cRepo
));


$cRepo->addModelPropertyProvider(
  new buffalokiwi\magicgraph\junctionprovider\MySQLJunctionPropertyService( 
    new buffalokiwi\magicgraph\junctionprovider\JunctionModelPropSvcCfg(
      'id',
      'products' ),
    $jRepo,
    $pRepo
));


//..Get and print the product model 
$p1 = $pRepo->get('1');
var_dump( $p1->toArray(null,true,true));

Outputs:

array (size=3)
  'id' => int 1
  'name' => string 'product1' (length=8)
  'categories' => 
    array (size=1)
      0 => 
        array (size=3)
          'id' => int 1
          'name' => string 'category1' (length=9)
          'products' => 
            array (size=1)
              0 => 
                array (size=3)
                  'id' => int 1
                  'name' => string 'product1' (length=8)
                  'categories' => 
                    array (size=1)
                      ...


//..Get and print the category model
$c1 = $cRepo->get('1');
var_dump( $p1->toArray(null,true,true));


Outputs:

array (size=3)
  'id' => int 1
  'name' => string 'category1' (length=9)
  'products' => 
    array (size=1)
      0 => 
        array (size=3)
          'id' => int 1
          'name' => string 'product1' (length=8)
          'categories' => 
            array (size=1)
              0 => 
                array (size=3)
                  'id' => int 1
                  'name' => string 'category1' (length=9)
                  'products' => 
                    array (size=1)
                      ...


```



### Nested Relationship Providers

Nesting is accomplished by using the same methods outlined in the [Relationships](#relationships) chapter.  

As I'm sure you've noticed in the above many to many example, relationship providers can be used to create a series of nested
objects.  Relationship providers can be plugged into any property in any model, which means we can use them to create a 
snazzy tree of objects.  Relationship providers can be used to back any model property 

First, we start by creating 3 simple tables.  For this example, the tables will only contain an id column.

Create some tables and insert a few values.  To keep this simple, we will use an id of "1" for everything.

```sql

  
create table tablea( id int, primary key(id) ) engine=innodb;
create table tableb( id int, primary key(id) ) engine=innodb;
create table tablec( id int, primary key(id) ) engine=innodb;

insert into tablea values(1);
insert into tableb values(1);
insert into tablec values(1);

```


Next, we create a serviceable model and corresponding property set for each of the tables.  We are going to assume that 
we have a variable $dbFactory, which is an instance of IConnectionFactory.  There is also a variable $tfact,which is an 
instance of ITransactionFactory.  These are detailed in examples from previous chapters.


```php

class Table1Model extends buffalokiwi\magicgraph\ServiceableModel {}
class Table2Model extends buffalokiwi\magicgraph\ServiceableModel {}
class Table3Model extends buffalokiwi\magicgraph\DefaultModel {}

$t1Props = new buffalokiwi\magicgraph\property\QuickPropertySet([
   'id' => [
       'type' => 'int',
       'flags' => ['primary']
   ],
    
   'table2model' => [
       'type' => 'model',
       'clazz' => Table2Model::class,
       'flags' => ['noinsert','noupdate','null'], //..Table2Model requires constructor arguments. Use null here.
   ]
]);


$t2Props = new buffalokiwi\magicgraph\property\QuickPropertySet([
   'id' => [
       'type' => 'int',
       'flags' => ['primary']
   ],
    
   'table3model' => [
       'type' => 'model',
       'clazz' => Table3Model::class,
       'flags' => ['noinsert','noupdate','null'], //..Table3Model requires constructor arguments. Use null here.
   ]
]);

$t3Props = new buffalokiwi\magicgraph\property\QuickPropertySet([
   'id' => [
       'type' => 'int',
       'flags' => ['primary']
   ]
]);

```

After creating the models, we will need to create a repository for each type of model.  For this, we will use the DefaultSQLServiceableRepository, 
which along with ServiceableModel, allows us to use relationship providers.  Repositories controlling models located at the 
edges of the object graph will need to be created first.  ie: TableC, then TableB, then TableA.


```php

//..There are no relationships in tableC
$t3Repo = new buffalokiwi\magicgraph\persist\DefaultSQLRepository(
  'tablec',
  $dbFactory->getConnection(),
  Table3Model::class,
  $t3Props,
);

$t2Repo = new buffalokiwi\magicgraph\persist\DefaultSQLServiceableRepository(
  'tableb',
  $dbFactory->getConnection(),
  Table2Model::class,
  $t2Props,
  $tfact,
  new buffalokiwi\magicgraph\OneOnePropertyService( new \buffalokiwi\magicgraph\OneOnePropSvcCfg(
    $t3Repo,
    'id',
    'table3model'
)));

$t1Repo = new buffalokiwi\magicgraph\persist\DefaultSQLServiceableRepository(
  'tablea',
  $dbFactory->getConnection(),
  Table1Model::class,
  $t1Props,
  $tfact,
  new buffalokiwi\magicgraph\OneOnePropertyService( new \buffalokiwi\magicgraph\OneOnePropSvcCfg(
    $t2Repo,
    'id',
    'table2model'
)));

```

Finally, we get the model from tablea, and we print the graph:

```php

$model1 = $t1Repo->get("1");

var_dump( $model1->toArray( null, true, true ));

Outputs:

array (size=2)
  'id' => int 1
  'table2model' => 
    array (size=2)
      'id' => int 1
      'table3model' => 
        array (size=1)
          'id' => int 1

```

Any relationship provider will work in exactly the same way as the one to one provider.



### How Editing and Saving Works


As detailed in the [Saveable Mapping Object Factory](#saveable-mapping-object-factory) section, models can be saved 
somewhere by calling the [ISaveableObjectFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ISaveableObjectFactory.html) 
save method.  This section will deal with how editing and saving works when using relationship providers.

Editing and saving is controlled both by the relationship provider and the edited property tracking system built into 
[ServiceableModel](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-DefaultModel.html#method_hasEdits).  
When a serviceable model is saved by some serviceable repository, calling the save method causes the serviceable repository to 
fetch save functions from the relationship providers.  

Relationship providers must implement the [IModelPropertyProvider](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-IModelPropertyProvider.html)
interface.  The method IModelPropertyProvider::getSaveFunction will return a function which is passed to a [ITransactionFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ITransactionFactory.html) 
where the save function is called, and the related model is persisted.

The [One to One](#one-to-one) relationship provider is relatively straightforward.  Any model properties backed by a
[OneOnePropertyService](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-OneOnePropertyService.html) 
will be automatically saved when the parent model is saved via some repository.  This will work with new models and also existing 
models loaded by the provider.

The [One to Many](#one-to-many) and [Many to Many](#many-to-many) relationship providers will manage array properties 
containing IModel instances.  This is slightly more complicated than the one to one provider.  In addition to saving 
edited models, the one to many and many to many providers will also manage inserts and deletes.  If new models are added to the 
array property, they will be inserted into the database.  If an existing model is removed from the array property, it 
will be deleted from the database.  If any filters or limits are used when loading related models, the delete functionality 
is disabled, and models must be manually unlinked via the repository controlling that model type.

Saves will automatically cascade when using nested relationship providers.  Any nested model at any position in the object 
graph can be edited, and when the top-most model is saved, it will 


---


## Extensible Models

We're finally through the foundational concepts, woohoo!  

Magic Graph models are designed to be as flexible as possible.  As I'm sure you've noticed, there are several ways to 
configure models, and each of those ways have different levels of extensibility.  For example, models can be created by 
using simple property annotations, or they can be created at runtime by using property configuration objects.  While using
the annotated properties is quick and easy, it is nowhere near as scalable as using property configuration objects.  


Through the use of property configuration objects we can:

1. Define the properties that will existing within a given model
2. Provide run time type information for properties.  For example, config objects can implement methods to return property names, which 
can be used to query property meta data in a model's property set.
3. Add additional meta data to properties 
4. Provide the ability to swap out the list of properties used for persistence.  For example, if we want to share a model 
between multiple persistence types that have different property names, we can swap out the configuration object.
5. Attach simple behaviors to individual properties.  ie: get, set, change, etc.
6. Extend save functionality through functions like: before save, after save, on save, save function, etc.
7. Property configuration can be dynamically generated at runtime, which allows us to implement patterns such as EAV.
8. Multiple property configuration objects can be used to create a single model.  This allows developers to create 
model extensions or to separate concerns into different packages.


### Property Configuration Interface

For configuration array definitions, please see [Property Configuration](#property-configuration).  

Property configuration objects must all implement the [IPropertyConfig](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IPropertyConfig.html) 
interface.  This interface is used by implementations of [IPropertySetFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IPropertySetFactory.html) 
to create [IPropertySet](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IPropertySet.html) instances, 
which contain all of the relevant properties, meta data, and behaviors used by [IModel](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-IModel.html) instances.

The IPropertyConfig interface currently contains four methods:


```php
getConfig(): array
```
getConfig() is called by IPropertySetFactory, and returns the property configuration array.  This array contains 
all of the property definitions, meta data, and optional event handlers.


```php
getPropertyNames(): array
```
getPropertyNames() will return a list of strings containing each property name defined by this configuration object.


```php
beforeSave( IModel $model ) : void;
```
beforeSave() is called by an IRepository prior to a model being persisted.  This is an opportunity to modify the model's 
state or add additional validation prior to save.  When creating the beforeSave() handler, the IPropertyConfig implementation
SHOULD iterate over the properties defined in the configuration array, and call each property-level beforeSave handler.


```php
afterSave( IModel $model ) : void;
```
afterSave() is called by an IRepository after a model has been persisted, but before commit().  This can be used to clean 
up after a save, check the results of a save, etc.  When creating the afterSave() handler, the IPropertyConfig implementation
SHOULD iterate over the properties defined in the configuration array, and call each property-level afterSave handler.



### Property Configuration Implementation

Now that we know how a property configuration object, and the configuration array is defined, let's build out a complete
implementation.  Magic Graph ships with a abstract base class 
[BasePropertyConfig](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-BasePropertyConfig.html), 
which contains constants for commonly used property configurations and adds the ability to incorporate behavioral strategies by 
passing [INamedPropertyBehavior](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-INamedPropertyBehavior.html) instances to the constructor.


Let's make a basic rectangle model.  In this example, we will create two classes: Rectangle and RectangleProperties.  
Rectangle is the value object, and RectangleProperties defines the properties contained within the Rectangle value object.


```php
/**
 * Property configuration for a rectangle value object
 */
class RectangleProperties extends buffalokiwi\magicgraph\property\BasePropertyConfig
{
  /**
   * Height property name 
   */
  const HEIGHT = 'height';
  
  /**
   * Width property name 
   */
  const WIDTH = 'width';
  
  
  /**
   * Returns the property configuration array 
   * @return array 
   */
  protected function createConfig() : array
  {
    return [
      self::HEIGHT => self::FINTEGER_REQUIRED,
      self::WIDTH => self::FINTEGER_REQUIRED
    ];
  }
}


/**
 * Rectangle Value Object
 */
class Rectangle extends buffalokiwi\magicgraph\GenericModel {}

//..Create the rectangle model instance 
$rectangle = new Rectangle( new RectangleProperties());


/**
 * Outputs:
 * array (size=2)
 *   'height' => int 0
 *   'width' => int 0
 */
var_dump( $rectangle->toArray());

/**
 * Throws Exception with message: 
 * "height" property of class "Rectangle" of type "int" is REQUIRED and must not be empty.
 */
$rectangle->validate();

```

The above example is fairly straightforward.  A configuration object defines two required properties, height and width.
When the model is instantiated, height and width are both zero.  This is because the default value each property is zero, and 
default values will bypass property validation.  When IModel::validate() is called, both properties are validated and will 
throw a [ValidationException](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-ValidationException.html).

That's great and all, but properties should validate when they are set, right?  The required property flag will only validate
when IModel::validate() is called, so if we want to validate when the property is set, we must add a validation callback.

We can rewrite the createConfig function like this:

```php
protected function createConfig() : array
{
  //..Validation callback that will throw an exception when setting an integer property value to zero
  $vInt = fn( buffalokiwi\magicgraph\property\IProperty $prop, int $value ) : bool => !empty( $value );

  return [
    self::HEIGHT => self::FINTEGER_REQUIRED + [self::VALIDATE => $vInt],
    self::WIDTH => self::FINTEGER_REQUIRED + [self::VALIDATE => $vInt]
  ];
}
```

After the model as been created using the above change, setting height or width equal to zero will throw an exception.
This does not affect the default property value of zero.  Default values will bypass validation when a property instance is created.

```php
//..Set height to zero
$rectangle->height = 0;

//..Throws an exception like:
//  Behavior validation failure in closure: RectangleProperties in file test.php on line 71
```


What if we wanted to make a rectangle behave as a square?  Since square is a specialization of a rectangle, we can 
create a behavioral strategy which will be used to enforce the height must equal width rule.  When height is set, width 
will automatically be set to the value of height and vise versa.  


First we will want to create an interface for RectangleProperties.  This will define two methods getHeight() and getWidth(), which 
will return the model property names for height and width.  This interface is how we will ensure that only rectangles 
are used with the behavioral strategy, and it's also a great way to decouple property names from the database column names.


```php
/**
 * This interface defines a property configuration object for a Rectangle.
 */
interface IRectangleProperties extends \buffalokiwi\magicgraph\property\IPropertyConfig
{
  /**
   * Get the height property name 
   * @return string
   */
  public function getHeight() : string;
  
  
  /**
   * Get the width property name 
   * @return string
   */
  public function getWidth() : string;
}
```


And our modified RectangleProperties class now looks like this:

```php
/**
 * Property configuration for a rectangle value object
 */
class RectangleProperties extends buffalokiwi\magicgraph\property\BasePropertyConfig implements IRectangleProperties
{
  /**
   * Height property name in the database
   */
  const HEIGHT = 'height';
  
  /**
   * Width property name in the database 
   */
  const WIDTH = 'width';
  
  
  /**
   * Get the height property name 
   * @return string
   */
  public function getHeight() : string
  {
    return self::HEIGHT;
  }
  
  
  /**
   * Get the width property name 
   * @return string
   */
  public function getWidth() : string
  {
    return self::WIDTH;
  }
  
  
  /**
   * Returns the property configuration array 
   * @return array 
   */
  protected function createConfig() : array
  {
    //..Zero is no longer allowed
    $vInt = fn( buffalokiwi\magicgraph\property\IProperty $prop, int $value ) : bool => !empty( $value );
    
    return [
      self::HEIGHT => self::FINTEGER_REQUIRED + [self::VALIDATE => $vInt],
      self::WIDTH => self::FINTEGER_REQUIRED + [self::VALIDATE => $vInt]
    ];
  }
}
```


Now that the property configuration is properly configured, we can create a behavioral strategy that will make 
height always equal to width in any model that uses IRectangleProperties.  To accomplish this, we will create a class
called BehaveAsSquare, which descends from GenericNamedPropertyBehavior.  GenericNamedPropertyBehavior is normally 
used to attach behavior to a single property.  

For our square behavior, we want to use the model setter callback 
(called any time a property value is set by IModel::setValue()).  This means we need to pass static::class to 
the GenericNamedPropertyBehavior constructor.  When the property name is equal to the class name, the behavior will 
be applied to every property in a model.  This will allow us to write a single handler for multiple properties.


```php
/**
 * Causes rectangles to behave as squares.
 * This uses the model setter callback to force height and width to always be equal.
 */
class BehaveAsSquare extends buffalokiwi\magicgraph\property\GenericNamedPropertyBehavior
{
  /**
   * Model setter callback 
   * @var \Closure
   */
  private \Closure $mSetter;
  
  
  public function __construct()
  {
    parent::__construct( static::class );
    $this->mSetter = $this->createModelSetterCallback();
  }
  
  
  /**
   * Return the model setter callback
   * @return \Closure|null
   */
  public function getModelSetterCallback(): ?\Closure
  {
    return $this->mSetter;
  }
  
  
  /**
   * Creates the model setter callback.  
   * No need to create this every time the setter is called.
   * @return \Closure
   */
  private function createModelSetterCallback() : \Closure 
  {
    //..This setter is a circular reference, so we want to know if we're already in the closure
    $inClosure = false;
    
    return function( 
      \buffalokiwi\magicgraph\IModel $model, 
      \buffalokiwi\magicgraph\property\IProperty $prop, 
      $value ) use(&$inClosure) : mixed 
    { 
      //..Return if already in closure 
      if ( $inClosure )
        return $value;      
      
      //..Set the state
      $inClosure = true;
      
      //..Get the rectangle property config 
      //..This will throw an exception if rectangleproperties are not used in the model.
      /* @var $props IRectangleProperties */
      $props = $model->getPropertyConfig( IRectangleProperties::class );
      
      //..Set the other dimension 
      switch( $prop->getName())
      {
        case $props->getHeight():
          $model->setValue( $props->getWidth(), $value );
        break;

        case $props->getWidth():
          $model->setValue( $props->getHeight(), $value );
        break;
      }

      try {
        return $value;
      } finally {
        //..Reset state
        $inClosure = false;
      }
    };    
  }
}
```

To make our rectangle behave as a square, we can initialize it like this:

```php
//..Create the rectangle model instance and make it a square 
$rectangle = new Rectangle( new RectangleProperties( new BehaveAsSquare()));

//..Set one dimension
$rectangle->height = 10;

/**
 * Outputs:
 * array (size=2)
 *   'height' => int 10
 *   'width' => int 10
 */
var_dump( $rectangle->toArray());
```

Since we all know a rectangle and a square aren't the same thing, we can use the same property configuration, and our new behavioral 
strategy to create two new models: Rectangle and Square.

```php

/**
 * Rectangle Value Object
 */
class Rectangle extends buffalokiwi\magicgraph\GenericModel 
{
  private IRectangleProperties $props;
         
  public function __construct( \buffalokiwi\magicgraph\property\IPropertyConfig ...$config )
  {
    parent::__construct( ...$config );
    //..Here we ensure that the model is actually a rectangle, and we get the property names.
    $this->props = $this->getPropertyConfig( IRectangleProperties::class );
  }
  

  /**
   * Sets the rectangle dimensions 
   * @param int $height Height 
   * @param int $width Width 
   * @return void
   */      
  public function setDimensions( int $height, int $width ) : void
  {
    $this->setValue( $this->props->getHeight(), $height );
    $this->setValue( $this->props->getWidth(), $width );
  }
  
  
  /**
   * Gets the height 
   * @return int
   */
  public function getHeight() : int
  {
    return $this->getValue( $this->props->getHeight());
  }
  
  
  /**
   * Gets the width 
   * @return int
   */
  public function getWidth() : int
  {
    return $this->getValue( $this->props->getWidth());
  }
}


/**
 * Square value object
 * Height and width are always equal 
 */
class Square extends buffalokiwi\magicgraph\GenericModel 
{
  private IRectangleProperties $props;
         
  public function __construct( \buffalokiwi\magicgraph\property\IPropertyConfig ...$config )
  {
    parent::__construct( ...$config );
    //..Here we ensure that the model is actually a rectangle, and we get the property names.
    $this->props = $this->getPropertyConfig( IRectangleProperties::class );
  }
  

  /**
   * Sets the rectangle dimensions 
   * @param int $height Height 
   * @param int $width Width 
   * @return void
   */      
  public function setDimension( int $heightAndWidth ) : void
  {
    //..Our BehaveAsSquare will handle this 
    //..We could have just as easily set both properties here, but this is an example of how strategies work.
    $this->setValue( $this->props->getHeight(), $heightAndWidth );
  }
  
  
  /**
   * Gets the height 
   * @return int
   */
  public function getHeight() : int
  {
    return $this->getValue( $this->props->getHeight());
  }  
}
```

And finally, we can create an instance of a square:

```php
$square = new Square( new RectangleProperties( new BehaveAsSquare()));

$square->setDimension( 10 );

/**
 * Outputs:
 * array (size=2)
 *   'height' => int 10
 *   'width' => int 10
 */
var_dump( $square->toArray());
```


### Using multiple property configurations


It's possible to use multiple IPropertyConfig objects to create a single model.  This is one of the more useful features
of property configuration objects. It's possible for one package to define a model, and have other packages extend that 
model by adding properties and behavior.  Each property configuration can also incorporate any inline event handlers and
zero or more behavioral strategies.  Think of this as a sort of plugin system.  Here's an example:

First we create two property configurations:

```php
class FooProps extends buffalokiwi\magicgraph\property\BasePropertyConfig
{
  protected function createConfig(): array
  {
    return [
      'foo' => self::FSTRING
    ];
  }
}


class BarProps extends buffalokiwi\magicgraph\property\BasePropertyConfig
{
  protected function createConfig(): array
  {
    return [
      'bar' => self::FSTRING
    ];
  }  
}
```

Now we can pass an instance of each configuration object to a model (or property set) constructor.

```php
//..Create the model instance with both property configuration objects 
$model = new buffalokiwi\magicgraph\GenericModel( new FooProps(), new BarProps());


/**
 * Outputs:
 * array (size=2)
 *   'foo' => string '' (length=0)
 *   'bar' => string '' (length=0)
 */
var_dump( $model->toArray());
```

Properties from both configurations will appear in the model.

We can also add properties at runtime.  Here's a third configuration we'll add to the model.

```php
class BazProps extends buffalokiwi\magicgraph\property\BasePropertyConfig
{
  protected function createConfig(): array
  {
    return [
      'baz' => self::FSTRING
    ];
  }  
}
```

Adding a configuration object at runtime is done through the property set:

```php
$model->getPropertySet()->addPropertyConfig( new BazProps());

/**
 * Outputs:
 * array (size=2)
 *   'foo' => string '' (length=0)
 *   'bar' => string '' (length=0)
 *   'baz' => string '' (length=0)
 */
var_dump( $model->toArray());
```


---


### Model Interface 

All models in Magic Graph must implement the [IModel](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-IModel.html) interface. 
The interface itself is fairly simple and straightforward.  

IModel focuses on a few key areas, Properties, Validation, State, Serialization and Cloning:

1. **Properties**
    1. **instanceOf()** - Tests that a IPropertyConfig instance is a or implements the supplied class or interface name.  This is used to test if a model is "of some type".
    2. **getPropertySet()** - Retrieve the internal IPropertySet instance containing the properties used in the model
    3. **getPropertyNameSet()** - Retrieve an instance of [IBigSet](https://github.com/SixArmDonkey/buffalotools_types#bigset) containing a list of property names in the property set.  This is used for 
methods that utilize model property names.  ie: toArray() can return a limited list of properties by supplying an instance of 
IBigSet containing active bits for each of the desired properties.
    4. **getPropertyNameSetByFlags()** - The same as getPropertyNameSet() and includes the ability to filter by enabled property flags.
    5. **getPropertyConfig()** - Retrieve an array containing the property configuration used to create the model properties
    6. **getIterator()** - Retrieve an iterator used to iterate over any non-array and non-model properties and values contained in the model.
2. **Validation**
    1. **validate()** - Individually validates each property, and the first property to test as invalid will throw a ValidationException
    2. **validateAll()** - Validates each property in the model and stores the results in a list.  Properties with failed validation are returned as a map.
3. **State**
    1. **getModifiedProperties()** - Retrieve an instance of IBigSet with the bits for any edited properties enabled 
    2. **getInsertProperties()** - Retrieve an instance of IBigSet with the bits for any properties required for a database "insert".
    3. **hasEdits()** - Tests if any properties have been edited since initialization 
4. **Serialization**
    1. **toArray()** - Used for persistence, debugging and other fun things.  Converts the IModel instance into a multi-dimensional array.
    2. **toObject()** - Used for JSON Serialization, converts IModel to an object graph.
    3. **fromArray()** - Used to initialize the model with data from the persistence layer.  Populates any matching IModel properties with the supplied values.
    4. **jsonSerialize()** - Usually calls toObject().
5. **Cloning**
    1. **__clone()** - IModel instances are cloneable.
    2. **createCopy()** - Preferred over __clone, this can be used to clone or copy (without primary keys) models and also cause them to be read only.


### Model Implementation 

Models are composed of a few components, a property set containing all various object property instances and the model
implementation.  Currently, every property set extends [DefaultPropertySet](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-DefaultPropertySet.html) 
and every model extends [DefaultModel](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-DefaultModel.html).

It is worth noting that DefaultModel contains quite a bit of functionality.  Instead of directly implementing IModel, it 
is recommended to extend all models from DefaultModel.  

At the time of writing, Magic Graph ships with 7 IModel implementations and two decorators:

1. **DefaultModel** - The base model.  Every model should extend from this class.  
    1. **ServiceableModel** - Extends DefaultModel and adds the necessary functionality required to support relationship providers.
    2. **AnnotatedModel** - Can use attributes in php8 to configure and initialize model properties.  
    3. **GenericModel** - Quick way to create a model using IPropertyConfig.
    4. **QuickModel** - Quick way to create a model with nothing other than the property configuration array.
    5. **QuickServiceableModel** 
    6. **ProxyModel** - Used to decorate IModel instances 
        1. **ReadOnlyModelWrapper** - A decorator for IModel that disables setting property values
        2. **ServiceableModelWrapper** - A decorator for IModel that can add relationship providers to model instances.

The quick and generic model variants are easier to instantiate, but using these models prevents you from selecting the 
property set, config mapper and property factory.  Internally, quick and generic models all use instances of DefaultPropertySet, DefaultConfigMapper and PropertyFactory.


---


## Behavioral Strategies

This has already been detailed in the [Property Behavior](#property-behavior) section, but since this might be the 
most important topic in all of Magic Graph, we're going to go over it again.

The goals of behavioral strategies are the following:

1. Reduce the complexity of models
2. Increase the ease of writing tests
3. Introduce or replace functionality without extending or modifying the model 


We've all seen models that try to do it all.  The messy code, the stinky code.  Things like support for third party packages 
hacked into models, ignoring separation of concerns, or referencing objects the model should know nothing about.  There are many
solutions to these problems, but most of the time I see developers write a service used to join several packages together.  This is great and all, 
but it still tightly couples packages and adds complexity.  If repositories are in use, and there's a separate service on top of 
said repository, which one should the developer use to save the model?  What happens if some code is written that doesn't know 
about the service?  Shenanigans ensue.

Behavioral Strategies are an attempt to simplify inter-package relationships.  Think of a strategy like an adapter.  We 
can write a program with tests, then attach the program to a model.  The model and/or repository will then dispatch events, 
which the strategy program will use to either change the model's state and/or introduce side effects.  

In this context, side effects may not be a bad thing.  For example, say we have an ecommerce platform, and we want to generate a shipping
label when an order has been packaged and is ready to ship.  We could write a strategy that monitors an order's status, knows how to interact with some 
shipping api, and generates a shipping label when the order's state moves to "ready to ship".  This strategy is simply attached
to the repository and model during object creation in composition root.  We now have an independently-testable program, which 
adds support for shipping api's to the order model without needing to modify the order model, repository or create a service layer.

Behavioral strategy programs are basically event handlers for various events fired by IProperty, IModel and IRepository.  Currently, there
are a few behavior interfaces.

[IPropertyBehavior](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IPropertyBehavior.html) is primarily used by the property configuration array, and contains several callbacks related to a single property.
All callbacks will include an argument IProperty, which is the property that triggered the callback.  


**Validation Callback**

The validation callback is called any time IProperty::validate() is invoked.  

```php
function getValidationCallback() : ?Closure
{
  /**
   * Validate some property value 
   * @param buffalokiwi\magicgraph\property\IProperty $prop Property being validated
   * @param mixed $value Value to validate
   * @return bool is valid
   */
  return function( IProperty $prop, mixed $value ) : bool {
    //..Validate $value 
    return false; //..Not valid, throws an exception 
  };
}
```


**Setter Callback**

The setter callback is called before IProperty::validate().  The purpose of this callback is to modify the value prior to 
it being written to the backing property object.  Think of this as serializing a property value.
```php
function getSetterCallback() : ?Closure
{
  /**
   * Modify a property value prior to being written to the backing property
   * @param buffalokiwi\magicgraph\property\IProperty $prop Property being set 
   * @param mixed $value Value to set 
   * @return mixed modified value 
   */  
  return function( buffalokiwi\magicgraph\property\IProperty $prop, mixed $value ) : mixed {
    //..Ensure that any incoming value is a string, then append 'bar'
    return (string)$value . 'bar';
  };
}
```


**Getter Callback**

The getter callback is called prior to returning a value from IProperty::getValue().  This is to modify the value 
stored in the backing property object prior to using it.  Think of this as deserializing a property value.
Notice the $context argument on the getter callback.  IModel::setValue() contains a context argument, and this can be used
to set some arbitrary context/meta data/state/etc used in the getter callbacks.  
```php
function getGetterCallback() : ?Closure
{
  /**
   * Modify a property value prior to being written to the backing property
   * @param buffalokiwi\magicgraph\property\IProperty $prop Property being set 
   * @param mixed $value Value to set 
   * @param array $context The context 
   * @return mixed modified value 
   */  
  return function( buffalokiwi\magicgraph\property\IProperty $prop, mixed $value, array $context ) : mixed {
    //..Ensure that any incoming value is a string, then append 'bar'
    return (string)$value . 'bar';
  };
}
```


**Init Callback**

The init callback is used to modify the default value prior to it being written to the backing object.  This is called
when IProperty::reset() is called.  This is never run through IProperty::validate(), so be careful with default values.
```php
function getInitCallback() : ?Closure
{
  /**
   * Modify the default value 
   * @param mixed $value The default value 
   * @return mixed default value 
   */
  return function ( mixed $value ) : mixed {
    return $value;
  };
}
```


**Empty Callback**

The empty callback is useful in situations where empty() does not return true, but whatever the value is should still be considered empty.
For example, if the property is a object representing a primitive, then empty() would return false even if the objects internal value 
was actually empty.  
```php
function getIsEmptyCallback() : ?Closure
{
  /**
   * Basic empty check that returns true if the value is empty or the value is equal to the default property value.
   * @param buffalokiwi\magicgraph\property\IProperty $prop Property being tested
   * @param mixed $value The value to test
   * @param mixed $defaultValue The default value for the property. 
   * @return bool is empty 
   */
  return function ( buffalokiwi\magicgraph\property\IProperty $prop, mixed $value, mixed $defaultValue ) : bool {
    return empty( $value ) || $value === $defaultValue;
  };
}
```


**Change Callback**

When a property value changes, this callback is fired.  It is worth noting, that this happens at the property level, 
not inside of any models.  Therefore, this event will have no access to other properties in the model.  Due to this restriction, 
there may be limited uses for this callback.  If you need access to other properties in a model, use the model level getter/setter callbacks.
```php
function getOnChangeCallback() : ?Closure
{
  /**
   * @param buffalokiwi\magicgraph\property\IProperty $prop The property being changed
   * @param mixed $oldValue The value prior to the change
   * @param mixed $newValue The value after the change
   */
  return function ( buffalokiwi\magicgraph\property\IProperty $prop, mixed $oldValue, mixed $newValue ) : void {
    //..Do something interesting 
  };
}
```


### HTML Property Package Callbacks


**HTML Input Callback**

As part of a fun little bonus to Magic Graph, all IProperty instances can be converted into their HTML counterparts.  When 
using [IElementFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-htmlproperty-IElementFactory.html) to generate HTML inputs, this callback will be used to override the default html generated by 
the element factory.  We will go over this further in the [Creating HTML elements](#creating-html-elements] chhapter.
```php
function getHTMLInputCallback() : ?Closure
{
  /**
   * Convert IProperty to IElement for HTML output
   * @param \buffalokiwi\magicgraph\IModel $model Model property belongs to
   * @param buffalokiwi\magicgraph\property\IProperty $prop Property to convert
   * @param string $name HTML element name attribute value
   * @param string $id HTML element id attribute value 
   * @param mixed $value Property value
   * @return \buffalokiwi\magicgraph\property\htmlproperty\IElement The HTML element 
   */
  return function (
    \buffalokiwi\magicgraph\IModel $model,
    \buffalokiwi\magicgraph\property\IProperty $prop,
    string $name,
    string $id,
    mixed $value ) : \buffalokiwi\magicgraph\property\htmlproperty\IElement {
    return new buffalokiwi\magicgraph\property\htmlproperty\TextAreaElement( $name, $id, $value );
  };
}
```


### Model-level callbacks


The following callbacks are invoked by IModel implementations.

**To Array Callback**

IModel::toArray() is used for persistence and serialization.  The toArray callback is invoted when converting property values to their persisted state.
```php
function getToArrayCallback() : ?Closure
{
  /**
   * @param \buffalokiwi\magicgraph\IModel $model Model being converted to an array
   * @param buffalokiwi\magicgraph\property\IProperty $prop Property the value belongs to
   * @param mixed $value Value to modify 
   * @return mixed modified value 
   */
  return function( 
    \buffalokiwi\magicgraph\IModel $model, 
    buffalokiwi\magicgraph\property\IProperty $prop, 
    mixed $value ) : mixed {
    //..Return the modified value 
    return $value;
  };
}
```


**Model Setter Callback**

The model setter callback is the same as the property setter callback, except it adds access to the model and it is invoked by DefaultModel instead of AbstractProperty.
```php
function getModelSetterCallback() : ?Closure
{
  /**
   * @param \buffalokiwi\magicgraph\IModel $model The model the property belongs to
   * @param buffalokiwi\magicgraph\property\IProperty $prop The property being set 
   * @param mixed $value The value being written
   * @return mixed The modified value to write to the backing property
   */
  return function( 
    \buffalokiwi\magicgraph\IModel $model, 
    \buffalokiwi\magicgraph\property\IProperty $prop, 
    mixed $value ) : mixed {
    //..Return modified value 
    return $value;
  };
}
```


**Model Getter Callback**

The model getter callback is the same as the property getter callback, except it adds access to the model and it is invoked by DefaultModel instead of AbstractProperty.
```php
function getModelGetterCallback() : ?Closure
{
  /**
   * @param \buffalokiwi\magicgraph\IModel $model The model the property belongs to
   * @param buffalokiwi\magicgraph\property\IProperty $prop The property being retrieved
   * @param mixed $value The value being retrieved
   * @return mixed The modified value to retrieve
   */
  return function( 
    \buffalokiwi\magicgraph\IModel $model, 
    \buffalokiwi\magicgraph\property\IProperty $prop, 
    mixed $value ) : mixed {
    //..Return modified value 
    return $value;
  };
}
```


### Named Property Behavior 

The [INamedPropertyBehavior](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-INamedPropertyBehavior.html) interface 
extends [IPropertyBehavior](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IPropertyBehavior.html), adds additional model-level callbacks.

The following callbacks are invoked by [ISaveableMappingObjectFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ISaveableObjectFactory.html) implementations.

**Model Validation Callback**

This is called when IModel::validate() is invoked, and is an opportunity to validate the state of a model.  Any validation 
errors must throw a [ValidationException](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-ValidationException.html) 
```php
function getModelValidationCallback() : ?Closure 
{
  /**
   * @param \buffalokiwi\magicgraph\IModel $model The model to validate 
   */
  return function( \buffalokiwi\magicgraph\IModel $model ) : void {
    if ( !$valid )
      throw new \buffalokiwi\magicgraph\ValidationException( 'Model is invalid' );
  };
}
```


**Before Save Callback**

This is what it sounds like.  When a model is saved by some [ISaveableMappingObjectFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ISaveableObjectFactory.html) implementation,
before save is called prior to the model being persisted.  
Note: In the default repository implementations, save is part of a transaction and any exceptions thrown will trigger a rollback.
```php
function getBeforeSaveCallback() : ?Closure
{
  /**
   * @param \buffalokiwi\magicgraph\IModel $model The model to save
   */
  return function( \buffalokiwi\magicgraph\IModel $model ) : void {
    //..Do something with the model before it's saved
  };
}
```


**After Save Callback**

This is the same thing as before save, but it happens after the model is saved.
```php
function getAfterSaveCallback() : ?Closure
{
  /**
   * @param \buffalokiwi\magicgraph\IModel $model The model to save
   */
  return function( \buffalokiwi\magicgraph\IModel $model ) : void {
    //..Do something with the model after it's saved
  };
}
```

There are a few ways of implementing INamedPropertyBehavior:

1. Extend [buffalokiwi\magicgraph\property\GenericNamedPropertyBehavior](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-GenericNamedPropertyBehavior.html)
2. Create an anonymous strategy with the [NamedPropertyBehaviorBuilder](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-NamedPropertyBehaviorBuilder.html)
3. Write your own implementation 


Extending GenericNamedPropertyBehavior is the preferred method of creating behavioral strategies.  By default, every callback 
will return null.  Override any of the methods in some subclass and return the callback closures.  In the following example, we will 
create a model called Test with a property name.  We'll create a strategy that will set name equal to "bar" if name is set to "foo", and if 
name is set to "baz" and exception is thrown.

```php
/**
 * Property definition for TestModel 
 */
class TestProperties extends buffalokiwi\magicgraph\property\BasePropertyConfig
{
  const NAME = 'name';
  
  public function getName() : string
  {
    return self::NAME;
  }
  
  protected function createConfig() : array
  {
    return [
      self::NAME => self::FSTRING
    ];
  }
}


/**
 * Test model
 */
class TestModel extends \buffalokiwi\magicgraph\GenericModel 
{
  /**
   * Property definitions 
   * @var TestProperties
   */
  private TestProperties $props;
  
  
  public function __construct( \buffalokiwi\magicgraph\property\IPropertyConfig ...$config )
  {
    parent::__construct( ...$config );
    $this->props = $this->getPropertyConfig( TestProperties::class );
  }
  
    
  public function getName() : string
  {
    return $this->getValue( $this->props->getName());
  }
  
  
  public function setName( string $name ) : void
  {
    $this->setValue( $this->props->getName(), $name );
  }
}


/**
 * If the name property equals "foo", it is set to "bar".
 * If theh name property equals "baz", a ValidationException is thrown 
 */
class TestModelBehavior extends buffalokiwi\magicgraph\property\GenericNamedPropertyBehavior
{
  public function getValidateCallback(): ?\Closure
  {
    return function( buffalokiwi\magicgraph\property\IProperty $prop, string $name ) : bool {
      //..If $name equals baz, then an exception is thrown
      return $name != 'baz';        
    };
  }
  
  
  public function getSetterCallback(): ?\Closure
  {
    return function( buffalokiwi\magicgraph\property\IProperty $prop, string $name ) : string {
      //..Returns bar if name equals foo.
      return ( $name == 'foo' ) ? 'bar' : $name;
    };
  }
}



//..Create an instance of test model with the test behavior. 
//..The behavior is wired to the name property.
$model = new TestModel( new TestProperties( new TestModelBehavior( TestProperties::NAME )));

//..Set the name 
$model->setName( 'The name' );

/**
 * Outputs:
 * array (size=1)
 *   'name' => string 'The name' (length=8)
 */
var_dump( $model->toArray());


//..Set the name to "foo"
$model->setName( 'foo' );

/**
 * Outputs:
 * array (size=1)
 *   'name' => string 'bar' (length=3)
 */
var_dump( $model->toArray());

//..Set to baz and an exception will be thrown 
//..Throws: "baz" of type "buffalokiwi\magicgraph\property\StringProperty" is not a valid value for the "name" property.  
//  Check any behavior callbacks, and ensure that the property is set to the correct type.  IPropertyBehavior::getValidateCallback() failed.
//..This will also generate an error "Behavior validation failure in closure: TestProperties in file XXX"
$model->setName( 'baz' );
```

When using any of the behavior callbacks, you can replace IModel and mixed types with any derived type.  It is also worth 
noting that if you wanted a behavior to work with all properties, you can pass static::class as the property name to the 
PropertyBehavior constructor from GenericNamedPropertyBehavior.  This will only work for model level callbacks, and when
the strategy class name matches the supplied property name, the strategy is applied to every property in the model.


---


## Database Connections

Magic Graph provides a simple abstraction over the [PHP PDO library](https://www.php.net/manual/en/book.pdo.php).  First, 
lets go over the four interfaces, then we'll go over the MySQL implementation.


**[IConnectionProperties](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-pdo-IConnectionProperties.html)** 

The connection properties interface is used to define the criteria used to connect to some database engine.  You'll find 
super fancy methods like getHost() and getDSN().  Truly mind-blowing stuff here.  It has everything you'd expect in a property bag 
for a database connection.  


**[IConnectionFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-pdo-IConnectionFactory.html)**

The connection factory is exactly what seems.  This interface may go through a revision in the near future.  The concept is to
have a factory that creates database connections.  In it's current form, it is probably best that one factory provides
connections for one persistence type.  In the future, this interface will be revised to more easily support multiple persistence
types in a single factory.  Note: This does support multiple types in a single factory, but it's not easy to work with.  For now, keep it one to one and it works nice.


**[IDBConnection](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-pdo-IDBConnection.html)**

This is an interface for the PDO object that ships with PHP, but with one additional method: 
```php
executeQuery( string $statement ) : Generator
```
executeQuery() is a simple way to execute a simple statement without parameters.  


**[IPDOConnection](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-pdo-IPDOConnection.html)**

IPDOConnection extends IDBConnection and it adds several methods to make it easier to work with common query types.  Let's take a look.


**Delete**

The delete method is used to delete rows.  This method will only delete by primary key, and composite primary keys are supported.

```php
/**
 * Execute a delete query for a record using a compound key.
 * @param string $table table name
 * @param array $pkPairs primary key to value pairs 
 * @param int $limit limit
 * @return int affected rows
 * @throws InvalidArgumentExcepton if table or col or id are empty or if col
 * contains invalid characters or if limit is not an integer or is less than
 * one
 * @throws DBException if there is a problem executing the query
 */
function delete( string $table, array $pkCols, int $limit = 1 ) : int;

//..Example:

$affectedRows = delete( 'mytable', ['pkcol1' => 'value1', 'pkcol2' => 'value2'], 1 );

//..Generates the statement:
// delete from mytable where pkcol1=? and pkcol2=? limit 1;  
```


**Update**  

Update updates matching rows in some table.  This is also matched by primary key, and composite keys are supported.

```php
/**
 * Build an update query using a prepared statement.
 * @param string $table Table name
 * @param array $pkPairs list of [primary key => value] for locating records to update.
 * @param array $pairs Column names and values map
 * @param int $limit Limit to this number
 * @return int the number of affected rows
 * @throws InvalidArgumentException
 * @throws DBException
 */
function update( string $table, array $pkPairs, array $pairs, int $limit = 1 ) : int;

//..Example

$affectedRows = update( 'mytable', ['id' => 1], ['name' => 'foo', 'md5name:md5' => 'foo'], 1 );

//..Generates the statement:
// update mytable set name=?, md5name=md5(?) where id=? limit 1;
```
Functions can be added to columns by appending ':func' to any column name.   Multiple functions can be chained like this: ':func1:func2'


**Insert**

Insert is similar to update except that it inserts new records!  Wooooooo! 
  
```php
/**
 * Build an insert query using a prepared statement.
 * This will work for most queries, but if you need to do something
 * super complicated, write your own sql...
 *
 *
 * @param string $table Table name
 * @param array $pairs Column names and values map
 * @return int last insert id for updates
 * @throws InvalidArgumentException
 * @throws DBException
 */
function insert( string $table, array $pairs ) : string;    

//..Example:
$lastInsertId = insert( 'mytable', ['col1' => 'value1', 'col2:md5' => 'value2'] );

//..generates statement:
// insert into mytable (col1, col2) values(?,md5(?)); 
```  


**Cursors**

Have you ever wanted to iterate over each row in some table?  You're in luck! 

```php  
/**
 * Creates a cursor over some result set 
 * @param string $statement Statement 
 * @param type $options Parameters
 * @param type $scroll Enable Scroll 
 * @return Generator Results 
 */
function forwardCursor( string $statement, $options = null, $scroll = false ) : Generator;  

//..Use it like this:

foreach( forwardCursor( 'select * from mytable where col=?', ['foo'] ) as $row )
{
  //..Do something with $row
  //..$row is an associative array containing column names and values.
}
```  

Note: The $scroll argument is deprecated and will be removed in a future release.  Scroll was supposed to allow bidirectional 
cursor movement, but not all drivers support scrollable cursors (mysql does not) and therefore $scroll should not be 
included in a generic interface.


**Select**

Surprise!  We can select things too!  Pass your statement and bindings to the select method, and BAM! you get results.
  
```php
/**
 * Select some stuff from some database
 * @param string $statement sql statement
 * @param type $opt Bindings for prepared statement.  This can be an object or an array 
 */ 
function select( string $statement, $opt = null ) : \Generator;  

//..Use like this:

foreach( select( 'select * from mytable where col=?', ['foo'] ) as $row )
{
  //..Do something with $row
  //..$row is an associative array containing column names and values.
}

//..Generates the statement:
//  select * from mytable where col=?
```


**Select Multiple Result Sets**

Queries that return multiple result sets are also fully supported.  This can be a stored procedure that returns multiple
result sets or simply adding semicolons between the statements.  Be careful with this one.  Semicolons can do nasty things.
```php
/**
 * Execute a sql statement that has multiple result sets
 * ie: a stored procedure that has multiple selects, or one of those snazzy
 * subquery statements
 * @param string $sql SQL statement to execute
 * @param array $bindings Column bindings 
 * @return Generator array results
 * @throws DBException if there is one
 */
public function multiSelect( string $sql, array $bindings = [] ) : Generator
//..Example:

foreach( multiSelect( 'select * from mytable where id=?; select * from mytable where id=?', [1,2] ) as $rowSet )
{
  //..Each $rowSet entry contains a set of rows to iterate over.
  foreach( $rowSet as $row )
  {
    //..$row is an associative array of column => value 
  }
}
```


**Execute**

Executes some arbitrary statement without a result set.  
```php
/**
 * Executes a query with no result set.
 * @param string $statement Statement to execute 
 * @param array $opt Map of bindings 
 * @return int
 */
function execute( string $statement, $opt = null ) : int;
```


### MySQL PDO 

Magic Graph currently ships with a single database adapter for MySQL, [MariaDBConnection](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-pdo-MariaDBConnection.html), which 
extends the abstract base class PDOConnection, implements IPDOConnection, and adds the necessary driver-specific sql statements.  This is the PDO implementation to 
use for all things MySQL/MariaDB.


### Connection Factories

Connection factories generate database connections for use with some driver.  I'm sure you've seen the examples throughout this readme, but in case you haven't, here it is:
```php
$dbFactory = new buffalokiwi\magicgraph\pdo\PDOConnectionFactory( //..A factory for managing and sharing connection instances 
  new buffalokiwi\magicgraph\pdo\MariaConnectionProperties(       //..Connection properties for MariaDB / MySQL
    'localhost',                  //..Database server host name 
    'root',                       //..User name
    '',                           //..Password
    'fancydatabase' ),            //..Database 
  //..This is the factory method, which is used to create database connection instances
  //..The above-defined connection arguments are passed to the closure.
  function( buffalokiwi\magicgraph\pdo\IConnectionProperties $args  ) { 
    //..Return a MariaDB connection 
    return new buffalokiwi\magicgraph\pdo\MariaDBConnection( $args );
  }
);
```

The idea is to create a factory using some connection properties, and have that generic factory return a PDO implementation of the correct type.  Nothing ground breaking here.


---


## Working with Currency

Currency is something that doesn't always work properly.  There are many ways to solve the currency problem (which we will not discuss here) 
Fortunately we have this awesome library [MoneyPHP](https://github.com/moneyphp/money),which is based on [Martin Fowler's money pattern](https://martinfowler.com/eaaCatalog/money.html), uses
strings internally to represent currency, and the best part is that money objects are immutable.  

One downside to MoneyPHP is that it does not have any type of interface.  It is simply the Money object.  In Magic Graph, 
there is an interface for Money [IMoney](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-money-IMoney.html), which is implemented by [MoneyProxy](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-money-MoneyProxy.html), which accepts an instance of [Money](https://github.com/moneyphp/money/blob/master/src/Money.php) and 
proxies all calls to the underlying Money object.  This is because at some point we may want to swap out MoneyPHP for some other library, and we can't do that without a proper abstraction.

Since MoneyPHP handles different currencies and formats, we need a money factory to have an easy way of generating money instances of the same currency.  In Magic Graph, we have a factory
[MoneyFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-money-MoneyFactory.html), which implements [IMoneyFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-money-IMoneyFactory.html).

Here's an example of how to set up a money factory for US Dollars

```php
$currencies = new \Money\Currencies\ISOCurrencies();

//..Money formatter 
$intlFmt = new Money\Formatter\IntlMoneyFormatter( 
  new \NumberFormatter( 'en_US', \NumberFormatter::CURRENCY ), 
  $currencies 
);

$decFmt = new Money\Formatter\DecimalMoneyFormatter( $currencies );

//..Money factory 
//..This is used to lock the system down to a certain type of currency, 
// and to provide an abstract wrapper for the underlying money implementation.
$dollarFactory = new \buffalokiwi\magicgraph\money\MoneyFactory( 
  function( string $amount ) use($intlFmt,$decFmt) : buffalokiwi\magicgraph\money\IMoney {
    return new buffalokiwi\magicgraph\money\MoneyProxy( 
      Money::USD( $amount ), 
      $intlFmt, 
      $decFmt );
  }
);   
```

Now we can create money and format it for the configured currency:

```php
$treeFiddy = $dollarFactory->getMoney( '3.50' );

/**
 * Outputs:
 * object(buffalokiwi\magicgraph\money\MoneyProxy)[600]
 *  private 'money' => 
 *    object(Money\Money)[601]
 *      private 'amount' => string '350' (length=3)
 *      private 'currency' => 
 *        object(Money\Currency)[602]
 *          private 'code' => string 'USD' (length=3)
 *  private 'formatter' => 
 *    object(Money\Formatter\IntlMoneyFormatter)[595]
 *      private 'formatter' => 
 *        object(NumberFormatter)[596]
 *      private 'currencies' => 
 *        object(Money\Currencies\ISOCurrencies)[594]
 *  private 'decFmt' => 
 *    object(Money\Formatter\DecimalMoneyFormatter)[597]
 *      private 'currencies' => 
 *        object(Money\Currencies\ISOCurrencies)[594]
 */
var_dump( $treeFiddy );

//..Outputs: 3.50
echo (string)$treeFiddy;

//..Outputs: $3.50
echo $treeFiddy->getFormattedAmount();
```

Using currency properties in Magic Graph is easy.  Simply use the 'money' property type in your property configuration arrays.

Note: Due to the use of MoneyFactory, a service locator will need to be passed to the config mapper.  This will allow the config mapper
to find the money factory (and other things) when creating property objects.  See [Magic Graph Setup](#the-config-mapper) for more information.  Here's a 
quick example for reference:

```php
//..Service locator 
$ioc = new buffalokiwi\buffalotools\ioc\IOC();

//..Default Magic Graph Configuration Mapper. 
//..This creates the property objects.
$configMapper = new buffalokiwi\magicgraph\property\DefaultConfigMapper( $ioc );

//..Factory wraps the config mapper and can combine config arrays.  
//  Uses the config mapper to produce properties.
$propertyFactory = new \buffalokiwi\magicgraph\property\PropertyFactory( $configMapper );

//..Use $propertyFactory to create instances of IPropertySet
```


---


## Creating HTML Elements

This package should not have been part of Magic Graph, and instead should have been released as a separate extension.  However, the package is here and it
is fully integrated with properties, and therefore it's not going anywhere.

By using [IElementFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-htmlproperty-IElementFactory.html) implementations, it is possible
to convert IProperty instances to [IElement](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-htmlproperty-IElement.html) and eventually to a string containing the HTML. 

The default implementation of IElementFactory is [ElementFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-htmlproperty-ElementFactory.html), which accepts
a list of [IElementFactoryComponent](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-htmlproperty-IElementFactoryComponent.html).  IELementFactoryComponent instances are used
to map a subclass of IProperty to a function resposible for converting the IProperty instance to an instance of IElement.  

There is a default mapping class called [DefaultComponentMap](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-htmlproperty-DefaultComponentMap.html), which can be used 
to quickly get started with ElementFactory.

Here's an example:

```php
//..Create a simple model with a few properties
$model = new buffalokiwi\magicgraph\QuickModel([
  'numberinput' => ['type' => 'int'],
  'stringinput' => ['type' => 'string'],
  'dateinput' => ['type' => 'date'],
  'boolinput' => ['type' => 'bool'],
  'enuminput' => ['type' => 'rtenum', 'config' => ['test1','test2','test3'], 'value' => 'test1']    
]);


$elementFactory = new buffalokiwi\magicgraph\property\htmlproperty\ElementFactory( ...( new buffalokiwi\magicgraph\property\htmlproperty\DefaultComponentMap())->getMap());

foreach( $model->getPropertySet()->getProperties() as $prop )
{
  echo $elementFactory->createElement( $model, $prop, $prop->getName(), null, (string)$model->getValue( $prop->getName()))->build();
  echo '<br />';
}
```

The above-example will generate five html inputs:

```html
<input step="1" type="number" value="0" name="numberinput" id="numberinput" />
<br />
<input type="text" name="stringinput" id="stringinput" />
<br />
<input type="date" name="dateinput" id="dateinput" />
<br />
<label><input type="checkbox" class=" checkbox" name="boolinput" id="boolinput" /><span></span></label>
<br />
<select name="enuminput" id="enuminput" >
  <option value="test1" selected="selected" >Test1</option>
  <option value="test2" >Test2</option>
  <option value="test3" >Test3</option>
</select>
<br />
```


If you want to add element factory components for any custom properties, or if you want to override the default 
components, you can pass this to the constructor of DefaultComponentMap.  Any matching properties are internally overridden.

For example, while this is the default handler for IStringProperty, it could be overridden if passed to the constructor.
```php
new buffalokiwi\magicgraph\property\htmlproperty\DefaultComponentMap([
  buffalokiwi\magicgraph\property\IStringProperty::class => function( 
    buffalokiwi\magicgraph\property\IStringProperty $prop, 
    string $name, 
    ?string $id, 
    string $value 
  ) : buffalokiwi\magicgraph\property\htmlproperty\IElement {
    $attrs = [];

    if ( $prop->getMin() != -1 )
      $attrs['minlength'] = $prop->getMin();

    if ( $prop->getMax() != -1 )
      $attrs['maxlength'] = $prop->getMax();

    if ( !empty( $prop->getPattern()))
      $attrs['pattern'] = $prop->getPattern();

    if ( $prop->getFlags()->hasVal( \buffalokiwi\magicgraph\property\IPropertyFlags::REQUIRED ))
      $attrs['required'] = 'required';

    if ( $prop->getMax() != -1 && $prop->getMax() > 255 )
      return new buffalokiwi\magicgraph\property\htmlproperty\TextAreaElement( $name, $id, $value, $attrs );
    else
      return new \buffalokiwi\magicgraph\property\htmlproperty\InputElement( 'text', $name, $id ?? '', $value, $attrs );
  }
]);
```

The definition for the callbacks is as follows:

```php
/**
 * Converts IProperty to IElement 
 * @param \buffalokiwi\magicgraph\property\IProperty $prop Property to convert
 * @param string $name property/html form input name 
 * @param string|null $id html element id attribute value 
 * @param string $value Property value as a string 
 * @return buffalokiwi\magicgraph\property\htmlproperty\IElement HTML Element 
 */
function( \buffalokiwi\magicgraph\property\IProperty $prop, string $name, 
  ?string $id, string $value ) : buffalokiwi\magicgraph\property\htmlproperty\IElement;
```


---



## Magic Graph Setup

Magic Graph was designed to support the [composition root pattern](https://medium.com/@cfryerdev/dependency-injection-composition-root-418a1bb19130). 
The idea is to have every call to "new object" in a single file called composition root.  While Magic Graph object instantiation may look complicated, 
you only have to write that code once, and all of it ends up in one place.  Instances of various Magic Graph components are then injected into other classes as a dependency. 

Here's what the composition root section for magic graph may look like.

1. Create the service locator container.  This is used to provide various factories (like DateFactory and MoneyFactory) to the config mapper.
2. Create a database connection factory.  This will be used by repositories.
3. Add DateFactory to the container.  This is used within IDateProperty.
4. Add MoneyFactory to the container.  This is used within IMoneyProperty.
5. Create the config mapper.  This is an instance of IConfigMapper, and is responsible for creating instances of IProperty based on types listed in the property configuration arrays.
6. Create the PropertyFactory instance.  This creates IPropertySet instances with the appropriate IConfigMapper.  Property sets contain the properties used by IModel instances.
7. Add ITransactionFactory to the container.  This will be used by various relationship providers and things that need to unify saves across multiple repositories.
8. Optionally create local variables for IDBConnection and IDBFactory.  This can make writing composition root a little easier and reduce calls to the container.


```php

/*********************/
/* IoC Container     */
/*********************/

$ioc = new \buffalokiwi\buffalotools\ioc\IOC();


/**********************/
/* Database           */
/**********************/

$ioc->addInterface(buffalokiwi\magicgraph\pdo\IConnectionFactory::class, function() {
  return new \buffalokiwi\magicgraph\pdo\PDOConnectionFactory( 
    new buffalokiwi\magicgraph\pdo\MariaConnectionProperties( 
      'localhost',    //..Host
      'root',         //..User
      '',             //..Pass
      'magicgraph' ), //..Database 
   function(\buffalokiwi\magicgraph\pdo\IConnectionProperties $args  ) {
     return new buffalokiwi\magicgraph\pdo\MariaDBConnection( $args, function(buffalokiwi\magicgraph\pdo\IDBConnection $c ) { $this->closeConnection($c); });
   });                
});


/**********************/
/* Dates              */
/**********************/

$ioc->addInterface( \buffalokiwi\buffalotools\date\IDateFactory::class, function() { 
  return new \buffalokiwi\buffalotools\date\DateFactory();   
});



/*********************/
/* Money Factory     */
/*********************/

$ioc->addInterface( \buffalokiwi\magicgraph\money\IMoneyFactory::class, function() {
  
  $currencies = new Money\Currencies\ISOCurrencies();
  //..Money formatter 
  $intlFmt = new \Money\Formatter\IntlMoneyFormatter( 
    new \NumberFormatter( 'en_US', \NumberFormatter::CURRENCY ), 
    $currencies );

  $decFmt = new \Money\Formatter\DecimalMoneyFormatter( $currencies );

  //..Money factory 
  //..This is used to lock the system down to a certain type of currency, 
  // and to provide an abstract wrapper for the underlying money implementation.
  return new \buffalokiwi\magicgraph\money\MoneyFactory( function( string $amount ) use($intlFmt,$decFmt) : \buffalokiwi\magicgraph\money\IMoney {
    return new \buffalokiwi\magicgraph\money\MoneyProxy( \Money\Money::USD( $amount ), $intlFmt, $decFmt );
  });
});


/*********************/
/* Magic Graph Setup */
/*********************/

//..Converts IPropertyConfig config arrays into properties
//..If creating custom propeties, this must be replaced with a custom implementation.
$configMapper = new buffalokiwi\magicgraph\property\DefaultConfigMapper( $ioc );

//..Factory wraps the config mapper and can combine config arrays.  
//  Uses the config mapper to produce properties.
$propertyFactory = new \buffalokiwi\magicgraph\property\PropertyFactory( $configMapper );

//..The property set factory is required when service providers augment the model configuration.  This is used for 
//  things like the EAV system.
//..The closure is provided with a list of IPropertyConfig instances which are
//  supplied by the various service providers and base config.
$ioc->addInterface( \buffalokiwi\magicgraph\property\IPropertySetFactory::class, function() use ($propertyFactory) {
  return new \buffalokiwi\magicgraph\property\PropertySetFactory(
    $propertyFactory, 
    function(\buffalokiwi\magicgraph\property\IPropertyFactory $factory, \buffalokiwi\magicgraph\property\IPropertyConfig ...$config ) {
      return new DefaultPropertySet( $factory, ...$config );
  });
});

//..Transaction factory is used to handle saving multiple things at one time
$ioc->addInterface( \buffalokiwi\magicgraph\persist\ITransactionFactory::class, function() {
  return new \buffalokiwi\magicgraph\persist\DefaultTransactionFactory();
});

//..I like to set up a few shared variables to use in composition root for the database factory and default connection.
//..Database connection factory 
$db = $ioc->getInstance( \buffalokiwi\magicgraph\pdo\IConnectionFactory::class );    
/* @var $db \buffalokiwi\magicgraph\pdo\IConnectionFactory */

//..Default shared db connection 
$dbc = $db->getConnection();
/* @var $dbc \buffalokiwi\magicgraph\pdo\IDBConnection */


```

Once Magic Graph has been initialized, you can start adding repositories to the container. 

The following example is based on this table:
```sql
create table testtable ( id int auto_increment primary key, name varchar(50)) engine=innodb;
```

```php

//..Test repository interface
//..We always need a unique name for the service locator
interface ITestRepo extends \buffalokiwi\magicgraph\persist\IRepository {}

//..Test repository implementation 
class TestRepo extends \buffalokiwi\magicgraph\persist\SQLRepository implements ITestRepo {};

//..Test model 
class TestModel extends buffalokiwi\magicgraph\DefaultModel {}


//..Add ITestRepo to the container 
$ioc->addInterface( ITestRepo::class, function() use ($dbc,$propertyFactory) {
  return new TestRepo(
    'testtable',
    new \buffalokiwi\magicgraph\DefaultModelMapper( function( buffalokiwi\magicgraph\property\IPropertySet $props ) {
      return new TestModel( $props );
    }, TestModel::class ),
    $dbc,
    new buffalokiwi\magicgraph\property\DefaultPropertySet( 
      $propertyFactory, 
      new buffalokiwi\magicgraph\property\QuickPropertyConfig([
        'id' => ['type' => 'int', 'flags' => ['primary']], 
        'name' => ['type' => 'string']]))
  );
});


//..And now if we wanted to use this
$testRepo = $ioc->getInstance( ITestRepo::class );
/* @var $testRepo \buffalokiwi\magicgraph\persist\IRepository */

//..Create a new model
$testModel = $testRepo->create();

//..Set the name property
$testModel->name = 'test';

//..Save the model 
$testRepo->save( $testModel );

//..Get the id of the new model
//..Outputs "1" 
echo $testModel->id;
```


---


## Entity Attribute Value