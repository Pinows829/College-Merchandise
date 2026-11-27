<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('colleges')) {
            Schema::create('colleges', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 30)->unique();
                $table->string('name', 150);
            });
        }

        if (! Schema::hasTable('courses')) {
            Schema::create('courses', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('college_id');
                $table->string('name', 180);
                $table->unique(['college_id', 'name']);
                $table->foreign('college_id')->references('id')->on('colleges')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('student_id', 40)->unique();
                $table->string('email', 190)->unique();
                $table->string('password');
                $table->string('full_name', 150);
                $table->unsignedInteger('college_id')->nullable();
                $table->enum('role', ['student', 'admin'])->default('student');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('college_id');
                $table->unsignedInteger('course_id')->nullable();
                $table->string('name', 160);
                $table->enum('category', ['Shirts', 'Jackets', 'Accessories'])->default('Shirts');
                $table->text('description');
                $table->decimal('price', 10, 2)->default(0);
                $table->index('name');
                $table->foreign('college_id')->references('id')->on('colleges')->restrictOnDelete()->cascadeOnUpdate();
                $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete()->cascadeOnUpdate();
            });
        } elseif (! Schema::hasColumn('products', 'course_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('course_id')->nullable()->after('college_id');
                $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete()->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('product_sizes')) {
            Schema::create('product_sizes', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('product_id');
                $table->string('size', 30);
                $table->unsignedInteger('stock')->default(0);
                $table->unique(['product_id', 'size']);
                $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete()->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->string('order_code', 40)->nullable()->unique();
                $table->decimal('total', 10, 2)->default(0);
                $table->enum('payment_method', ['GCash', 'Cash on Pickup']);
                $table->string('gcash_ref', 100)->default('');
                $table->enum('status', ['Pending', 'Paid', 'Preparing', 'Ready for Pickup', 'Completed'])->default('Pending');
                $table->timestamp('created_at')->useCurrent();
                $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('order_id');
                $table->unsignedInteger('product_size_id');
                $table->unsignedInteger('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreign('product_size_id')->references('id')->on('product_sizes')->restrictOnDelete()->cascadeOnUpdate();
            });
        }

        $collegeNames = [
            'CCICT' => 'College of Computing, Information and Communications Technology',
            'CAS' => 'College of Arts and Sciences',
            'COE' => 'College of Engineering',
            'COT' => 'College of Technology',
            'COED' => 'College of Education',
            'CME' => 'College of Management and Entrepreneurship',
        ];

        foreach ($collegeNames as $code => $name) {
            \App\Models\College::query()->updateOrCreate(['code' => $code], ['name' => $name]);
        }
    }

    public function down(): void
    {
        // This migration is intentionally non-destructive for an existing merch_db.
    }
};
