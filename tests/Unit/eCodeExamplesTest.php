<?php

use AOWD\DataType;
use AOWD\SQLite;

test('TLDR', function () {
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
    // echo "$count[0][record_count] record(s)";

    // Close the Database Connection
    $db->close();

    // Test exception
    expect($count[0]['record_count'])->toBe(1);
});
