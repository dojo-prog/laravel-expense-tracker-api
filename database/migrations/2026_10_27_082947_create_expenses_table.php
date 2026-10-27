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
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUuid('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();
            

            $table->text('description')->nullable();
            $table->decimal('amount', 8, 2);
            $table->date('expense_date');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(
                ['user_id', 'expense_date'], 
                'idx_expense_user_date'
            );
            $table->index(
                ['user_id', 'category_id', 'expense_date'], 
                'idx_expense_user_category_date'
            );
        });

        DB::statement('
            ALTER TABLE expenses 
            ADD CONSTRAINT check_amount_gt_zero 
                CHECK (amount > 0)
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            ALTER TABLE expenses 
            DROP CONSTRAINT check_amount_gt_zero
        ');

        Schema::dropIfExists('expenses');
    }
};
