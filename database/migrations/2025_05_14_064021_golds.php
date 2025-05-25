<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('address');
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('karats', function (Blueprint $table) {
            $table->id();
            $table->string('karat');
            $table->decimal('rate', 5, 2);
            $table->bigInteger('buy_price');
            $table->bigInteger('sell_price');
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->boolean('status')->default(false);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('types')->onDelete('cascade');
            $table->foreignId('karat_id')->constrained('karats')->onDelete('cascade');
            $table->decimal('weight', 10, 2);
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->integer('stock_quantity')->default(0);
            $table->date('received_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_totals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('total')->default(0);
            $table->timestamps();
        });

        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->date('opname_date');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();
        });

        Schema::create('stock_opname_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('stock_total_id')->constrained('stock_totals')->cascadeOnDelete();
            $table->integer('physical_quantity');
            $table->integer('quantity_difference');
            $table->timestamps();
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->decimal('weight', 15, 2);
            $table->bigInteger('buy_price')->default(0);
            $table->bigInteger('subtotal')->default(0);
            $table->timestamps();
        });
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->unique();
            $table->enum('transaction_type', ['sale', 'purchase', 'pawning', 'change']);
            $table->enum('payment_method', ['cash', 'online'])->default('cash');
            $table->string('payment_link')->nullable();
            $table->enum('status', ['pending', 'success', 'expired', 'failed'])->default('pending');
            $table->dateTime('transaction_date')->nullable();
            $table->bigInteger('total_amount')->default(0);
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('cash')->default(0);
            $table->bigInteger('change')->default(0);
            $table->bigInteger('discount')->default(0);
            $table->bigInteger('total_amount');
            $table->timestamps();
        });

        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('weight', 15, 2);
            $table->bigInteger('buy_price')->default(0);
            $table->bigInteger('subtotal')->default(0);
            $table->timestamps();
        });


        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->bigInteger('total_amount')->default(0);
            $table->timestamps();
        });


        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('weight', 15, 2);
            $table->bigInteger('buy_price')->default(0);
            $table->bigInteger('subtotal')->default(0);
            $table->timestamps();
        });

        Schema::create('pawnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade')->onUpdate('cascade');
            $table->date('pawn_date');
            $table->bigInteger('estimated_value')->default(0);
            $table->decimal('rate', 5, 2);
            $table->date('due_date');
            $table->bigInteger('cash')->default(0);
            $table->bigInteger('change')->default(0);
            $table->enum('status', ['pending', 'active', 'paid_off'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pawning_details', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('pawning_id')->constrained('pawnings')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('types')->onDelete('cascade');
            $table->foreignId('karat_id')->constrained('karats')->onDelete('cascade');
            $table->decimal('weight', 10, 2);
            $table->integer('quantity');
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->bigInteger('cash')->default(0); // uang yang dibayar customer (kalau tukar kurang)
            $table->bigInteger('change')->default(0); // kembalian kalau tukar tambah
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('changes');
        Schema::dropIfExists('pawning_details');
        Schema::dropIfExists('pawnings');
        Schema::dropIfExists('purchase_details');
        Schema::dropIfExists('sale_details');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('stock_opname_details');
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('stock_totals');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('products');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('karats');
        Schema::dropIfExists('types');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('customers');
    }
};
