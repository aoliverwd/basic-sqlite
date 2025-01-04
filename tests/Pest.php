<?php

use AOWD\SQLite;

ini_set('error_log', __DIR__ . '/error.log');

function databaseLocation(): string
{
    return __DIR__ . '/example.sqlite3';
}

function newDatabase(): SQLite
{
    return new SQLite(databaseLocation());
}

function tableName(): string
{
    return 'example';
}
