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
        console_logger("Up Migration...");
    }
    
    /**
     * The Down method is to drop table
     *
     * @return void
     */
    public function down()
    {
        console_logger("Down Migration...");
    }
}