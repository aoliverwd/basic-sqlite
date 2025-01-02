<?php

use AOWD\SQLite;

ini_set('error_log', __DIR__ . '/error.log');

function newDatabase(): SQLite
{
    return new SQLite(__DIR__ . '/example.sqlite3');
}

function tableName(): string
{
    return 'example';
}
