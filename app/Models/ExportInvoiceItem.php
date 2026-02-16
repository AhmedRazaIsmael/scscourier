<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\ExportInvoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExportInvoiceItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'export_invoice_id', 'book_no', 'consignee', 'account_head',
        'currency', 'currency_rate', 'gross_weight',
        'rate', 'amount', 'freight_rate', 'ref_no'
    ];
    public function invoice()
    {
        return $this->belongsTo(ExportInvoice::class, 'export_invoice_id');
    }

     // <-- Add this
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'book_no', 'bookNo');
    }

    
}