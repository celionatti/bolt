<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============       ================
 * {CLASSNAME} Migration 
 * ===============       ================
 * ======================================
 */

namespace PhpStrike\migrations;

use celionatti\Bolt\Migration\BoltMigration;

class {CLASSNAME} extends BoltMigration
{
    /**
     * The Up method is to create table.
     *
     * @return void
     */
    public function up()
    {
        $this->createTable("users")
            ->id()->primaryKey()
            ->varchar("user_id")->index("user_id")
            ->varchar("surname")->index("surname")
            ->varchar("othername")->index("othername")
            ->varchar("email")->uniquekey("email")
            ->varchar("phone", 20)->nullable()
            ->varchar("avatar", 300)->nullable()
            ->varchar("password")
            ->enum("gender", ['male', 'female', 'others'])->defaultValue("others")
            ->enum("role", ['user', 'admin', 'editor'])->defaultValue("user")
            ->varchar("token", 300)->nullable()
            ->timestamp("token_expiration")->nullable()
            ->tinyint("is_verified")->defaultValue(0)
            ->tinyint("is_blocked")->defaultValue(0)
            ->timestamps()
            ->build();
    }
    
    /**
     * The Down method is to drop table
     *
     * @return void
     */
    public function down()
    {
        $this->dropTable("users");
    }
}