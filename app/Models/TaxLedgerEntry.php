<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxLedgerEntry extends Model
{
    use HasFactory;

    protected $table = 'tax_ledger_entries';

    protected $guarded = ['id'];

    protected $casts = [
        'transaction_date'         => 'date',
        'pvp_gross_amount'         => 'decimal:2',
        'taxable_base'             => 'decimal:2',
        'iva_transferred_16'       => 'decimal:2',
        'iva_creditable_16'        => 'decimal:2',
        'platform_retention_isr_1' => 'decimal:2',
        'platform_retention_iva_8' => 'decimal:2',
        'net_iva_payable'          => 'decimal:2',
    ];
}
