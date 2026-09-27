<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliar rma_requests con trazabilidad de resoluciones
        if (Schema::hasTable('rma_requests')) {
            Schema::table('rma_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('rma_requests', 'replacement_order_id')) {
                    $table->unsignedBigInteger('replacement_order_id')->nullable()->after('resolution_type');
                }
                if (!Schema::hasColumn('rma_requests', 'replacement_order_number')) {
                    $table->string('replacement_order_number', 50)->nullable()->after('replacement_order_id');
                }
                if (!Schema::hasColumn('rma_requests', 'store_credit_code')) {
                    $table->string('store_credit_code', 50)->nullable()->after('replacement_order_number');
                }
                if (!Schema::hasColumn('rma_requests', 'resolved_at')) {
                    $table->timestamp('resolved_at')->nullable()->after('status');
                }
                if (!Schema::hasColumn('rma_requests', 'resolved_by_user_id')) {
                    $table->unsignedBigInteger('resolved_by_user_id')->nullable()->after('resolved_at');
                }
            });
        }

        // 2. Ampliar orders con tipado de orden y vinculo a garantia
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'order_type')) {
                    $table->string('order_type', 40)->default('regular')->after('status');
                }
                if (!Schema::hasColumn('orders', 'rma_request_id')) {
                    $table->unsignedBigInteger('rma_request_id')->nullable()->after('order_type');
                }
            });
        }

        // 3. Crear tabla inmutable de resoluciones RMA (TASK-INT-09, TASK-LOG-04B)
        if (!Schema::hasTable('rma_resolutions')) {
            Schema::create('rma_resolutions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('rma_request_id');
                $table->string('resolution_type', 50); // 'replacement_order', 'store_credit', 'rejected_sanitary_violation', 'refund'
                $table->unsignedBigInteger('replacement_order_id')->nullable();
                $table->string('replacement_order_number', 50)->nullable();
                $table->string('store_credit_code', 50)->nullable();
                $table->decimal('store_credit_amount', 10, 2)->default(0.00);
                $table->boolean('whatsapp_message_sent')->default(false);
                $table->json('whatsapp_payload')->nullable();
                $table->unsignedBigInteger('resolved_by_user_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('rma_request_id')->references('id')->on('rma_requests')->cascadeOnDelete();
                $table->foreign('replacement_order_id')->references('id')->on('orders')->nullOnDelete();
                $table->foreign('resolved_by_user_id')->references('id')->on('users')->nullOnDelete();
                $table->index(['rma_request_id', 'resolution_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rma_resolutions');

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'rma_request_id')) {
                    $table->dropColumn('rma_request_id');
                }
                if (Schema::hasColumn('orders', 'order_type')) {
                    $table->dropColumn('order_type');
                }
            });
        }

        if (Schema::hasTable('rma_requests')) {
            Schema::table('rma_requests', function (Blueprint $table) {
                $columns = ['replacement_order_id', 'replacement_order_number', 'store_credit_code', 'resolved_at', 'resolved_by_user_id'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('rma_requests', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
