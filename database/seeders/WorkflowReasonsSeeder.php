<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowReason;

class WorkflowReasonsSeeder extends Seeder
{
    public function run(): void
    {
        $templates = WorkflowTemplate::all();

        foreach ($templates as $template) {
            if (!empty($template->motivos)) {
                foreach ($template->motivos as $motivo) {
                    WorkflowReason::firstOrCreate([
                        'workflow_template_id' => $template->id,
                        'name' => trim($motivo),
                    ]);
                }
            }
        }

        echo "✅ Motivos importados para a tabela workflow_reasons!\n";
    }
}
