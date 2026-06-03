<?php

namespace App\Services\HRMS\DocumentGeneration;

use App\Models\HRMS\DocumentGeneration\DocumentTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DocumentTemplateS
{
    public function createTemplate(array $data)
    {
        $data['created_by_user_id'] = Auth::id() ?? 1;
        $data['updated_by_user_id'] = Auth::id() ?? 1;
        if (Schema::hasColumn('document_templates', 'template_type')) {
            $data['template_type'] = $data['template_type'] ?? 'html';
        } else {
            unset($data['template_type']);
        }
        if (Schema::hasColumn('document_templates', 'version')) {
            $data['version'] = $data['version'] ?? 'v1';
        } else {
            unset($data['version']);
        }
        if (Schema::hasColumn('document_templates', 'is_archived')) {
            $data['is_archived'] = !empty($data['is_archived']);
        } else {
            unset($data['is_archived']);
        }
        if (!Schema::hasColumn('document_templates', 'docx_file_path')) {
            unset($data['docx_file_path']);
        }
        if (!Schema::hasColumn('document_templates', 'detected_fields')) {
            unset($data['detected_fields']);
        }
        
        $fields = $data['fields'] ?? [];
        unset($data['fields']);

        $template = DocumentTemplate::create($data);

        $this->syncFields($template, $fields);

        return $template;
    }

    public function updateTemplate(DocumentTemplate $template, array $data)
    {
        $data['updated_by_user_id'] = Auth::id() ?? 1;
        if (Schema::hasColumn('document_templates', 'template_type')) {
            $data['template_type'] = $data['template_type'] ?? ($template->template_type ?: 'html');
        } else {
            unset($data['template_type']);
        }
        if (Schema::hasColumn('document_templates', 'version')) {
            $data['version'] = $data['version'] ?? ($template->version ?: 'v1');
        } else {
            unset($data['version']);
        }
        if (Schema::hasColumn('document_templates', 'is_archived')) {
            $data['is_archived'] = !empty($data['is_archived']);
        } else {
            unset($data['is_archived']);
        }
        if (!Schema::hasColumn('document_templates', 'docx_file_path')) {
            unset($data['docx_file_path']);
        }
        if (!Schema::hasColumn('document_templates', 'detected_fields')) {
            unset($data['detected_fields']);
        }
        
        $fields = $data['fields'] ?? [];
        unset($data['fields']);

        $template->update($data);

        $this->syncFields($template, $fields);

        return $template;
    }

    private function syncFields(DocumentTemplate $template, array $fields)
    {
        // Simple wipe and recreate for now
        $template->fields()->delete();

        foreach ($fields as $index => $fieldData) {
            $template->fields()->create([
                'field_key' => $fieldData['field_key'],
                'field_label' => $fieldData['field_label'],
                'field_type' => $fieldData['field_type'] ?? 'text',
                'is_required' => $fieldData['is_required'] ?? false,
                'sort_order' => $index,
            ]);
        }
    }

    public function deleteTemplate(DocumentTemplate $template)
    {
        return $template->delete();
    }
}
