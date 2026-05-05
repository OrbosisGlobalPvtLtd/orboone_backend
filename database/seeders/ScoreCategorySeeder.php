<?php

namespace Database\Seeders;

use App\Models\HRMS\Employee\ScoreCategoryM as ScoreCategory;
use Illuminate\Database\Seeder;

class ScoreCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $scoreCategories = ["Discipline", "Hardworking"];

        foreach($scoreCategories as $category) 
        {
            ScoreCategory::factory()->create(['name' => $category]);
        }
    }
}
