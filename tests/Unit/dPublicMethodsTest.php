<?php

use AOWD\DataType;
use AOWD\SQLite;

test('Constructor', function () {
    $db = new SQLite(databaseLocation(), [
        'cache_size' => 10000
    ]);

    expect($db)->toBeInstanceOf(SQLite::class);
});

test('Clear registered columns', function () {
    $db = newDatabase();

    // Create a New SQLite Database Instance
    $db = new SQLite(__DIR__ . '/users.sqlite3');

    // Set the Target Table
    $table = 'users';
    $db->setTableName($table);

    // Register Columns for the Table
    $db->registerColumn('first_name', DataType::TEXT);

    // Clear registered columns
    $db->clearRegisteredColumns();

    // Check column does not exist
    expect($db->hasColumn('first_name'))->toBeFalse();

    // Register Columns for the Table
    $db->registerColumn('first_name', DataType::TEXT);

    // Create the Table Schema
    $db->migrate();

    // Check column does not exist
    expect($db->hasColumn('first_name'))->toBeFalse();
});
