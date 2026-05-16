<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeekoffRuleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $rules = [
            [1, null, 1, 0], [2, null, 1, 0], [3, null, 1, 0], [4, null, 1, 0], [5, null, 1, 0],
            [6, 1, 1, 0], [6, 2, 0, 1], [6, 3, 1, 0], [6, 4, 0, 1], [6, 5, 0, 1],
            [7, null, 0, 1],
        ];

        foreach ($rules as [$weekday, $weekNumber, $isWorking, $isOff]) {
            DB::table('weekoff_rules')->updateOrInsert(
                ['weekday' => $weekday, 'week_number' => $weekNumber],
                $this->columns('weekoff_rules', [
                    'is_working' => $isWorking,
                    'is_off' => $isOff,
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ])
            );
        }
    }

    private function columns(string $table, array $data): array
    {
        return collect($data)->filter(fn ($value, $column) => Schema::hasColumn($table, $column))->all();
    }
}
