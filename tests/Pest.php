<?php

use AOWD\SQLite;

function newDatabase(): SQLite
{
    return new SQLite(__DIR__ . '/example.sqlite');
}

function tableName(): string
{
    return 'example';
}
