<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowTemplate extends Model
{
    use HasFactory;

    protected $table = 'workflow_templates';

    protected $fillable = [
        'name',
        'motivos',
    ];

    protected $casts = [
        'motivos' => 'array',
    ];

    /**
     * 🔗 Etapas vinculadas a este template
     */
    public function workflows()
    {
        return $this->hasMany(ProcessWorkflow::class, 'workflow_template_id')
                    ->orderBy('step_order', 'asc');
    }

    /**
     * 🔗 Motivos normalizados vinculados a este template
     */
    public function reasons()
    {
        return $this->hasMany(WorkflowReason::class, 'workflow_template_id');
    }

    /**
     * 🔗 Execuções ativas com base neste template
     */
    public function executions()
    {
        return $this->hasMany(ProcessExecution::class, 'workflow_template_id');
    }

    /**
     * 🔍 Busca template pelo motivo (modo compatível com legado JSON)
     */
    public static function findByMotivo(string $motivo): ?self
    {
        return self::all()->first(function ($template) use ($motivo) {
            return in_array($motivo, $template->motivos ?? []);
        });
    }

    /**
     * 🚀 Carrega o template com todas as relações
     */
    public static function full($id)
    {
        return self::with(['workflows', 'reasons'])->find($id);
    }
}
