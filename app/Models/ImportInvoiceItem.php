<?php
namespace App\Models;

use App\Models\ImportInvoice;
use Illuminate\Database\Eloquent\Model;

class ImportInvoiceItem extends Model
{
    
     protected $fillable = [
        'import_invoice_id', 'book_no', 'shipper', 'account_head',
        'currency', 'currency_rate', 'gross_weight',
        'rate', 'amount', 'freight_rate', 'ref_no'
    ];

    public function invoice()
    {
        return $this->belongsTo(ImportInvoice::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'book_no', 'bookNo');
    }
}
