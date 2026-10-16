<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CRM: leads (every form on the site lands here), the parts a lead asked for,
 * and orders with a public tracking code + PIN and a status history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->nullable()->unique();     // L-000123, set after insert
            $table->string('type', 20)->default('quote');           // quote | recoating | wizard | callback | contact
            $table->string('status', 20)->default('new')->index();  // see App\Enums\LeadStatus

            $table->string('name');
            $table->string('phone', 32);
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->text('message')->nullable();
            $table->json('attachments')->nullable();  // uploaded drawings / photos
            $table->json('payload')->nullable();      // wizard answers, recoating calculator values

            $table->string('locale', 2)->default('uz');
            $table->string('source')->nullable();     // page or utm_source
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('lead_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');                 // snapshot, survives product edits
            $table->unsignedInteger('quantity')->default(1);
            $table->json('options')->nullable();            // configurator: diameter, length, hardness...
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->nullable()->unique();  // IR-1042, public tracking code
            $table->string('pin', 4);                         // given to the customer with the code
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();

            $table->string('customer_name');
            $table->string('customer_phone', 32);
            $table->string('company')->nullable();

            $table->string('status', 20)->default('accepted')->index(); // see App\Enums\OrderStatus
            $table->date('due_date')->nullable();
            $table->decimal('total', 14, 2)->nullable();
            $table->text('notes')->nullable();                // internal, never shown publicly
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20);
            $table->string('comment')->nullable();
            $table->boolean('is_public')->default(true);      // shown on the tracking page
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_events');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('lead_items');
        Schema::dropIfExists('leads');
    }
};
