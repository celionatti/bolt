<?php

use Your\Project\Migration\Migration; // Adjust the namespace accordingly

// Create an instance of your migration class
$migration = new Migration();

// Create a table called 'users' with columns
$migration
    ->createTable('users')
    ->addIntColumn('id')->autoIncrement()->addPrimaryKey()
    ->addVarcharColumn('username', 255)->addUniqueIndex('username')
    ->addVarcharColumn('email', 255)->addUniqueIndex('email')
    ->addColumn('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP')
    ->addColumn('updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')
    ->insert();

// Create another table called 'posts' with columns
$migration
    ->createTable('posts')
    ->addIntColumn('id')->autoIncrement()->addPrimaryKey()
    ->addVarcharColumn('title', 255)
    ->addVarcharColumn('content', 1000)
    ->addForeignKey('user_id', 'users', 'id') // Add a foreign key reference to the 'users' table
    ->insert();

// Rename the 'posts' table to 'blog_posts'
$migration->renameTable('posts', 'blog_posts');

// Add a new column to the 'users' table
$migration->addColumn('is_admin TINYINT')->nullable()->insert();

// Drop the 'is_admin' column from the 'users' table
$migration->dropColumn('is_admin');

// Drop the 'blog_posts' table
$migration->dropTable('blog_posts');

// Drop the 'users' table
$migration->dropTable('users');



// ===============================================================================================

// Example usage of the new methods

// Add a nullable column 'age' to the 'users' table
$migration->createTable('users')
    ->addIntColumn('id')->autoIncrement()->addPrimaryKey()
    ->addVarcharColumn('username', 255)->addUniqueIndex('username')
    ->addVarcharColumn('email', 255)->addUniqueIndex('email')
    ->addColumn('age INT')->nullable() // Adding a nullable column
    ->timestamp('created_at')->useCurrent()
    ->timestamp('updated_at')->useCurrent()
    ->insert();

// Creating a table with a 'last_login' timestamp column
$migration->createTable('sessions')
    ->addIntColumn('id')->autoIncrement()->addPrimaryKey()
    ->addColumn('user_id INT')
    ->timestamp('created_at')->useCurrent()
    ->insert();
