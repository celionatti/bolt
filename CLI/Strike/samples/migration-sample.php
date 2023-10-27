<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============       ================
 * {CLASSNAME} Migration 
 * ===============       ================
 * ======================================
 */

namespace Bolt\migrations;

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
        $this->createTable("login_attempts")
            ->id()->primaryKey()
            ->varchar("user_id", 255)->nullable()->foreignKey("user_id", "users", "user_id")
            ->varchar("user_attempted")
            ->varchar("ip_address")->nullable()
            ->varchar("user_agent")->nullable()
            ->timestamp("timestamp")
            ->enum("success", ['true', 'false'])->defaultValue("false")
            ->varchar("failure_reason")->nullable()
            ->build(true);
    }

    /**
     * The Down method is to drop table
     *
     * @return void
     */
    public function down()
    {
        $this->dropTable("login_attempts");
    }
}