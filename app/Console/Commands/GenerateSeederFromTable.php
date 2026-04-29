<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateSeederFromTable extends Command
{
    protected $signature = 'db:seed-from-table {table} {--class=}';
    protected $description = 'Generate a Laravel seeder from existing table data';

    public function handle(): int
    {
        $table = $this->argument('table');
        $className = $this->option('class') ?: Str::studly(Str::singular($table)) . 'Seeder';

        try {
            $rows = DB::table($table)->get()->map(function ($row) {
                return (array) $row;
            })->toArray();

            if (empty($rows)) {
                $this->warn("Table '{$table}' is empty.");
                return self::SUCCESS;
            }

            $exportRows = array_map(function ($row) {
                foreach ($row as $key => $value) {
                    if ($value instanceof \DateTimeInterface) {
                        $row[$key] = $value->format('Y-m-d H:i:s');
                    }
                }
                return $row;
            }, $rows);

            $dataString = var_export($exportRows, true);

            $content = <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class {$className} extends Seeder
{
    public function run(): void
    {
        DB::table('{$table}')->insert({$dataString});
    }
}

PHP;

            $path = database_path("seeders/{$className}.php");
            File::put($path, $content);

            $this->info("Seeder created successfully: {$path}");
            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}