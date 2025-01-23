<?php

use AOWD\Exceptions\CompleteTransaction;
use AOWD\Exceptions\BeginTransaction;
use AOWD\Exceptions\ConnectError;
use AOWD\Exceptions\DatabasePath;
use AOWD\Exceptions\QueryError;
use AOWD\Exceptions\SetTable;
use AOWD\DataType;
use AOWD\SQLite;

test('Set table exception', function () {
    $db = newDatabase();

    try {
        $table_name = $db->getCurrentTableName();
    } catch (SetTable $e) {
        expect($e->getMessage())->toBe('Table name has not been set');
    }
});

test('Database path exception', function () {
    try {
        $db = new SQLite('');
    } catch (DatabasePath $e) {
        expect($e->getMessage())->toBe('Path provided is not a valid directory');
    }
});


test('Query exception', function () {
    $db = newDatabase();

    try {
        $results = $db->query("SELECT * FROM");
    } catch (QueryError $e) {
        expect($e->getMessage())->toBe('Unable to prepare statement: incomplete input - SELECT * FROM');
    }
});


test('Query parameters exception', function () {
    $db = newDatabase();
    $table = $db->setTableName(tableName());

    try {
        $results = $db->query("SELECT * FROM $table WHERE id = ?", true, [
            ['foo']
        ]);
    } catch (QueryError $e) {
        expect($e->getMessage())->toBe('Error Processing Params - SELECT * FROM example WHERE id = ?');
    }
});
