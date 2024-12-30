# Basic SQLite

Basic SQLite is a lightweight PHP helper class designed to simplify interaction with SQLite databases. It streamlines the process of connecting to, querying, and managing SQLite databases, making database operations more intuitive and efficient for developers.

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

When establishing a new class instance, the below methods are available:

* close
* getDatabaseLocation
* beginWriteTransaction
* completeWriteTransaction
* queryIsWriteStatement
* getCurrentTableName
* setTableName
* registerColumn
* hasColumn
* migrate
* query
* getColumns
* getIndices
* getNames
* getKeyFromName

## TLDR

Below demonstrates basic implementation of the SQLite helper class.

```php
// Import the SQLite Helper Class
use AOWD\SQLite;

// Create a New SQLite Database Instance
$db = new SQLite(__DIR__ . '/users.sqlite');

// Set the Target Table
$table = 'users';
$db->setTableName($table);

// Register Columns for the Table
$db->registerColumn('first_name', 'text');
$db->registerColumn('last_name', 'text');
$db->registerColumn('uuid', 'text', false, true, true);

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
        1,
        'some lastname',
        SQLITE3_TEXT
    ],
    [
        1,
        uniqid('uuid' . time(), true),
        SQLITE3_TEXT
    ],
]);

// Count Records in the Table
$count = $db->query("SELECT count(*) AS 'record_count' FROM `$table`");
echo "$count[0][record_count] record(s)";

// Close the Database Connection
$db->close();
```

In summary, the script above demonstrates how to use the SQLite helper class to:

1. Set up a database connection.
2. Define a table schema.
3. Insert records using prepared statements.
4. Query the database.
5. Close the connection.