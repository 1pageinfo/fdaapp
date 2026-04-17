<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('description');
        });

        Schema::table('chats', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('tab');
        });

        $groupIds = DB::table('groups')
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('id');

        foreach ($groupIds as $index => $groupId) {
            DB::table('groups')
                ->where('id', $groupId)
                ->update(['sort_order' => $index + 1]);
        }

        $chatGroups = DB::table('chats')
            ->select('id', 'group_id')
            ->orderBy('group_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy('group_id');

        foreach ($chatGroups as $chatRows) {
            foreach ($chatRows->values() as $index => $chatRow) {
                DB::table('chats')
                    ->where('id', $chatRow->id)
                    ->update(['sort_order' => $index + 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};