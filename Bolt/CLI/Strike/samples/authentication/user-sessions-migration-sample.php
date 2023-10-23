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
        $this->createTable("user_sessions")
            ->id()->primaryKey()
            ->varchar("user_id", 255)->nullable()->uniqueKey("user_id")
            ->varchar("token_hash")->nullable()
            ->varchar("expiration")->nullable()
            ->build();
    }

    /**
     * The Down method is to drop table
     *
     * @return void
     */
    public function down()
    {
        $this->dropTable("user_sessions");
    }
}