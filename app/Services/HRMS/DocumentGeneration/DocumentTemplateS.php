<?php

namespace App\Services\HRMS\DocumentGeneration;

use App\Models\HRMS\DocumentGeneration\DocumentTemplate;
use Illuminate\Support\Facades\Auth;

class DocumentTemplateS
{
    public function createTemplate(array $data)
    {
        $data['created_by_user_id'] = Auth::id() ?? 1;
        $data['updated_by_user_id'] = Auth::id() ?? 1;
        
        $fields = $data['fields'] ?? [];
        unset($data['fields']);

        $template = DocumentTemplate::create($data);

        $this->syncFields($template, $fields);

        return $template;
    }

    public function updateTemplate(DocumentTemplate $template, array $data)
    {
        $data['updated_by_user_id'] = Auth::id() ?? 1;
        
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
