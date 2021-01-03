# BuffaloKiwi Magic Graph
  
**Behavioral-based object modeling and persistence library for PHP 7.4**  
  
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
7. [Repositories](#repositories)
    1. [Mapping Object Factory](#mapping-object-factory)
    2. [Saveable Mapping Object Factory](#saveable-mapping-object-factory)
    3. [SQL Repository](#sql-repository)
    4. [Decorating Repositories](#decorating-repositories)
    5. [Serviceable Repository](#serviceable-repository)
    6. [Composite Primary Keys](#composite-primary-keys)
8. Transactions
    1. Overview
    2. Creating a Transaction
    3. Transaction Factory
    4. Save Functions
    5. Chained Transaction Manager
    6. Unit of Work
    7. Full Example
9. Relationships
    1. One to One 
    2. One to Many
    3. Many to Many 
    4. Nested Relationship Providers 
    5. Model property providers 
    6. How Saving Works
    7. Editing Nested Models
10. Extensible Models
    1. Overview
    2. Property configuration interface
    3. Property configuration implementation
    4. Model interface
    5. Model implementation 
11. Behavioral Strategies 
12. Property service providers
13. Model service providers 
14. Database Connections 
    1. PDO 
    2. Extending PDO 
    3. Connection Factories 
15. Working with Currency 
    1. MoneyPHP/Money
    2. Currency properties 
16. Creating HTML elements
17. Magic Graph Setup
    1. The Config Mapper 
    2. Property Factory
    3. Property Set Factory
18. Entity-Attribute-Value (EAV)
19. Searching
20. Extending Magic Graph 
21. Tutorial

---
  

## Overview

The magic part of Magic Graph isn't building SQL queries using functions (SQL works great).  
The magic is how behavior is defined, how models are created and linked together, and how all of this can be done 
at design time or run time.  Magic Graph makes it easy to design and use rich hierarchical domain models, 
which can incorporate various independently designed and tested behavioral strategies.  
  
Magic Graph is a convention based library, coded in pure PHP - with zero outside configuration.  XML, YAML or JSON will 
not be found anywhere near Magic Graph.  
  
  

**Why was this written?**

Magento.  After using that monstrosity, I decided to write a better eCommerce engine.  
That meant I needed an ORM library that allowed me to create an EAV-ish style system, but without the insanity of EAV.  
I realized that the persistence layer isn't really all that important at all, and should have zero impact on how an 
application is designed.  Instead of storing information about the data in the database, everything is done by convention,
in code, where it belongs.  This is accomplished by backing model properties with independent objects that handle a 
certain data type.  This allowed me to create models on the fly, with various self-validating data types, which can 
also have various behaviors coupled to them.

Magic Graph is stable, and is currently the foundation of the Retail Rack eCommerce engine.
  
  
**Persistence**

Persistence is optional, and it's possible to create object factories without using the persistence package.

Magic Graph persistence uses the repository and unit of work patterns.  Any type of persistence layer can be used, and it 
is not limited to SQL.  Transactions can occur across different database connections and storage types (with obvious limitations).  
Currently Magic Graph includes MySQL/MariaDB adapters out of the box, and additional adapters added in future releases.

All examples in this documentation will assume that you want to use the persistence package.  

---

## Installation

```
composer require buffalokiwi/magicgraph
```
  

---
  
  
## Dependencies

Magic Graph requires one third party and two BuffaloKiwi libraries.

1. [BuffaloKiwi/buffalotools_ioc](https://github.com/SixArmDonkey/buffalotools_ioc) - A service locator 
2. [BuffaloKiwi/buffalotools_types](https://github.com/SixArmDonkey/buffalotools_types) - Enum and Set support
3. [MoneyPHP/Money](https://github.com/moneyphp/money) - PHP implementation of Fowler's Money pattern
  

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

Let's take a look at some code.

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
options attached to the various default properties included with MagicGraph, which we will cover in the [Validation](#) chapter.
  
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
Column must list a class name implementing the IEnum interface in the 'clazz' attribute.
```
IPropertyType::TENUM = 'enum'
```  
  
#### Runtime Enum  
Backed by [IEnumProperty](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-property-IEnumProperty.html)
Enum members are configured via the "config" property and is backed by a RuntimeEnum instance.  Runtime Enum instances
do not use the "clazz" attribute.
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
will read/write instances of ISet (or descendants of ISet as specified by the "clazz" attribute).  
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
  
  
### Repositories 
  
Magic Graph repositories are an implementation of the [Repository Pattern](https://martinfowler.com/eaaCatalog/repository.html).  
Repositories are an abstraction that encapsulates the logic for accessing some persistence layer.  Similar to a collection, 
repositories provide methods for creating, saving, removing and retrieving IModel instances.  Repositories are object factories, and are designed to 
produce a single object type.  However, in a SQL setting, repositories may work with multiple tables from a database to produce a single model instance.
When creating aggregate repositories, it's your choice if you wish to create a single repository per table, or a single 
repository that access several tables.  It's worth noting that a repository may reference other repositories that access 
different persistence layers.
  
Repositories implement the [IRepository](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-IRepository.html) interface.
  
  
#### Mapping Object Factory
  
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
  
  
  
#### Saveable Mapping Object Factory
  
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
  
  
  
#### SQL Repository
  
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
  
  
  
#### Decorating Repositories
  
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
  
  

#### Serviceable Repository 
  
The serviceable repositories are used for repository relationships, which are discussed in the [Relationships](#relationships) section.  
  
Essentially, serviceable repositories accept [ITransactionFactory](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ITransactionFactory.html) 
and zero or more IModelPropertyProvider instances, which are used to extend the functionality of certain properties.  
  
For example, say you have a model A with an IModel property B.  By default the repository for model A does not know 
repository B or model B exists.  A IModelPropertyProvider instance could add a lazy loading scheme for retrieving 
model B when the property is accessed from model A.  Additionally, the model property provider could provide a way to 
save edits to model B when model A is saved.  
  
  
#### Composite Primary Keys
  
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
  

### Transactions

#### Overview 
Transactions are used to execute save operations against some persistence layer.  Similar to a database transaction, 
MagicGraph transactions will:

1. Start a transaction in the persistence layer when available
2. Execute arbitrary code against the persistence layer
3. Commit the changes
4. Roll back the changes on failure

Transactions are based on a single interface [ITransaction](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-ITransaction.html), 
and can have multiple implementations used to support various persistence layers.  MagicGraph fully supports using 
multiple, and different, persistence layers concurrently.  Transactions can be considered an adapter, which 
executes persistence-specific commands used to implement the required commit and rollback functionality.

Currently, MagicGraph ships with a single transaction type: [MySQLTransaction](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-MySQLTransaction.html)


#### Creating a Transaction

At the heart of any transaction is the code to be executed.  In MagicGraph, the interface [IRunnable](https://sixarmdonkey.github.io/magicgraph/classes/buffalokiwi-magicgraph-persist-IRunnable.html)
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

The default Transaction object shipped with MagicGraph does not connect to any specific persistence layer, and the implementations
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
the MagicGraph Transaction abstraction provides a way for us to run transactions against multiple database connections, 
or even different persistence engines.  


#### Transaction Factory

The transaction factory generates instances of some subclass of ITransaction.  The idea is to pass ITransactionFactory::createTransactions() a list of 
IRunnable instances.  The supplied IRunnable instances should be a subclass of IRunnable, and 





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
