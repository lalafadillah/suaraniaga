<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->after('product_id');
            $table->string('recipient_name')->nullable()->after('quantity');
            $table->string('recipient_phone')->nullable()->after('recipient_name');
            $table->text('shipping_address')->nullable()->after('recipient_phone');
            $table->text('note')->nullable()->after('shipping_address');
            $table->string('payment_method')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'quantity',
                'recipient_name',
                'recipient_phone',
                'shipping_address',
                'note',
                'payment_method',
            ]);
        });
    }
};
