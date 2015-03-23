<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Heonozis\AR\GetResponseSettings;

class CreateArGetresponseSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('ar_getresponse_settings')) {
            Schema::create('ar_getresponse_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('key');
                $table->string('value');
                $table->timestamps();
            });

            GetResponseSettings::create( [
                'key' => 'api_key' ,
                'value' => '' ,
            ] );

            GetResponseSettings::create( [
                'key' => 'campaign_name' ,
                'value' => '' ,
            ] );

        }


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ar_getresponse_settings');
    }
}
