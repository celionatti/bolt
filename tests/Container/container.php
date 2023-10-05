<?php

/**
 * Usage
 */


 // Create an instance of the Container class
$container = new Container();

// Bind a class to an abstract name or a closure
$container->bind('LoggerInterface', FileLogger::class);

// Resolve an instance from the container
$logger = $container->make('LoggerInterface');

// Create a singleton binding
$container->singleton('DatabaseConnection', function () {
    return new DatabaseConnection('localhost', 'user', 'password');
});

// Resolve a singleton instance
$dbConnection1 = $container->make('DatabaseConnection');
$dbConnection2 = $container->make('DatabaseConnection');

// $dbConnection1 and $dbConnection2 will be the same instance due to singleton binding
