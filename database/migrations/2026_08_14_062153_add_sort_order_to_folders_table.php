<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('year');
        });

        // Initialize sort_order for existing main folders
        $mainFolderIds = DB::table('folders')
            ->whereNull('parent_id')
            ->orderBy('year', 'desc')
            ->orderBy('id')
            ->pluck('id');

        foreach ($mainFolderIds as $index => $folderId) {
            DB::table('folders')
                ->where('id', $folderId)
                ->update(['sort_order' => $index + 1]);
        }

        // Initialize sort_order for existing subfolders grouped by parent_id
        $subfolders = DB::table('folders')
            ->whereNotNull('parent_id')
            ->orderBy('parent_id')
            ->orderBy('id')
            ->get()
            ->groupBy('parent_id');

        foreach ($subfolders as $parentId => $folderRows) {
            foreach ($folderRows->values() as $index => $folderRow) {
                DB::table('folders')
                    ->where('id', $folderRow->id)
                    ->update(['sort_order' => $index + 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
