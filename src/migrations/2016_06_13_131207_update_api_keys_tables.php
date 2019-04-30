<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateApiKeysTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        DB::statement('ALTER TABLE `api_keys` CHANGE `public_key` `api_key` varchar(255) CHARSET utf8 COLLATE utf8_unicode_ci NULL');
		Schema::table('api_keys', function (Blueprint $table) {
            $table->dateTime('valid_until')->nullable()->after('active');
            $table->dateTime('valid_from')->nullable()->after('active');
            $table->dropColumn('private_key');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        DB::statement('ALTER TABLE `api_keys` CHANGE `api_key` `public_key` varchar(255) CHARSET utf8 COLLATE utf8_unicode_ci NULL');
		Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn(['valid_from', 'valid_until']);
            $table->text('private_key');
        });
	}

}
