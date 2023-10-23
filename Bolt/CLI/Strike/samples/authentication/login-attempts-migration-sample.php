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

use Bolt\Bolt\Migration\BoltMigration;

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
            ->varchar("user_attempted")->nullable()->foreignKey("user_attempted", "users", "email")->uniqueKey("user_attempted")
            ->int("attempts")
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