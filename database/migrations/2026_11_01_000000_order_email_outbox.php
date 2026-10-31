<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('order_email_outbox',function(Blueprint $t){$t->id();$t->unsignedInteger('order_id')->index();$t->string('event_key',64)->unique();$t->json('payload');$t->unsignedTinyInteger('attempts')->default(0);$t->timestamp('available_at')->nullable()->index();$t->timestamp('sent_at')->nullable();$t->string('last_error',100)->nullable();$t->timestamps();}); }
 public function down(): void { Schema::dropIfExists('order_email_outbox'); }
};
