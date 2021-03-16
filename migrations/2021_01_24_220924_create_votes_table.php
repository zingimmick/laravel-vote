<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVotesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            config('vote.table_names.votes'),
            function (Blueprint $table): void {
                config('vote.uuids') ? $table->uuid('uuid') : $table->bigIncrements('id');
                $table->unsignedBigInteger(config('vote.column_names.user_foreign_key'))->index()->comment('user_id');
                $table->morphs('voteable');
                $table->boolean('upvote')->default(true);
                $table->timestamps();
                $table->unique([config('vote.column_names.user_foreign_key'), 'voteable_type', 'voteable_id']);
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('vote.table_names.votes'));
    }
}
