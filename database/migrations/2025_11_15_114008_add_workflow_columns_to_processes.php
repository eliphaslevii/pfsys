<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('processes', function (Blueprint $table) {

            // Template do workflow (definido pelo motivo escolhido)
            $table->foreignId('workflow_template_id')
                ->nullable()
                ->constrained('workflow_templates')
                ->onDelete('set null')
                ->after('process_type_id');

            // Motivo que define o template
            $table->foreignId('workflow_reason_id')
                ->nullable()
                ->constrained('workflow_reasons')
                ->onDelete('set null')
                ->after('workflow_template_id');

            // Etapa atual do fluxo
            $table->foreignId('current_workflow_step_id')
                ->nullable()
                ->constrained('workflow_steps')
                ->onDelete('set null')
                ->after('workflow_reason_id');
        });

        Schema::table('processes', function (Blueprint $table) {
            if (!Schema::hasColumn('processes', 'workflow_template_id')) {
                $table->foreignId('workflow_template_id')->nullable()->after('process_type_id')->constrained('workflow_templates')->onDelete('set null');
            }
            if (!Schema::hasColumn('processes', 'workflow_reason_id')) {
                $table->foreignId('workflow_reason_id')->nullable()->after('workflow_template_id')->constrained('workflow_reasons')->onDelete('set null');
            }
            if (!Schema::hasColumn('processes', 'current_workflow_step_id')) {
                $table->foreignId('current_workflow_step_id')->nullable()->after('workflow_reason_id')->constrained('workflow_steps')->onDelete('set null');
            }
        });

    }


    public function down(): void
    {
        Schema::table('processes', function (Blueprint $table) {

            if (Schema::hasColumn('processes', 'current_workflow_step_id')) {
                $table->dropForeign(['current_workflow_step_id']);
                $table->dropColumn('current_workflow_step_id');
            }

            if (Schema::hasColumn('processes', 'workflow_reason_id')) {
                $table->dropForeign(['workflow_reason_id']);
                $table->dropColumn('workflow_reason_id');
            }

            if (Schema::hasColumn('processes', 'workflow_template_id')) {
                $table->dropForeign(['workflow_template_id']);
                $table->dropColumn('workflow_template_id');
            }
        });
    }
};
