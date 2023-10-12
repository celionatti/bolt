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
        $this->dropTable("users");
    }
}