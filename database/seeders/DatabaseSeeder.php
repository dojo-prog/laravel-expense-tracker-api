<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = collect([
            User::firstOrCreate(
                ['email' => 'dojo@example.com'],
                [
                    'name' => 'Dojo Example',
                    'password' => Hash::make('testPass'),
                ],
            ),
            ...User::factory()->count(3)->create(),
        ]);

        $users->each(function (User $user): void {
            CategorySeeder::categoriesFor($user);

            $categories = $user->categories()->get();

            Expense::factory()
                ->count(fake()->numberBetween(20, 40))
                ->recycle($user)
                ->recycle($categories)
                ->create();

            $month = now()->startOfMonth()->toDateString();

            $categories
                ->random(fake()->numberBetween(4, 6))
                ->each(fn ($category) => Budget::firstOrCreate(
                    ['user_id' => $user->id, 'category_id' => $category->id, 'month' => $month],
                    ['limit_amount' => fake()->randomFloat(2, 100, 2000)],
                ));
        });
    }
}
