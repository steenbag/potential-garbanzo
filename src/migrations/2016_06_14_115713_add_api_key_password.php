<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApiKeyPassword extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('api_keys', function (Blueprint $table) {
            $table->text('password')->nullable()->after('api_key');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn(['password']);
        });
	}

}
