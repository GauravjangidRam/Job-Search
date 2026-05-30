<?php

namespace Database\Seeders;

use App\Models\CareerInsight;
use Illuminate\Database\Seeder;

class CareerInsightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insights = [
            // Salary data (at least 4 records)
            ['type' => 'salary', 'label' => 'Software Engineer', 'value' => '120000', 'sort_order' => 1],
            ['type' => 'salary', 'label' => 'Product Manager', 'value' => '135000', 'sort_order' => 2],
            ['type' => 'salary', 'label' => 'Data Scientist', 'value' => '145000', 'sort_order' => 3],
            ['type' => 'salary', 'label' => 'UX Designer', 'value' => '105000', 'sort_order' => 4],
            ['type' => 'salary', 'label' => 'DevOps Engineer', 'value' => '140000', 'sort_order' => 5],

            // Hiring trend data (at least 6 records)
            ['type' => 'trend', 'label' => 'January', 'value' => '1200', 'sort_order' => 1],
            ['type' => 'trend', 'label' => 'February', 'value' => '1350', 'sort_order' => 2],
            ['type' => 'trend', 'label' => 'March', 'value' => '1500', 'sort_order' => 3],
            ['type' => 'trend', 'label' => 'April', 'value' => '1420', 'sort_order' => 4],
            ['type' => 'trend', 'label' => 'May', 'value' => '1600', 'sort_order' => 5],
            ['type' => 'trend', 'label' => 'June', 'value' => '1750', 'sort_order' => 6],

            // In-demand skills data (at least 5 records)
            ['type' => 'skill', 'label' => 'JavaScript', 'value' => '85', 'sort_order' => 1],
            ['type' => 'skill', 'label' => 'Python', 'value' => '78', 'sort_order' => 2],
            ['type' => 'skill', 'label' => 'React', 'value' => '72', 'sort_order' => 3],
            ['type' => 'skill', 'label' => 'AWS', 'value' => '68', 'sort_order' => 4],
            ['type' => 'skill', 'label' => 'SQL', 'value' => '65', 'sort_order' => 5],
        ];

        foreach ($insights as $insight) {
            CareerInsight::create($insight);
        }
    }
}
