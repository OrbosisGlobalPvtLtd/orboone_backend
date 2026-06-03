<?php

namespace App\Services\HRMS\DocumentGeneration;

use Illuminate\Support\Facades\Log;
use ZipArchive;

class DocumentFieldDetectorS
{
    protected array $invalidPlaceholders = [];

    /**
     * Get invalid placeholders detected during the last scan.
     */
    public function getInvalidPlaceholders(): array
    {
        return $this->invalidPlaceholders;
    }

    /**
     * Detect placeholders from a DOCX template.
     *
     * Supported format: {{placeholder_key}}
     */
    public function detectFromDocx(string $absoluteDocxPath): array
    {
        $this->invalidPlaceholders = [];

        if (!is_file($absoluteDocxPath)) {
            return [];
        }

        if (!class_exists(ZipArchive::class)) {
            Log::warning('DocumentFieldDetectorS: ZipArchive extension is missing.');
            return [];
        }

        $zip = new ZipArchive();
        if ($zip->open($absoluteDocxPath) !== true) {
            return [];
        }

        $xmlBuffer = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (!is_string($entry) || !str_starts_with($entry, 'word/') || !str_ends_with($entry, '.xml')) {
                continue;
            }

            $content = $zip->getFromIndex($i);
            if (!is_string($content) || $content === '') {
                continue;
            }

            // Strip XML tags to rebuild full text sequences across runs.
            $xmlBuffer .= ' ' . preg_replace('/<[^>]+>/', '', $content);
        }

        $zip->close();

        if ($xmlBuffer === '') {
            return [];
        }

        // 1. Scan for all valid placeholders using exact requested regex: /\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/
        preg_match_all('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', $xmlBuffer, $validMatches);
        $validFields = array_values(array_unique($validMatches[1] ?? []));
        sort($validFields);

        // 2. Scan for any invalid / malformed placeholders:
        $invalid = [];
        $offset = 0;
        while (($pos = strpos($xmlBuffer, '{{', $offset)) !== false) {
            // Grab a chunk of 60 chars starting from "{{""
            $chunk = substr($xmlBuffer, $pos, 60);

            // If it starts with a valid placeholder, skip past it
            if (preg_match('/^\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', $chunk, $m)) {
                $offset = $pos + strlen($m[0]);
                continue;
            }

            // Otherwise, it is malformed! Let's extract up to the end of the malformed placeholder.
            // A malformed placeholder can have trailing characters like "||" or end early.
            if (preg_match('/^(\{\{\s*[a-zA-Z0-9_.]+(?:\|\||\})?)/', $chunk, $m)) {
                $malformed = $m[1];
                if (!in_array($malformed, $invalid)) {
                    $invalid[] = $malformed;
                }
                $offset = $pos + strlen($malformed);
            } else {
                if (preg_match('/^(\{\{[^\s]*)/', $chunk, $m)) {
                    $malformed = $m[1];
                    if (!in_array($malformed, $invalid)) {
                        $invalid[] = $malformed;
                    }
                    $offset = $pos + strlen($malformed);
                } else {
                    $offset = $pos + 2;
                }
            }
        }

        $this->invalidPlaceholders = $invalid;

        return $validFields;
    }

    /**
     * Compare detected placeholders with the document_placeholders master table
     * to identify auto-mapped, unknown, and missing required placeholders.
     */
    public function analyzePlaceholders(string $documentType, array $detected): array
    {
        $masterPlaceholders = \Illuminate\Support\Facades\DB::table('document_placeholders')
            ->where('is_active', true)
            ->get()
            ->keyBy('placeholder_key');

        $requiredIds = \Illuminate\Support\Facades\DB::table('document_type_placeholders')
            ->where('document_type', $documentType)
            ->where('is_required', true)
            ->pluck('placeholder_id')
            ->toArray();

        $requiredPlaceholders = \Illuminate\Support\Facades\DB::table('document_placeholders')
            ->whereIn('id', $requiredIds)
            ->pluck('placeholder_key')
            ->toArray();

        $unknown = [];
        $autoMapped = [];
        foreach ($detected as $field) {
            if ($masterPlaceholders->has($field)) {
                $autoMapped[] = $field;
            } else {
                $unknown[] = $field;
            }
        }

        $missing = [];
        foreach ($requiredPlaceholders as $reqKey) {
            if (!in_array($reqKey, $detected)) {
                $missing[] = $reqKey;
            }
        }

        $mapping = [];
        foreach ($detected as $field) {
            if ($masterPlaceholders->has($field)) {
                $m = $masterPlaceholders->get($field);
                $mapping[$field] = [
                    'placeholder_key' => $field,
                    'label' => $m->label,
                    'group_name' => $m->group_name,
                    'source_type' => $m->source_type,
                    'is_required' => in_array($field, $requiredPlaceholders),
                    'status' => 'mapped'
                ];
            } else {
                $mapping[$field] = [
                    'placeholder_key' => $field,
                    'label' => ucwords(str_replace('_', ' ', $field)),
                    'group_name' => 'Custom Block',
                    'source_type' => 'manual',
                    'is_required' => false,
                    'status' => 'unknown'
                ];
            }
        }

        foreach ($missing as $field) {
            if ($masterPlaceholders->has($field)) {
                $m = $masterPlaceholders->get($field);
                $mapping[$field] = [
                    'placeholder_key' => $field,
                    'label' => $m->label,
                    'group_name' => $m->group_name,
                    'source_type' => $m->source_type,
                    'is_required' => true,
                    'status' => 'missing'
                ];
            }
        }

        return [
            'detected' => $detected,
            'auto_mapped' => $autoMapped,
            'unknown' => $unknown,
            'missing_required' => $missing,
            'placeholder_mapping' => $mapping
        ];
    }
}

