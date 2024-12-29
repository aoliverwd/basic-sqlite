<?php

test('Insert 100 rows', function () {
    $db = newDatabase();
    $table = $db->setTableName(tableName());
    $query = <<<QUERY
    INSERT INTO `$table` (uuid, foo, bar) VALUES (:uuid, :foo, :bar)
    QUERY;

    for ($i = 1; $i <= 100; $i += 1) {
        $db->queryDB($query, false, [
            [
                ':uuid',
                uniqid('uuid' . time(), true),
                SQLITE3_TEXT
            ],
            [
                ':foo',
                uniqid('foo' . time(), true),
                SQLITE3_TEXT
            ],
            [
                ':bar',
                time(),
                SQLITE3_TEXT
            ],
        ]);
    }

    $count = $db->queryDB("SELECT count(*) AS 'record_count' FROM `$table`");
    expect($count[0]['record_count'])->toBe(100);
});
