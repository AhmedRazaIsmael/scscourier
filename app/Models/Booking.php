<?php

namespace App\Models;

use App\Models\City;
use App\Models\User;
use App\Models\Partner;
use App\Models\Customer;
use App\Models\ShipmentCost;
use App\Models\BookingStatus;
use App\Models\ExportInvoice;
use App\Models\ImportInvoice;
use App\Models\InvoiceRecovery;
use App\Models\ExportInvoiceItem;
use App\Models\ThirdPartyBooking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'bookNo', 
        'bookDate', 
        'customer_id', 
        'bookingType', 
        'service', 
        'bookChannel', 
        'paymentMode',
        'origin', 
        'originCountry', 
        'destination', 
        'destinationCountry', 
        'postalCode', 
        'invoiceValue',
        'weight', 
        'pieces', 
        'length', 
        'width', 
        'height', 
        'dimensionalWeight', 
        'orderNo', 
        'arrivalClearance',
        'itemContent', 
        'itemDetail', 
        'shipperCompany', 
        'shipperName', 
        'shipperNumber', 
        'shipperEmail',
        'shipperAddress', 
        'consigneeCompany', 
        'consigneeName', 
        'consigneeNumber', 
        'consigneeEmail',
        'consigneeAddress', 
        'remarks', 
        'pickupInstructions', 
        'deliveryInstructions', 
        'codAmount',
        'territory', 
        'salesPerson', 
        'rateType',
        'was_edited',
        'partner_id',
        'assigned_at',
        'email_to',
        'email_cc'

    ];

    public function salesPersonUser()
    {
        return $this->belongsTo(User::class, 'salesPerson');
    }
    
    public function territoryUser()
    {
        return $this->belongsTo(User::class, 'territory');
    }
    public function statuses()
    {
        return $this->hasMany(BookingStatus::class, 'booking_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function latestStatus()
{
    return $this->hasOne(BookingStatus::class, 'booking_id', 'id')->latestOfMany();
}


    /**
 * Get all statuses for this booking (by ID or bookNo)
 */
public function allStatusesMixed()
{
    return BookingStatus::where(function($q){
        $q->where('booking_id', $this->id)
          ->orWhere('booking_id', $this->bookNo);
    })->orderByDesc('created_at')->get();
}

public function latestStatusMixed()
{
    return BookingStatus::where(function($q){
        $q->where('booking_id', $this->id)
          ->orWhere('booking_id', $this->bookNo);
    })->latest('created_at')->first();
}
    public function attachments()
    {
        return $this->hasMany(BookingAttachment::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function exportInvoiceItems()
    {
        return $this->hasMany(ExportInvoiceItem::class, 'book_no', 'bookNo');
    }
    
    public function importInvoiceItems()
    {
        return $this->hasMany(ImportInvoiceItem::class, 'book_no', 'bookNo');
    }
    
    public function exportInvoices()
    {
        return $this->hasManyThrough(
            ExportInvoice::class,
            ExportInvoiceItem::class,
            'book_no', // FK on ExportInvoiceItem
            'id',      // PK on ExportInvoice
            'bookNo',  // Local key on Booking
            'export_invoice_id' // Local key on ExportInvoiceItem
        );
    }
    
    public function importInvoices()
    {
        return $this->hasManyThrough(
            ImportInvoice::class,
            ImportInvoiceItem::class,
            'book_no',
            'id',
            'bookNo',
            'import_invoice_id'
        );
    }

    public function shipmentCosts()
    {
        return $this->hasMany(ShipmentCost::class, 'trackNo', 'bookNo');
    }

    public function invoiceRecoveries()
    {
        return $this->hasMany(InvoiceRecovery::class, 'invoice_ref_id', 'bookNo');
    }

    public function destinationHub()
    {
        return $this->belongsTo(City::class, 'destination', 'id'); 
        // assuming 'destination' stores city ID and City has 'id' as PK
    }
    public function bookingStatuses()
    {
        return $this->hasMany(BookingStatus::class, 'booking_id', 'bookNo');
    }

    public function thirdparty()
    {
        return $this->hasOne(ThirdPartyBooking::class, 'book_no', 'bookNo');
    }

}
