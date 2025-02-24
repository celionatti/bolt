<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============       ================
 * {{CLASSNAME}} Migration
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
        Schema::create("{{TABLENAME}}", function (Blueprint $table) {
            $table->id();
            $table->string('{{UNIQUENAME}}_id')->index('{{UNIQUENAME}}_id');
            $table->string('name')->nullable();
            $table->string('email')->unique('email');
            $table->string('password');
            $table->text('bio');
            $table->string('remember_token');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('email_verified_at')->nullable();
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
        Schema::dropIfExists("{{TABLENAME}}");
    }
};