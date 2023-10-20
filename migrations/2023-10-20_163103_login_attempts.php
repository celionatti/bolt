<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============       ================
 * BM_2023_10_20_163103_login_attempts Migration 
 * ===============       ================
 * ======================================
 */

namespace Bolt\migrations;

use Bolt\Bolt\Migration\BoltMigration;

class BM_2023_10_20_163103_login_attempts extends BoltMigration
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
            ->varchar("user_id", 255)->nullable()
            ->varchar("user_attempted")
            ->varchar("ip_address")->nullable()
            ->varchar("user_agent")->nullable()
            ->timestamp("timestamp")
            ->enum("success", ['true', 'false'])->defaultValue("false")
            ->varchar("failure_reason")->nullable()
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
