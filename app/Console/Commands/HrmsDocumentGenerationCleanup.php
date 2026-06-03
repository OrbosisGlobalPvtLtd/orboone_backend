<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HRMS\DocumentGeneration\GeneratedDocument;
use App\Models\HRMS\DocumentGeneration\GeneratedDocumentLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class HrmsDocumentGenerationCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrms:document-generation-cleanup 
                            {--dry-run : Only show the counts of records that would be deleted} 
                            {--force : Force delete the selected records without interactive confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up test, legacy, and dummy generated document records from database and storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if (!$dryRun && !$force) {
            if (!$this->confirm('Are you sure you want to proceed? Use --dry-run first to review. Proceed anyway?')) {
                $this->info('Cleanup cancelled.');
                return 0;
            }
        }

        $this->info('Scanning database for generated document test records...');

        // Build the query to find target test/dummy records
        $query = GeneratedDocument::query();

        // 1. Records with template_type = docx (Legacy test data)
        // 2. Records containing "test" or "dummy" in titles or candidate names
        // 3. Records with candidate name = "Candidate"
        // 4. Records where document_number starts with "test", "tst", "doc-retry"
        // 5. Records where pdf_status is "libreoffice_missing"
        $query->where(function ($q) {
            $q->where('template_type', 'docx')
              ->orWhere('document_title', 'like', '%test%')
              ->orWhere('document_title', 'like', '%dummy%')
              ->orWhere('candidate_name', 'like', '%test%')
              ->orWhere('candidate_name', 'like', '%dummy%')
              ->orWhere('candidate_name', 'Candidate')
              ->orWhere('document_number', 'like', 'TST%')
              ->orWhere('document_number', 'like', 'TEST%')
              ->orWhere('document_number', 'like', 'DOC-RETRY%')
              ->orWhere('pdf_status', 'libreoffice_missing');
        });

        // Let's filter out any records that are linked to actual employees (unless it's test data)
        // A record is real if it has an employee_id and the candidate name doesn't contain "test" or "dummy".
        // To be safe, we only target test records.
        $records = $query->withTrashed()->get();
        $count = $records->count();

        if ($count === 0) {
            $this->info('No test, legacy or dummy records found to clean up.');
            return 0;
        }

        $this->info("Found {$count} matching test/dummy generated document records:");
        foreach ($records as $record) {
            $this->line("- [ID: {$record->id}] No: {$record->document_number} | Title: {$record->document_title} | Candidate: {$record->candidate_name} | Type: {$record->template_type}");
        }

        if ($dryRun) {
            $this->info("\n[DRY RUN] Would delete {$count} database records and their associated PDF/DOCX files from storage.");
            return 0;
        }

        $this->info("\nDeleting associated files from storage...");
        $deletedFilesCount = 0;

        foreach ($records as $record) {
            // Delete DOCX file if exists
            if ($record->generated_docx_path && Storage::disk('private')->exists($record->generated_docx_path)) {
                Storage::disk('private')->delete($record->generated_docx_path);
                $deletedFilesCount++;
            }
            // Delete PDF file if exists
            if ($record->generated_pdf_path && Storage::disk('private')->exists($record->generated_pdf_path)) {
                Storage::disk('private')->delete($record->generated_pdf_path);
                $deletedFilesCount++;
            }
            if ($record->pdf_path && Storage::disk('private')->exists($record->pdf_path)) {
                Storage::disk('private')->delete($record->pdf_path);
                $deletedFilesCount++;
            }

            // Force delete related logs
            GeneratedDocumentLog::where('generated_document_id', $record->id)->delete();
            
            // Force delete the record
            $record->forceDelete();
        }

        $this->info("Successfully deleted {$count} database records and {$deletedFilesCount} files from private storage.");
        return 0;
    }
}
