<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_exit_processes')) {
            Schema::create('employee_exit_processes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id')->index();
                $table->string('exit_type', 50)->default('resignation');
                $table->date('resignation_date')->nullable();
                $table->date('termination_date')->nullable();
                $table->date('exit_initiated_date')->nullable();
                $table->date('last_working_day')->nullable();
                $table->date('last_working_date')->nullable();
                $table->unsignedInteger('notice_period_days')->nullable();
                $table->boolean('notice_waived')->default(false);
                $table->boolean('immediate_exit')->default(false);
                $table->boolean('buyout_recovery')->default(false);
                $table->date('fnf_due_date')->nullable();
                $table->text('reason')->nullable();
                $table->string('status', 60)->default('exit_initiated')->index();
                $table->string('asset_status', 30)->default('pending');
                $table->string('fnf_status', 30)->default('pending');
                $table->string('document_status', 30)->default('pending');
                $table->string('handover_status', 30)->default('pending');
                $table->string('asset_handover_status', 30)->default('pending');
                $table->string('experience_letter_status', 30)->default('pending');
                $table->string('relieving_letter_status', 30)->default('pending');
                $table->string('final_status', 40)->default('pending');
                $table->unsignedBigInteger('initiated_by_user_id')->nullable();
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->unsignedBigInteger('completed_by_user_id')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('employee_exit_processes', function (Blueprint $table) {
                $this->addIfMissing($table, 'string', 'exit_type', ['length' => 50, 'default' => 'resignation']);
                $this->addIfMissing($table, 'date', 'termination_date');
                $this->addIfMissing($table, 'date', 'exit_initiated_date');
                $this->addIfMissing($table, 'date', 'last_working_day');
                $this->addIfMissing($table, 'date', 'last_working_date');
                $this->addIfMissing($table, 'unsignedInteger', 'notice_period_days');
                $this->addIfMissing($table, 'boolean', 'notice_waived', ['default' => 0]);
                $this->addIfMissing($table, 'boolean', 'immediate_exit', ['default' => 0]);
                $this->addIfMissing($table, 'boolean', 'buyout_recovery', ['default' => 0]);
                $this->addIfMissing($table, 'date', 'fnf_due_date');
                $this->addIfMissing($table, 'string', 'status', ['length' => 60, 'default' => 'exit_initiated']);
                $this->addIfMissing($table, 'string', 'asset_status', ['length' => 30, 'default' => 'pending']);
                $this->addIfMissing($table, 'string', 'document_status', ['length' => 30, 'default' => 'pending']);
                $this->addIfMissing($table, 'string', 'handover_status', ['length' => 30, 'default' => 'pending']);
                $this->addIfMissing($table, 'unsignedBigInteger', 'approved_by_user_id');
                $this->addIfMissing($table, 'text', 'remarks');
            });
        }

        if (Schema::hasTable('asset_allocations')) {
            Schema::table('asset_allocations', function (Blueprint $table) {
                $this->addIfMissing($table, 'date', 'returned_date');
                $this->addIfMissing($table, 'unsignedBigInteger', 'received_by_user_id');
                $this->addIfMissing($table, 'string', 'return_condition', ['length' => 120]);
                $this->addIfMissing($table, 'text', 'return_remarks');
            });
        }

        if (Schema::hasTable('permissions')) {
            $now = now();
            $permissions = [
                ['employee_exit.view', 'View employee exits'],
                ['employee_exit.initiate', 'Initiate employee exits'],
                ['employee_exit.update', 'Update employee exits'],
                ['employee_exit.asset_clearance', 'Update exit asset clearance'],
                ['employee_exit.fnf_process', 'Process exit FNF'],
                ['employee_exit.document_generate', 'Generate exit documents'],
                ['employee_exit.complete', 'Complete employee exits'],
                ['employee_exit.cancel', 'Cancel employee exits'],
            ];

            foreach ($permissions as [$key, $description]) {
                DB::table('permissions')->updateOrInsert(
                    ['key' => $key],
                    [
                        'module' => 'hrms',
                        'submodule' => 'employee_exit',
                        'action' => 'manage',
                        'description' => $description,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
    }

    private function addIfMissing(Blueprint $table, string $type, string $column, array $options = []): void
    {
        if (Schema::hasColumn($table->getTable(), $column)) {
            return;
        }

        $definition = $options['length'] ?? null ? $table->{$type}($column, $options['length']) : $table->{$type}($column);
        if (array_key_exists('default', $options)) {
            $definition->default($options['default']);
        }
        $definition->nullable();
    }
};
