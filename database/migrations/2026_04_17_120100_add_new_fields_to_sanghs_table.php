<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sanghs', function (Blueprint $table) {
            $table->string('unique_ref_no')->nullable()->after('sangh_sr_no');
            $table->unsignedBigInteger('pradeshik_sr_no')->nullable()->after('unique_ref_no');
            $table->string('pradeshik_ref_no')->nullable()->after('pradeshik_sr_no');
            $table->unsignedBigInteger('district_sr_no')->nullable()->after('pradeshik_ref_no');
            $table->string('district_ref_no')->nullable()->after('district_sr_no');

            $table->unsignedSmallInteger('registration_year')->nullable()->after('district_ref_no');
            $table->string('category_code', 1)->nullable()->after('registration_year');
            $table->string('sangh_type_code', 1)->nullable()->after('category_code');

            $table->string('pradeshik_vibhag')->nullable()->after('sangh_type_code');
            $table->string('pradeshik_vibhag_code', 10)->nullable()->after('pradeshik_vibhag');
            $table->string('district_code', 10)->nullable()->after('district');

            $table->string('village')->nullable()->after('taluka');
            $table->string('mukkam_post')->nullable()->after('city');
            $table->string('pincode', 20)->nullable()->after('mukkam_post');
            $table->string('road_path')->nullable()->after('address');
            $table->string('ward_section')->nullable()->after('road_path');

            $table->string('president_phone', 30)->nullable()->after('president');
            $table->string('president_whatsapp', 30)->nullable()->after('president_phone');
            $table->string('president_email')->nullable()->after('president_whatsapp');
            $table->string('secretary_phone', 30)->nullable()->after('secretary');
            $table->string('secretary_whatsapp', 30)->nullable()->after('secretary_phone');
            $table->string('secretary_email')->nullable()->after('secretary_whatsapp');
        });
    }

    public function down(): void
    {
        Schema::table('sanghs', function (Blueprint $table) {
            $table->dropColumn([
                'unique_ref_no',
                'pradeshik_sr_no',
                'pradeshik_ref_no',
                'district_sr_no',
                'district_ref_no',
                'registration_year',
                'category_code',
                'sangh_type_code',
                'pradeshik_vibhag',
                'pradeshik_vibhag_code',
                'district_code',
                'village',
                'mukkam_post',
                'pincode',
                'road_path',
                'ward_section',
                'president_phone',
                'president_whatsapp',
                'president_email',
                'secretary_phone',
                'secretary_whatsapp',
                'secretary_email',
            ]);
        });
    }
};
