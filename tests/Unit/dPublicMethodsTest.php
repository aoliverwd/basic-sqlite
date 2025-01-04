<?php

use AOWD\DataType;
use AOWD\SQLite;

test('Constructor', function () {
    $db = new SQLite(databaseLocation(), [
        'cache_size' => 10000
    ]);

    expect($db)->toBeInstanceOf(SQLite::class);
});
