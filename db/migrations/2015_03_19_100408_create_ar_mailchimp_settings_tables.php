<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Heonozis\AR\MailChimpSettings;

class CreateArMailchimpSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if(!Schema::hasTable('ar_mailchimp_settings')) {
            Schema::create('ar_mailchimp_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('key');
                $table->string('value');
                $table->timestamps();
            });
        }

        MailChimpSettings::create( [
            'key' => 'api_key' ,
            'value' => '' ,
        ] );

        MailChimpSettings::create( [
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
        Schema::drop('ar_mailchimp_settings');
    }
}
