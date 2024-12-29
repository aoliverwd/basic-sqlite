<?php

test('Create new database', function () {
    $db = newDatabase();
    $db->open();
    $db_exists = file_exists($db->getDatabaseLocation());
    $db->close();

    expect($db_exists)->toBeTrue();
});

test('Table not set', function () {
    $db = newDatabase();

    try {
        $db->getCurrentTableName();
    } catch (Exception $e) {
        expect($e->getMessage())->toBe('Table name has not been set');
    }
});

test('Table set', function () {
    $db = newDatabase();
    $table = $db->setTableName(tableName());
    expect($table)->toBe(tableName());
});

test('Register text field', function () {
    $db = newDatabase();
    $table = $db->setTableName(tableName());
    $colun_name = 'uuid';
    $colun_type = 'text';

    $db->registerColumn(
        column_name: $colun_name,
        type: $colun_type,
        can_be_null: false,
        is_post_required: true,
    );

    // First assertion
    expect($db->hasColumn($colun_name))->toBeTrue();

    // Create migration
    $db->migrate();

    // Get table columns
    $columns = $db->getColumns();

    // Test column name and type
    expect($columns[1]['name'])->toBe($colun_name);
    expect(strtolower($columns[1]['type']))->toBe($colun_type);
    expect($columns[1]['notnull'])->toBe(1);
});

test('Register unique index', function () {
    $db = newDatabase();
    $table = $db->setTableName(tableName());
    $colun_name = 'foo';
    $index_id = "idx_$colun_name";
    $colun_type = 'text';

    $db->registerColumn(
        column_name: $colun_name,
        type: $colun_type,
        can_be_null: false,
        is_post_required: true,
        is_index: true,
        is_unique: true
    );

    // Create migration
    $db->migrate();

    // Get table indices
    $indices = $db->getIndices();
    $key = $db->getKeyFromName($indices, $index_id);

    // Test column index exists and is unique
    expect($indices[$key]['name'])->toBe($index_id);
    expect($indices[$key]['unique'])->toBe(1);
});


test('Register non unique index', function () {
    $db = newDatabase();
    $table = $db->setTableName(tableName());
    $colun_name = 'bar';
    $index_id = "idx_$colun_name";
    $colun_type = 'text';

    $db->registerColumn(
        column_name: $colun_name,
        type: $colun_type,
        can_be_null: false,
        is_post_required: false,
        is_index: true,
        is_unique: false
    );

    // Create migration
    $db->migrate();

    // Get table indices
    $indices = $db->getIndices();

    $key = $db->getKeyFromName($indices, $index_id);

    // Test column index exists and is unique
    expect($indices[$key]['name'])->toBe($index_id);
    expect($indices[$key]['unique'])->toBe(0);
});
