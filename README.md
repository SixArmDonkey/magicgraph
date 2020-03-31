# BuffaloKiwi Magic Graph
  
**Magic Graph isn't your typical ORM library.**

MIT License

---

## Table of Contents

1. [Overview](#overview)
2. [Installation](#installation)
3. [Dependencies](#dependencies)
4. [Getting Started](#gettingstarted)
---
  

## Overview

The magic part of Magic Graph isn't building SQL queries using functions.  The magic is 
how behavior is defined, how models are created and linked together, and how all of this can be done 
at design time or run time.  Magic Graph makes it easy to design and use rich hierarchical domain models, 
which can incorporate various independently designed and tested behavioral strategies.  
  

**Why was this written?**

Magento.  After using that monstrosity, I decided to write my own eCommerce engine (almost finished).  
That meant I needed an ORM library that allowed me to create an EAV-ish style system, but without the insanity of EAV.  
I realized that the persistence layer isn't really all that important at all, and should have zero impact on how an 
application is designed.  Instead of storing information about the data in the database, everything is done by convention,
in code, where it belongs.  This is accomplished by backing model properties with independent objects that handle a 
certain data type.  This allowed me to create models on the fly, with various self-validating data types, which can 
also have various behaviors coupled to them.

Magic Graph is quite stable, and is currently the foundation of my eCommerce engine.
  
  
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
  
  
## Getting Started

### What is a Model?

In Magic Graph, a Model is an object that implements the IModel interface.  






    $repo = new buffalokiwi\magicgraph\persist\InlineSQLRepo( 'test', $db->getConnection(),
      new \buffalokiwi\magicgraph\property\DefaultIntegerProperty( 'id', 0, null, buffalokiwi\magicgraph\property\IPropertyFlags::PRIMARY ),
      new \buffalokiwi\magicgraph\property\DefaultStringProperty( 'name', '', new buffalokiwi\magicgraph\property\PropertyBehavior( 
        /**
         * Validate the name property 
         * @param IProperty $prop The name property object 
         * @param string $value The name property value 
         * @return bool Success (required by AbstractProperty)
         * @throws buffalokiwi\magicgraph\ValidationException 
         */
        function( buffalokiwi\magicgraph\property\IProperty $prop, string $value ) : bool {
          //..Can do this 
          if ( strlen( $value ) != 3 )
            throw new \buffalokiwi\magicgraph\ValidationException( 'Name property must be 3 characters in length' );
          
          //..Return true/false for success
          return true;
        }
      ), buffalokiwi\magicgraph\property\IPropertyFlags::REQUIRED )
    );
      
    //..Can create and save a new record like this.
    //$repo->save( $repo->create(['name' => 'foo']));
      
    //..Get the first model 
    $model = $repo->get('1');
    //..Validation exceptions are thrown on set AND save.
    $model->name = 'longername';
    //$repo->save( $model );    
 