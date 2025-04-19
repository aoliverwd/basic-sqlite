<?php

use AOWD\SQLite;

test("Insert 1,000 rows using named placeholders", function () {
    $db = newDatabase();
    $table = $db->setTableName(tableName());
    $query = <<<QUERY
INSERT INTO `$table` (uuid, foo, bar) VALUES (:uuid, :foo, :bar)
QUERY;

    for ($i = 1; $i <= 1000; $i += 1) {
        $db->query($query, false, [
            [":uuid", uniqid("uuid" . time(), true)],
            [":foo", uniqid("foo" . time(), true)],
            [":bar", time()],
        ]);
    }

    $count = $db->query("SELECT count(*) AS 'record_count' FROM `$table`");
    expect($count[0]["record_count"])->toBe(1000);
});

test("Insert 1,000 rows using positional placeholders", function () {
    $db = newDatabase();
    $table = $db->setTableName(tableName());
    $query = <<<QUERY
INSERT INTO `$table` (uuid, foo, bar) VALUES (?, ?, ?)
QUERY;

    for ($i = 1; $i <= 1000; $i += 1) {
        $db->query($query, false, [
            [1, uniqid("uuid" . time(), true), SQLITE3_TEXT],
            [2, uniqid("foo" . time(), true), SQLITE3_TEXT],
            [3, time(), SQLITE3_TEXT],
        ]);
    }

    $count = $db->query("SELECT count(*) AS 'record_count' FROM `$table`");
    expect($count[0]["record_count"])->toBe(2000);
});

test("Open database as readonly", function () {
    $db = new SQLite(db_location: databaseLocation(), readonly: true);
    $table = $db->setTableName(tableName());

    $count = $db->query("SELECT count(*) AS 'record_count' FROM `$table`");
    expect($count[0]["record_count"])->toBe(2000);
});
