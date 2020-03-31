# BuffaloKiwi Magic Graph
  
**Magic Graph isn't your typical ORM library.**

MIT License

---

## Table of Contents

1. [Overview](#overview)
2. [Installation](#installation)
  
---
  

## Overview

The magic part of Magic Graph isn't building SQL queries using functions.  The magic is 
how behavior is defined, how models are created and linked together, and how all of this can be done 
at design time or run time.  Magic Graph makes it easy to design and use rich hierarchical domain models, 
which can incorporate various independently designed and tested behavioral strategies.  
  

**Why was this written?**

Magento made me do it.  After using that monstrosity, I decided to write my own eCommerce engine (almost finished).  
That meant I needed an ORM type library that allowed me to create an EAV-ish style system, but without the insanity of EAV.  
Instead of storing information about the data in the database, I stored that in code, where it belongs.  This is accomplished 
by backing model properties with independent objects that handle a certain data type.  This allowed me to create models
on the fly, with various self-validating data types, which can also have various behaviors coupled to them.

Magic Graph is quite stable, and is currently the foundation of my eCommerce engine.
  
  
**Persistence**

Persistence is optional, and it's possible to create object factories without using the persistence package.

Magic Graph persistence uses the repository and unit of work patterns.  Any type of persistence layer can be used, and it 
is not limited to SQL.  Transactions can occur across different database connections and storage types (with obvious limitations).  
Currently Magic Graph includes MySQL/MariaDB adapters out of the box, and additional adapters added in future releases.


---

## Installation

```
composer require buffalokiwi/magicgraph
```

  
---
  