<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDocumentManagementProfileCompletionColumns extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('document_types')) {
            Schema::table('document_types', function (Blueprint $table) {
                if (! Schema::hasColumn('document_types', 'code')) {
                    $table->string('code')->nullable()->unique()->after('name');
                }

                if (! Schema::hasColumn('document_types', 'applies_to')) {
                    $table->string('applies_to', 50)->default('all')->after('scope');
                }

                if (! Schema::hasColumn('document_types', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('has_expiry');
                }
            });
        }

        if (Schema::hasTable('employee_documents_new')) {
            $this->dropForeignIfExists(
                'employee_documents_new',
                'employee_documents_new_category_id_foreign'
            );

            Schema::table('employee_documents_new', function (Blueprint $table) {
                if (! Schema::hasColumn('employee_documents_new', 'uploaded_by_user_id')) {
                    $table->unsignedBigInteger('uploaded_by_user_id')->nullable()->after('category_id');
                }

                if (! Schema::hasColumn('employee_documents_new', 'verified_at')) {
                    $table->dateTime('verified_at')->nullable()->after('verified_by_user_id');
                }

                if (! Schema::hasColumn('employee_documents_new', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('verified_at');
                }

                if (! Schema::hasColumn('employee_documents_new', 'expiry_date')) {
                    $table->date('expiry_date')->nullable()->after('rejection_reason');
                }

                if (! Schema::hasColumn('employee_documents_new', 'file_original_name')) {
                    $table->string('file_original_name')->nullable()->after('file_path');
                }

                if (! Schema::hasColumn('employee_documents_new', 'file_mime_type')) {
                    $table->string('file_mime_type', 150)->nullable()->after('file_original_name');
                }

                if (! Schema::hasColumn('employee_documents_new', 'file_size')) {
                    $table->unsignedBigInteger('file_size')->nullable()->after('file_mime_type');
                }

                if (! Schema::hasColumn('employee_documents_new', 'is_required')) {
                    $table->boolean('is_required')->default(false)->after('expiry_date');
                }
            });

            if (
                Schema::hasTable('document_types') &&
                Schema::hasColumn('employee_documents_new', 'category_id')
            ) {
                $this->addForeignIfMissing(
                    'employee_documents_new',
                    'employee_documents_new_category_id_foreign',
                    'category_id',
                    'document_types',
                    'id'
                );
            }
        }

        if (Schema::hasTable('employees_new')) {
            Schema::table('employees_new', function (Blueprint $table) {
                if (! Schema::hasColumn('employees_new', 'experience_type')) {
                    $table->string('experience_type', 30)->nullable()->after('employment_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employee_documents_new')) {
            $this->dropForeignIfExists(
                'employee_documents_new',
                'employee_documents_new_category_id_foreign'
            );
        }

        if (Schema::hasTable('employees_new') && Schema::hasColumn('employees_new', 'experience_type')) {
            Schema::table('employees_new', function (Blueprint $table) {
                $table->dropColumn('experience_type');
            });
        }

        if (Schema::hasTable('employee_documents_new')) {
            Schema::table('employee_documents_new', function (Blueprint $table) {
                foreach (
                    [
                        'uploaded_by_user_id',
                        'verified_at',
                        'rejection_reason',
                        'expiry_date',
                        'file_original_name',
                        'file_mime_type',
                        'file_size',
                        'is_required',
                    ] as $column
                ) {
                    if (Schema::hasColumn('employee_documents_new', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('document_types')) {
            Schema::table('document_types', function (Blueprint $table) {
                foreach (['code', 'applies_to', 'is_active'] as $column) {
                    if (Schema::hasColumn('document_types', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function addForeignIfMissing(
        string $tableName,
        string $constraintName,
        string $columnName,
        string $referencesTable,
        string $referencesColumn
    ): void {
        if (DB::getDriverName() === 'mysql') {
            $exists = DB::selectOne(
                'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                 WHERE TABLE_SCHEMA = DATABASE() 
                 AND TABLE_NAME = ? 
                 AND CONSTRAINT_NAME = ?',
                [$tableName, $constraintName]
            );

            if ($exists) {
                return;
            }
        }

        try {
            Schema::table($tableName, function (Blueprint $table) use (
                $columnName,
                $referencesTable,
                $referencesColumn
            ) {
                $table->foreign($columnName)
                    ->references($referencesColumn)
                    ->on($referencesTable)
                    ->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // Keep migration safe if FK already exists with another name.
        }
    }

    private function dropForeignIfExists(string $tableName, string $constraintName): void
    {
        if (DB::getDriverName() !== 'mysql') {
            try {
                Schema::table($tableName, function (Blueprint $table) use ($constraintName) {
                    $table->dropForeign($constraintName);
                });
            } catch (\Throwable $e) {
            }

            return;
        }

        $exists = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = ? 
             AND CONSTRAINT_NAME = ?',
            [$tableName, $constraintName]
        );

        if (! $exists) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($constraintName) {
            $table->dropForeign($constraintName);
        });
    }
}
