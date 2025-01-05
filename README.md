![PHPUnit](https://github.com/aoliverwd/basic-sqlite/actions/workflows/ci.yml/badge.svg) [![Latest Stable Version](https://poser.pugx.org/alexoliverwd/basic-sqlite/v)](//packagist.org/packages/alexoliverwd/basic-sqlite) [![License](https://poser.pugx.org/alexoliverwd/basic-sqlite/license)](https://packagist.org/packages/alexoliverwd/basic-sqlite)

# Basic SQLite

Basic SQLite is a lightweight PHP helper class designed to simplify interaction with SQLite databases. It streamlines the process of connecting to, querying, and managing SQLite databases.

## Installation

Preferred installation is via Composer:

```bash
composer require alexoliverwd/basic-sqlite
```

## Basic Usage

The constructor initializes a new instance of the ```AOWD\SQLite``` class, setting up the connection to the specified SQLite database file. It performs validation to ensure the provided path points to a valid directory and constructs the full path to the SQLite file.

If the parsed directory exists but the specified SQLite file does not, the file will be created automatically.

```php
use AOWD\SQLite;

$db_location = __DIR__ . '/example.sqlite';
$db = new SQLite($db_location);
```

When establishing a [new class instance](#__construct), the below methods are available:

- [beginWriteTransaction](#beginwritetransaction)
- [close](#close)
- [completeWriteTransaction](#completewritetransaction)
- [getColumns](#getcolumns)
- [getCurrentTableName](#getcurrenttablename)
- [getDatabaseLocation](#getdatabaselocation)
- [getIndices](#getindices)
- [getKeyFromName](#getkeyfromname)
- [getNames](#getnames)
- [hasColumn](#hascolumn)
- [migrate](#migrate)
- [query](#query)
- [queryIsWriteStatement](#queryiswritestatement)
- [setTableName](#settablename)

## TLDR

In summary, the example below demonstrates how to use the SQLite helper class to:

1. Set up a database connection.
2. Define a table schema.
3. Insert records using prepared statements.
4. Query the database.
5. Close the connection.

```php
// Import the SQLite Helper Class
use AOWD\SQLite;
use AOWD\DataType;

// Create a New SQLite Database Instance
$db = new SQLite(__DIR__ . '/users.sqlite3');

// Set the Target Table
$table = 'users';
$db->setTableName($table);

// Register Columns for the Table
$db->registerColumn('first_name', DataType::TEXT);
$db->registerColumn('last_name', DataType::TEXT);
$db->registerColumn('uuid', DataType::TEXT, false, true, true);

// Create the Table Schema
$db->migrate();

// Insert a Record Using a Prepared Statement
$query = <<<QUERY
INSERT INTO `$table` (`first_name`, `last_name`, `uuid`) VALUES (?, ?, ?)
QUERY;

$db->query($query, false, [
    [
        1,
        'some firstname',
        SQLITE3_TEXT
    ],
    [
        2,
        'some lastname',
        SQLITE3_TEXT
    ],
    [
        3,
        uniqid('uuid' . time(), true),
        SQLITE3_TEXT
    ]
]);

// Count Records in the Table
$count = $db->query("SELECT count(*) AS 'record_count' FROM `$table`");
echo "$count[0][record_count] record(s)";

// Close the Database Connection
$db->close();
```

# Public Methods

The below methods are detailed descriptions, parameters, return types, and usage examples for each method.

## \__construct

### Description

Initializes the SQLite class with a specified database location and optional pragmas.

### Parameters
- `$db_location`: The file path to the SQLite database.
- `$pragmas`: An optional associative array of pragmas to configure the SQLite database.

### Pragma Defaults

By default, the following pragmas are set when initializing a new instance:

- journal_mode: WAL
- busy_timeout: 5000
- synchronous: NORMAL
- cache_size: 2000
- temp_store: memory
- foreign_keys: true


### Returns
An instance of the SQLite class.

### Example
```php
$db = new SQLite('/path/to/database.sqlite', [
    'cache_size' => 10000
]);
```

## beginWriteTransaction

### Description

Starts a write transaction for the SQLite database.


### Returns
Void.

### Example
```php
$db->beginWriteTransaction();
```

## close

### Description

Closes the connection to the SQLite database.

### Returns
Void.

### Example
```php
$db->close();
```

## completeWriteTransaction

### Description

Completes the current write transaction, committing any changes to the database.

### Returns
Void.

### Example
```php
$db->completeWriteTransaction();
```


## getColumns

### Description

Retrieves a list of column names for the current table.

### Returns
Array of column names.

### Example
```php
$columns = $db->getColumns();
print_r($columns);
```


## getCurrentTableName

### Description

Retrieves the name of the current table being operated on.


### Returns
The name of the current table as a string.

### Example
```php
$table_name = $db->getCurrentTableName();
echo $table_name;
```


## getDatabaseLocation

### Description

Retrieves the file path of the SQLite database.

### Returns
The database file path as a string.

### Example
```php
$db_location = $db->getDatabaseLocation();
echo $db_location;
```


## getIndices

### Description

Retrieves a list of indices for the current table.

### Returns
Array of index names.

### Example
```php
$indices = $db->getIndices();
print_r($indices);
```


## getKeyFromName

### Description

Finds the key corresponding to a given name in an array of items.

### Parameters
- `$items`: An array of items.
- `$name`: The name to find the key for.

### Returns
The key corresponding to the given name.

### Example
```php
$key = $db->getKeyFromName([['name' => 'users'], ['name' => 'orders']], 'orders');
```


## getNames

### Description

Extracts names from an array of items.

### Parameters
- `$items`: An array of items to extract names from.


### Returns
Array of names.

### Example
```php
$names = $db->getNames([['name' => 'users'], ['name' => 'orders']]);
print_r($names);
```


## hasColumn

### Description

Checks whether a specified column exists in the current table.

### Parameters
- `$column_name`: The name of the column to check.


### Returns
Boolean indicating whether the column exists.

### Example
```php
$has_column = $db->hasColumn('email');
```

## migrate

### Description

Performs migrations to ensure the database schema is up-to-date.

### Returns
Void.

### Example
```php
$db->migrate();
```


## query

### Description

Executes an SQL query on the database, with optional parameter binding and row return.

### Parameters
- `$query`: The SQL query string to execute.
- `$return_rows`: Whether to return rows from the query. Defaults to true.
- `$bind_params`: An associative array of parameters to bind to the query.


### Returns
Array of rows if `$return_rows` is true; otherwise, Void.

### Example
```php
$rows = $db->query('SELECT * FROM users WHERE id = :id', true, ['id' => 1]);
```


## queryIsWriteStatement

### Description

Checks if a given query is a write operation (e.g., INSERT, UPDATE, DELETE).

### Parameters
- `$query`: The SQL query string to analyze.


### Returns
Boolean indicating whether the query is a write operation.

### Example
```php
$is_write = $db->queryIsWriteStatement('INSERT INTO users (name) VALUES ("John Doe")');
```


## setTableName

### Description

Sets the name of the table to be used for subsequent operations.

### Parameters
- `$table_name`: The name of the table.


### Returns
Void.

### Example
```php
$db->setTableName('users');
```
