<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiKeyTables extends Migration {

	/**
	 * Create our API key tables.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('api_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->string('client_name', 255);
            $table->text('notes');
            $table->string('slug', 255);
            $table->enum('type', ['hosted', 'remote'])->default('hosted');
            $table->string('public_key', 255);
            $table->text('private_key');
            $table->boolean('active')->default(true);

            $table->timestamps();
            $table->engine = 'InnoDB';
        });

        /**
         * Create a table storing the valie referrers for each key.
         */
        Schema::create('api_referrers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('api_key_id')->unsigned();

            $table->enum('type', ['ip', 'uri', 'script']);
            $table->string('value', 512);

            $table->timestamps();
            $table->engine = 'InnoDB';
        });

        /**
         * Create a table storing api access permissions for clients.
         */
        Schema::create('api_grants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('api_key_id')->unsigned();
            $table->string('api', 255);
            $table->string('method', 255);
            $table->boolean('value')->default(true);

            $table->timestamps();
            $table->engine = 'InnoDB';
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('api_keys');
        Schema::dropIfExists('api_referrers');
        Schema::dropIfExists('api_grants');
	}

}
