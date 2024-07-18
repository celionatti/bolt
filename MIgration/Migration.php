<?php

declare(strict_types=1);

/**
 * =======================================
 * Bolt - Migration Class ================
 * =======================================
 */

namespace celionatti\Bolt\Migration;

abstract class Migration
{
    /**
     * Run the migrations.
     */
    abstract public function up(): void;

    /**
     * Reverse the migrations.
     */
    abstract public function down(): void;
}