<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_issue_note_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('issue_note_id');
            $table->unsignedInteger('requisition_item_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('quantity_requested');
            $table->unsignedInteger('quantity_issued');
            $table->timestamps();

            $table->foreign('issue_note_id')->references('id')->on('tbl_issue_notes')->cascadeOnDelete();
            $table->foreign('requisition_item_id')->references('id')->on('tbl_requisition_items');
            $table->foreign('item_id')->references('id')->on('tbl_items');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_issue_note_items');
    }
};