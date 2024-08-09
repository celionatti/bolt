<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============       ================
 * {CLASSNAME} Migration 
 * ===============       ================
 * ======================================
 */

 use celionatti\Bolt\Migration\Migration;
 use celionatti\Bolt\illuminate\Schema\Schema;
 use celionatti\Bolt\illuminate\Schema\Blueprint;

return new class extends Migration
{
    /**
     * The Up method is to create table.
     *
     * @return void
     */
    public function up():void
    {
        Schema::create("{TABLENAME}", function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('rate_limits', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique('key');
            $table->integer('attempts')->default(0);
            $table->integer('expires_at');
            $table->timestamps();
        });
    }

    /**
     * The Down method is to drop table
     *
     * @return void
     */
    public function down():void
    {
        Schema::dropIfExists("{TABLENAME}");
    }
};