<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixApiFks extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Load all existing API Keys and delete any grants that belong to orphaned keys.
        $keys = \Steenbag\Tubes\Keys\Ardent\ApiKey::all();
        $keyIds = $keys->modelKeys();
        \Steenbag\Tubes\Keys\Ardent\ApiGrant::whereNotIn('api_key_id', $keyIds + [-1])->delete();

        // then add our FKs.
        Schema::table('api_grants', function (Blueprint $table) {
            $table->foreign('api_key_id')->references('id')->on('api_keys')->onDelete('CASCADE');
        });

        Schema::table('api_referrers', function (Blueprint $table) {
            $table->foreign('api_key_id')->references('id')->on('api_keys')->onDelete('CASCADE');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('api_grants', function (Blueprint $table) {
            $table->dropForeign('api_grants_api_key_id_foreign');
        });

        Schema::table('api_referrers', function (Blueprint $table) {
            $table->dropForeign('api_referrers_api_key_id_foreign');
        });
	}

}
