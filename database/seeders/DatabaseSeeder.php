<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Responsibility;
use App\Models\Role;
use App\Models\Team;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // cara 1
        // Company::factory(10)->withTeam()->create();

        // cara 2
        // Company::factory(10)->create(function (array $attributes) {
        //     Team::factory()->create([
        //         'name' => $this->faker->jobTitle(),
        //         'icon' => $this->faker->imageUrl(),
        //         'company_id' => $attributes['id'],
        //     ]);
        //     return [];
        // });

        // Team::factory(5)->create();
        // Role::factory(50)->create();
        // Responsibility::factory(200)->create();
        // Employee::factory(1000)->create();
    }
}
