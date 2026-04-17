<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('files', function (Blueprint $table) {
            if (!Schema::hasColumn('files', 'disk_path')) {
                $table->string('disk_path')->nullable()->after('path');
            }
        });
    }

    public function down()
    {
        Schema::table('files', function (Blueprint $table) {
            if (Schema::hasColumn('files', 'disk_path')) {
                $table->dropColumn('disk_path');
            }
        });
    }

};
