<?php

// Basic UUID generation
$uuid = UUID::generate();

// Ordered UUID (better for database indexing)
$orderedUuid = UUID::orderedGenerate();

// Short ID for URLs
$shortId = UUID::shortGenerate();

// Validation
if (UUID::validate($uuid)) {
    // Valid UUID
}

// Parsing components
$components = UUID::parse($uuid);
echo 'Created at: ' . date('Y-m-d H:i:s', $components['timestamp']);