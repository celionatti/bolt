<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============       ================
 * BM_2023_10_07_043155_users Migration 
 * ===============       ================
 * ======================================
 */

namespace Bolt\migrations;

use Bolt\Bolt\Migration\BoltMigration;

class BM_2023_10_07_043155_users extends BoltMigration
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
            ->varchar("user_id", 100)->index("user_id")
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
            ->enum("is_verified", ['true', 'false'])->defaultValue("false")
            ->enum("is_blocked", ['true', 'false'])->defaultValue("false")
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