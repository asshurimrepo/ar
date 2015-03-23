<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Heonozis\AR\AweberSettings;

class CreateArAweberSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('ar_aweber_settings')) {
            Schema::create('ar_aweber_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('key');
                $table->string('value');
                $table->timestamps();
            });
        }

        AweberSettings::create( [
            'key' => 'customer_key' ,
            'value' => '' ,
        ] );
        AweberSettings::create( [
            'key' => 'customer_secret' ,
            'value' => '' ,
        ] );
        AweberSettings::create( [
            'key' => 'access_key' ,
            'value' => '' ,
        ] );
        AweberSettings::create( [
            'key' => 'access_secret' ,
            'value' => '' ,
        ] );
        AweberSettings::create( [
            'key' => 'list_name' ,
            'value' => '' ,
        ] );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ar_aweber_settings');
    }
}
