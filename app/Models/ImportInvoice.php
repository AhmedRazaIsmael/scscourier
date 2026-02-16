<?php
namespace App\Models;

use App\Models\Customer;
use App\Models\InvoiceRecovery;
use App\Models\ImportInvoiceItem;
use Illuminate\Database\Eloquent\Model;

class ImportInvoice extends Model
{
    protected $fillable = [
        'invoice_no', 'customer_id', 'invoice_date',
        'pay_due_date', 'pay_mode', 'remarks', 'ref_no'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'pay_due_date' => 'date',
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(ImportInvoiceItem::class);
    }
    public function recoveries()
    {
        return $this->hasMany(InvoiceRecovery::class, 'invoice_id')->where('invoice_type', 'import');
    }
}
