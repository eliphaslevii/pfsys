<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nfe extends Model
{
    protected $table = 'nfes';
    protected $fillable = [
        'documento_xml_id',
        'chave',
        'numero',
        'serie',
        'natureza_operacao',
        'modelo',
        'tipo_operacao',
        'destino_operacao',

        'emitente_cnpj',
        'emitente_nome',
        'emitente_ie',
        'emitente_endereco',
        'emitente_municipio',
        'emitente_uf',
        'emitente_cep',

        'dest_cnpj',
        'dest_nome',
        'dest_ie',
        'dest_endereco',
        'dest_municipio',
        'dest_uf',
        'dest_cep',

        'valor_total',
        'valor_produtos',
        'valor_frete',
        'valor_seguro',
        'valor_desconto',
        'valor_tributos',

        'transportadora_cnpj',
        'mod_frete',
        'transportadora_nome',
        'volume_quantidade',
        'volume_especie',
        'peso_bruto',
        'peso_liquido',

        'fatura_numero',
        'fatura_valor',
        'data_vencimento',

        'informacoes_adicionais',
        'emitente_iest',
        'emitente_im',
        'crt',
        'protocolo_autorizacao',
        'data_autorizacao',
        'status_autorizacao',
        'motivo_autorizacao',
    ];
    protected $casts = [
        'data_emissao' => 'datetime',
        'data_vencimento' => 'date',
    ];
    public function tracking()
    {
        return $this->hasMany(\App\Models\NfeTracking::class);
    }

    public function lastTracking()
    {
        return $this->hasOne(\App\Models\NfeTracking::class)->latestOfMany();
    }
    public function trackingState()
    {
        return $this->hasOne(\App\Models\NfeTrackingState::class, 'nfe_id');
    }

    public function agendamentos()
    {
        return $this->belongsToMany(AgendamentoLogistica::class, 'agendamento_nfe', 'nfe_id', 'agendamento_id');
    }
    public function itens()
    {
        return $this->hasMany(\App\Models\NfeItem::class, 'nfe_id');
    }

    public function ibscbsTot()
    {
        return $this->hasOne(\App\Models\NfeIbscbsTot::class, 'nfe_id');
    }
}
