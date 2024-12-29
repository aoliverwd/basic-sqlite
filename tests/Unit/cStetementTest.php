<?php

test('Create query is write statement', function () {
    $db = newDatabase();

    $query = <<<QUERY
    CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    QUERY;

    expect($db->queryIsWriteStatement($query))->toBeTrue();
});

test('Insert query is write statement', function () {
    $db = newDatabase();

    $query = <<<QUERY
    INSERT INTO users (username, email) VALUES ('user1', 'user1@example.com');
    QUERY;

    expect($db->queryIsWriteStatement($query))->toBeTrue();
});


test('Delete query is write statement', function () {
    $db = newDatabase();

    $query = <<<QUERY
    DELETE FROM users WHERE id = 1;
    QUERY;

    expect($db->queryIsWriteStatement($query))->toBeTrue();
});

test('Update query is write statement', function () {
    $db = newDatabase();

    $query = <<<QUERY
    UPDATE users SET email = 'updated_email@example.com' WHERE username = 'user1';
    QUERY;

    expect($db->queryIsWriteStatement($query))->toBeTrue();
});

test('Alter query is write statement', function () {
    $db = newDatabase();

    $query = <<<QUERY
    ALTER TABLE posts_new RENAME TO posts;
    QUERY;

    expect($db->queryIsWriteStatement($query))->toBeTrue();
});

test('Drop query is write statement', function () {
    $db = newDatabase();

    $query = <<<QUERY
    DROP TABLE IF EXISTS posts;
    QUERY;

    expect($db->queryIsWriteStatement($query))->toBeTrue();
});

test('Insert query with bound parameters is write statement', function () {
    $db = newDatabase();

    $query = <<<QUERY
    INSERT INTO users (username, email) VALUES (:name, :email);
    QUERY;

    expect($db->queryIsWriteStatement($query))->toBeTrue();
});

test('Query is not write statement', function () {
    $db = newDatabase();

    $query = <<<QUERY
    SELECT id, username FROM users WHERE email LIKE '%example.com';
    QUERY;

    expect($db->queryIsWriteStatement($query))->toBeFalse();
});

test('Complex query is not write statement', function () {
    $db = newDatabase();

    $query = <<<QUERY
    SELECT u.username, p.title, p.published_at
    FROM users u
    JOIN posts p ON u.id = p.user_id
    WHERE p.published_at IS NOT NULL;
    QUERY;

    expect($db->queryIsWriteStatement($query))->toBeFalse();
});

test('Join query is not write statement', function () {
    $db = newDatabase();

    $query = <<<QUERY
    SELECT u.username, COUNT(p.id) AS post_count 
    FROM users u
    LEFT JOIN posts p ON u.id = p.user_id
    WHERE u.username = :username
    GROUP BY u.id;
    QUERY;

    expect($db->queryIsWriteStatement($query))->toBeFalse();
});
