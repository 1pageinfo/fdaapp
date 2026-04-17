<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSanghsTable extends Migration
{
    public function up()
    {
        Schema::create('sanghs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sangh_sr_no')->unique(); // created dynamically
            $table->string('name_of_sangh')->nullable();
            $table->string('district')->nullable();
            $table->string('district_no')->nullable();
            $table->string('taluka')->nullable();
            $table->string('city')->nullable();
            $table->string('division')->nullable();
            $table->string('division_no')->nullable();
            $table->string('total_m_f')->nullable(); // "Total M/F" as text
            $table->date('date_meeting')->nullable(); // Date (Regulatory Board / Annual / Special Meeting)
            $table->string('receipt_no')->nullable();
            $table->date('receipt_date')->nullable();
            $table->decimal('receipt_amount', 12, 2)->nullable();
            $table->string('division_membership_no')->nullable();
            $table->unsignedInteger('male')->nullable();
            $table->unsignedInteger('female')->nullable();
            $table->unsignedInteger('total_members')->nullable();
            $table->text('address')->nullable();
            $table->string('president')->nullable();
            $table->string('tel_no')->nullable();
            $table->string('alt_tel_no')->nullable();
            $table->string('email')->nullable();
            $table->string('secretary')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sanghs');
    }
}
