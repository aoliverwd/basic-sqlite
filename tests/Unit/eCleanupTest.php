<?php

test('Clean up', function () {
    $db = newDatabase();
    $db_location = $db->getDatabaseLocation();

    if (file_exists($db_location)) {
        unlink($db_location);
    }

    expect(file_exists($db_location))->toBeFalse();
});
