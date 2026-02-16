<?php
namespace App\Models;

use App\Models\Customer;
use App\Models\InvoiceRecovery;
use App\Models\ExportInvoiceItem;
use Illuminate\Database\Eloquent\Model;

class ExportInvoice extends Model
{
    protected $fillable = [
        'invoice_no', 'invoice_date', 'pay_due_date',
        'pay_mode', 'remarks', 'customer_id', 'ref_no'
    ];

    public function items()
    {
        return $this->hasMany(ExportInvoiceItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function recoveries()
    {
        return $this->hasMany(InvoiceRecovery::class, 'invoice_id')->where('invoice_type', 'export');
    }
 

}
