<?php

// Basic SELECT
$results = $queryBuilder->select(['name', 'email'])
    ->from('users')
    ->where('age', '>', 18)
    ->orderBy('name', 'DESC')
    ->limit(10)
    ->execute();

// Complex query with JOIN and pagination
$paginated = $queryBuilder->select(['users.*', 'orders.total'])
    ->from('users')
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->whereNested(function ($query) {
        $query->where('users.active', 1)
            ->orWhere('users.verified', 1);
    })
    ->paginate(15, 2);

// UPSERT example
$queryBuilder->insert('users', [
        'email' => 'test@example.com',
        'name' => 'Test User'
    ])
    ->onDuplicateKeyUpdate(['name'])
    ->execute();