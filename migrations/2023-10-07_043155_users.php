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
            ->varchar("username", 255)->nullable()->index("username")
            ->varchar("email", 255)->uniquekey("email")
            ->enum("acl", ['guest', 'admin'])->defaultValue("guest")
            ->varchar("password")
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
        $this->consoleLog("Down Migration...");
    }
}