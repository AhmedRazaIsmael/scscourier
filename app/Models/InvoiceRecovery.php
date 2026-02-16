<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\ExportInvoice;
use App\Models\ImportInvoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceRecovery extends Model
{
    protected $fillable = [
        'invoice_ref_id',
        'invoice_type',        // 'import' or 'export'
        'recovery_person',
        'receiving_path',
        'recovery_amount',
        'remarks',
        'insert_by',
    ];

    // Export invoice relation
    public function exportInvoice()
    {
        return $this->belongsTo(ExportInvoice::class, 'invoice_ref_id');
    }

    // Import invoice relation
    public function importInvoice()
    {
        return $this->belongsTo(ImportInvoice::class, 'invoice_ref_id');
    }

    // Dynamic accessor
    public function getInvoiceAttribute()
    {
        return $this->invoice_type === 'import'
            ? $this->importInvoice
            : $this->exportInvoice;
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }
}
