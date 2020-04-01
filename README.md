# BuffaloKiwi Magic Graph
  
**Magic Graph isn't your typical ORM library.**

MIT License

---

## Table of Contents

[Generated Documentation](https://sixarmdonkey.github.io/magicgraph/)

1. [Overview](#overview)
2. [Installation](#installation)
3. [Dependencies](#dependencies)
4. [Definitions](#definitions)
5. [Getting Started](#gettingstarted)
---
  

## Overview

The magic part of Magic Graph isn't building SQL queries using functions (SQL works great).  
The magic is how behavior is defined, how models are created and linked together, and how all of this can be done 
at design time or run time.  Magic Graph makes it easy to design and use rich hierarchical domain models, 
which can incorporate various independently designed and tested behavioral strategies.  
  
Magic Graph is a convention based library, coded in pure PHP, with zero outside configuration.  XML, YAML or JSON will 
not be found anywhere near Magic Graph.  
  
  

**Why was this written?**

Magento.  After using that monstrosity, I decided to write a better eCommerce engine (almost finished).  
That meant I needed an ORM library that allowed me to create an EAV-ish style system, but without the insanity of EAV.  
I realized that the persistence layer isn't really all that important at all, and should have zero impact on how an 
application is designed.  Instead of storing information about the data in the database, everything is done by convention,
in code, where it belongs.  This is accomplished by backing model properties with independent objects that handle a 
certain data type.  This allowed me to create models on the fly, with various self-validating data types, which can 
also have various behaviors coupled to them.

Magic Graph is quite stable, and is currently the foundation of the Retail Rack eCommerce engine.
  
  
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

