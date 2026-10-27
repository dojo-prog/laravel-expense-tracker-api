<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * The canonical expense-tracker categories seeded for each user.
     *
     * @return array<string, array{description: string, color: string}>
     */
    public static function categories(): array
    {
        return [
            'Groceries' => [
                'description' => 'Supermarket and grocery store purchases',
                'color' => '#22c55e',
            ],
            'Dining Out' => [
                'description' => 'Restaurants, cafes, and takeout',
                'color' => '#f97316',
            ],
            'Rent & Utilities' => [
                'description' => 'Rent, electricity, water, and internet bills',
                'color' => '#3b82f6',
            ],
            'Transportation' => [
                'description' => 'Fuel, public transit, ride shares, and parking',
                'color' => '#06b6d4',
            ],
            'Health & Medical' => [
                'description' => 'Pharmacy, doctor visits, and medical treatments',
                'color' => '#ef4444',
            ],
            'Insurance' => [
                'description' => 'Health, auto, home, and life insurance premiums',
                'color' => '#64748b',
            ],
            'Entertainment' => [
                'description' => 'Movies, concerts, games, and hobbies',
                'color' => '#a855f7',
            ],
            'Shopping' => [
                'description' => 'Clothing, electronics, and household items',
                'color' => '#ec4899',
            ],
            'Travel' => [
                'description' => 'Flights, hotels, and vacation spending',
                'color' => '#f59e0b',
            ],
            'Subscriptions' => [
                'description' => 'Streaming services, apps, and memberships',
                'color' => '#6366f1',
            ],
            'Education' => [
                'description' => 'Tuition, courses, and books',
                'color' => '#14b8a6',
            ],
            'Savings & Investments' => [
                'description' => 'Transfers to savings, retirement, and investments',
                'color' => '#10b981',
            ],
        ];
    }

    /**
     * Create the canonical categories for a user.
     */
    public static function categoriesFor(User $user): void
    {
        foreach (self::categories() as $name => $attributes) {
            Category::firstOrCreate(
                ['user_id' => $user->id, 'name' => $name],
                $attributes,
            );
        }
    }

    /**
     * Seed the categories for every existing user.
     */
    public function run(): void
    {
        User::query()->each(fn (User $user) => self::categoriesFor($user));
    }
}
