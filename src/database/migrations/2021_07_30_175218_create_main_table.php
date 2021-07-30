<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMainTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 75);
            $table->double('price', 15, 2)->default(0);
            $table->boolean('is_active')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')->constrained('users');
            $table->foreignId('company_id')->constrained('company');
            $table->string('pr_no', 30)->unique();
            $table->double('total', 15, 2)->default(0);
            $table->smallInteger('pay_status')->default(0);
            $table->integer('qty_total')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchases_id')->constrained('purchases');
            $table->foreignId('product_id')->constrained('products');
            $table->double('price', 15, 2)->default(0);
            $table->integer('qty')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('ac_code', 10)->unique();
            $table->string('name', 25);
            $table->string('parent_code', 10)->nullable();
            $table->boolean('is_active')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('company_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounts_id')->constrained('accounts');
            $table->foreignId('company_id')->constrained('company');
            $table->double('debit', 15, 2)->default(0);
            $table->double('credit', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('refno', 30)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounts_id')->constrained('accounts');
            $table->foreignId('users_id')->constrained('users');
            $table->double('debit', 15, 2)->default(0);
            $table->double('credit', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('refno', 30)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->constrained('company')->after('password');
            $table->geometry('location')->after('password');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_balances');
        Schema::dropIfExists('customer_balances');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('purchase_detail');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('products');
    }
}
