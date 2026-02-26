<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookingStatusController;
use App\Http\Controllers\ImportInvoiceController;
use App\Http\Controllers\InvoiceRecoveryController;
use App\Http\Controllers\DimensionalWeightController;
use App\Http\Controllers\ShopifyAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ----------------------
// Public / Login
// ----------------------
Route::view('/login', 'login');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/auth/shopify', [ShopifyAuthController::class, 'redirectToShopify']);
Route::get('/auth/shopify/callback', [ShopifyAuthController::class, 'handleCallback'])
    ->name('shopify.callback');


// ----------------------
// Authenticated Routes
// ----------------------
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [BookingController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [BookingController::class, 'dashboard'])->name('dashboard');

    // Static Views
    Route::view('/book-tracking', 'book-tracking');
    Route::view('/domestic-booking', 'domestic-booking');
    Route::view('/export-booking', 'export-booking');
    Route::view('/import-booking', 'import-booking');
    Route::view('/cross-border', 'crossborder-booking');
    Route::view('/single-label', 'single-label');
    Route::view('/bulk-label', 'bulk-label');
    Route::view('/pdo-bulk-label', 'pdo-bulk-label');
    Route::view('/pdo-single-label', 'pdo-single-label');
    Route::view('/sticker-single-label', 'pdo-single-label');
    Route::view('/undertaking-print', 'undertaking-print');
    Route::view('/3pl-booking', '3pl-booking');
    Route::view('/counter-partner', 'counter-partner');
    Route::view('/arrival-scan', 'arrival-scan');
    Route::view('/out-of-delivery-scan', 'out-of-delivery-scan');
    Route::view('/edit-dimensional-weight', 'edit-dimensional-weight');
    Route::view('/booking-status', 'booking-status');

    // Booking Routes
    Route::get('/search-data', [BookingController::class, 'searchData'])->name('search.data');
    Route::get('/bookings/download', [BookingController::class, 'downloadBookings'])->name('bookings.download');
    Route::get('/search-booking/aggregate', [BookingController::class, 'aggregate'])->name('booking.aggregate');
    Route::post('/search-booking/compute', [BookingController::class, 'compute'])->name('booking.compute');
    Route::get('/search-booking/row-filter', [BookingController::class, 'rowFilter'])->name('booking.rowFilter');
    Route::get('/search-booking/chart', [BookingController::class, 'chart'])->name('booking.chart');
    Route::get('/search-booking/download', [BookingController::class, 'download'])->name('booking.download');
    Route::post('/search-booking/set-columns', [BookingController::class, 'setColumns'])->name('booking.setColumns');
    Route::post('/new-booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/tracking', [BookingController::class, 'getBookingByBookNo'])->name('booking.track');
    Route::get('/tracking/sonic', [BookingController::class, 'trackSonic'])->name('booking.sonic.track');
    Route::get('/booking/tranzo-track', [BookingController::class, 'trackTranzo'])->name('booking.tranzo.track');
    Route::get('/booking-status', [BookingController::class, 'bookingStatus'])->name('booking.status');
    Route::get('/booking/sonic-status/{bookNo}', [BookingController::class, 'getSonicStatus'])->name('booking.sonic.status');
    Route::get('/domestic-booking', [BookingController::class, 'createDomestic'])->name('booking.domestic');
    Route::get('/export-booking', [BookingController::class, 'createExport'])->name('booking.export');
    Route::get('/import-booking', [BookingController::class, 'createImport'])->name('booking.import');
    Route::get('/cross-border', [BookingController::class, 'createCrossBorder'])->name('booking.crossborder');
    Route::get('/booking/preview/{id}', [BookingController::class, 'preview'])->name('bookings.preview');
    Route::get('/bookings/{id}/edit', [BookingController::class, 'edit'])->name('booking.edit');
    Route::put('/bookings/{id}/update', [BookingController::class, 'update'])->name('booking.update');
    Route::get('/bookings', [BookingController::class, 'index'])->name('booking.index');
    Route::get('/bookings/{id}/edit-type', [BookingController::class, 'edit'])->name('booking.type.edit.redirect');
    Route::get('/domestic-booking/{id}/edit', [BookingController::class, 'editDomestic'])->name('booking.edit.domestic');
    Route::get('/export-booking/{id}/edit', [BookingController::class, 'editExport'])->name('booking.edit.export');
    Route::get('/import-booking/{id}/edit', [BookingController::class, 'editImport'])->name('booking.edit.import');
    Route::get('/cross-border/{id}/edit', [BookingController::class, 'editCrossBorder'])->name('booking.edit.crossborder');
    Route::get('/booking/type/edit/redirect/{bookNo}', [BookingController::class, 'editByBookNo'])->name('booking.type.edit.redirect');
    Route::get('/bookings/void', [BookingController::class, 'voidedBookings'])->name('booking.void.list');
    Route::post('/booking/void-submit', [BookingController::class, 'sub mitVoid'])->name('booking.void.submit');
    Route::get('/void-bookings', [BookingController::class, 'voidBookingsView'])->name('void.bookings');
    Route::post('/void-bookings/reset/{id}', [BookingController::class, 'resetVoid'])->name('voidBookings.reset');

    Route::get('/booking-analysis', [BookingController::class, 'analysis'])->name('booking.analysis');
    Route::post('/booking-analysis', [BookingController::class, 'bookingAnalysisFilter'])->name('booking.analysis.filter');
    Route::get('/undertaking-print', [BookingController::class, 'undertakingForm'])->name('undertaking.form');
    Route::get('/undertaking-print/generate', [BookingController::class, 'printUndertaking'])->name('undertaking.generate');
    Route::get('/assigning-counter-partner', [BookingController::class, 'assigningCounterPartner'])->name('assigning.counter.partner');
    Route::post('/assign-counter-partner', [BookingController::class, 'assignCounterPartner'])->name('assign.counter.partner');
    Route::prefix('booking-status')->name('bookingStatus.')->group(function () {
        Route::post('/filter', [BookingStatusController::class, 'filter'])->name('filter');
        Route::post('/row-filter', [BookingStatusController::class, 'rowFilter'])->name('rowFilter');
        Route::post('/compute', [BookingStatusController::class, 'compute'])->name('compute');
        Route::post('/aggregate', [BookingStatusController::class, 'aggregate'])->name('aggregate');
        Route::get('/download', [BookingStatusController::class, 'download'])->name('download');
    });
    // Wizard Booking Flow
    Route::get('/wizard/bookings/upload', [BookingController::class, 'showStep1'])->name('wizard.bookings.step1');
    Route::post('/wizard/bookings/upload', [BookingController::class, 'handleStep1'])->name('wizard.bookings.step1.upload');
    Route::get('/wizard/bookings/map', [BookingController::class, 'showStep2'])->name('wizard.bookings.step2');
    Route::post('/wizard/bookings/validate', [BookingController::class, 'handleStep2'])->name('wizard.bookings.step2.validate');
    Route::get('/wizard/bookings/confirm', [BookingController::class, 'showStep3'])->name('wizard.bookings.step3');
    Route::post('/wizard/bookings/store', [BookingController::class, 'storeData'])->name('wizard.bookings.final');
    Route::post('/pending-shipments/download', [BookingController::class, 'downloadPending'])->name('pending.download');
    Route::post('/wizard/bookings/cancel', function () {
        session()->forget(['wizard_data', 'wizard_columns', 'wizard_mapping']);
        return redirect()->route('wizard.bookings.step1');
    })->name('wizard.bookings.cancel');
    Route::get('wizard/bookings/sample-csv', [BookingController::class, 'downloadSampleCsv'])
        ->name('wizard.bookings.sample');
    Route::get('/pending/download', [BookingController::class, 'Pendingdownload'])
        ->name('pending.download');
    // Booking Status Routes
    Route::get('/bookings/{id}/status', [BookingStatusController::class, 'edit'])->name('booking.status.edit');
    Route::post('/bookings/{id}/status', [BookingStatusController::class, 'update'])->name('booking.status.update');
    Route::get('/pending-shipments', [BookingController::class, 'pendingShipments'])->name('pending.shipments');
    Route::get('/booking-status/filter', [BookingStatusController::class, 'filterByDate'])->name('booking.status.filterByDate');
    Route::post('/booking-status/update-by-date', [BookingStatusController::class, 'updateByDate'])->name('booking.status.updateByDate');
    Route::get('/bulk-booking-status', [BookingStatusController::class, 'editBookingStatusView'])->name('booking.status.editBookingStatusView');
    Route::post('booking/status/update-selected', [BookingStatusController::class, 'updateSelected'])
        ->name('booking.status.updateSelected');

    // Invoicing Routes
    Route::post('/invoice/store-from-booking', [InvoiceController::class, 'storeFromBooking'])->name('invoice.storeFromBooking');
    Route::get('/invoicing/import', [InvoiceController::class, 'import'])->name('invoice.import');
    Route::get('/invoicing/export', [InvoiceController::class, 'export'])->name('invoice.export');
    Route::get('/invoicing/report', [InvoiceController::class, 'report'])->name('invoice.report');
    Route::get('/invoicing/import/{id}/print-pdf', [ImportInvoiceController::class, 'printPDF'])->name('invoice.import.print.pdf');
    Route::get('/customer-uninvoiced-import-bookings/{customer}', [ImportInvoiceController::class, 'getUninvoicedImportBookings']);
    Route::get('/invoicing/import/{id}/edit', [ImportInvoiceController::class, 'edit'])->name('invoice.import.edit');
    Route::get('/invoicing', function () {
        return view('invoicing');
    })->name('invoicing.index');
    Route::get('/uninvoiced-import', [InvoiceController::class, 'uninvoicedImport'])->name('invoice.uninvoiced.import');
    Route::get('/invoice/create/{bookNo}', [InvoiceController::class, 'createFromBooking'])
        ->name('invoice.createFromBooking');

    Route::post('/invoice/store', [InvoiceController::class, 'storeFromBooking'])
        ->name('invoice.storeFromBooking');


    Route::prefix('invoicing/import')->group(function () {
        Route::get('/create', [ImportInvoiceController::class, 'create'])->name('invoice.import.create');
        Route::post('/store', [ImportInvoiceController::class, 'store'])->name('invoice.import.store');
    });

    Route::prefix('invoicing/export')->group(function () {
        Route::get('/create', [InvoiceController::class, 'exportCreate'])->name('invoice.export.create');
        Route::post('/store', [InvoiceController::class, 'exportStore'])->name('invoice.export.store');
    });

    // Additional Invoicing Routes
    Route::get('/search-data/chart', [BookingController::class, 'searchDataChart'])->name('searchdata.chart');
    Route::get('/invoicing/export/{id}/edit', [InvoiceController::class, 'exportEdit'])->name('invoice.export.edit');
    Route::post('/invoicing/export/{id}/update-items', [InvoiceController::class, 'exportUpdateItems'])->name('invoice.export.update.items');
    Route::get('/invoicing/export/{id}/print', [InvoiceController::class, 'exportPrint'])->name('invoice.export.print');
    Route::get('/invoicing/export/{id}/print-pdf', [InvoiceController::class, 'exportPrintPDF'])->name('invoice.export.print.pdf');
    Route::get('/invoice/recovery', [InvoiceController::class, 'invoiceRecovery'])->name('invoice.recovery');
    Route::get('/invoicing/import/{id}', [ImportInvoiceController::class, 'show'])->name('invoice.import.show');
    Route::put('/invoicing/import/{id}', [ImportInvoiceController::class, 'update'])->name('invoice.import.update');
    Route::get('/uninvoiced-export', [InvoiceController::class, 'uninvoicedExport'])->name('invoice.uninvoiced.export');
    Route::get('/invoicing/import/{id}/print', [ImportInvoiceController::class, 'print'])->name('invoice.import.print');
    Route::get('/customer-bookings/{customer}', [InvoiceController::class, 'getCustomerBookings']);
    Route::get('/customer-import-bookings/{customerId}', [ImportInvoiceController::class, 'getCustomerBookings'])
        ->name('customer.import.bookings');
    Route::get('/customer-export-bookings/{customerId}', [InvoiceController::class, 'getCustomerExportBookings'])
        ->name('customer.export.bookings');
    // Recovery Ivoice
    Route::post('/invoicing/import/{id}/update-items', [ImportInvoiceController::class, 'updateItems'])->name('invoice.import.update.items');
    Route::get('/invoice-recovery', [InvoiceController::class, 'invoiceRecovery'])->name('invoice.recovery');
    Route::get('/invoice-recovery/{id}/{type}', [InvoiceController::class, 'showRecoveryInvoice'])->name('invoice.recovery.show');
    Route::post('/invoice-recovery/save', [InvoiceController::class, 'saveRecovery'])->name('invoice.recovery.save');
    Route::get('/recovered-invoices', [InvoiceController::class, 'recoveredInvoices'])->name('recovered.invoices');
    // Labels Routes
    Route::get('/single-label', [LabelController::class, 'singleLabelForm'])->name('label.single.form');
    Route::post('/single-label', [LabelController::class, 'printSingleLabel'])->name('label.single.print');
    Route::get('/bulk-label', [LabelController::class, 'bulkLabelForm'])->name('label.bulk.form');
    Route::post('/bulk-label', [LabelController::class, 'printBulkLabel'])->name('print.bulk.label');
    Route::get('/sticker-label', [LabelController::class, 'stickerLabelForm'])->name('label.sticker.form');
    Route::post('/sticker-label', [LabelController::class, 'printStickerLabel'])->name('label.sticker.print');
    Route::get('/pdo-bulk-label', [LabelController::class, 'bulkPODForm'])->name('label.bulk.form');
    Route::get('/api/pod-bookings', [LabelController::class, 'podBookingsJson'])->name('api.pod.bookings');
    Route::post('/bulk-pod-label', [LabelController::class, 'printBulkPODLabel'])->name('label.bulk.pod');
    Route::get('/label/pod/single', [LabelController::class, 'searchSinglePOD'])->name('label.single.pod.form');

    // Generate PDF
    Route::get('/label/pod/single/generate', [LabelController::class, 'printSinglePOD'])->name('label.single.pod.generate');
    Route::post('/label/pod/single/generate', [LabelController::class, 'generateSinglePOD'])->name('label.single.pod.generate');
    Route::get('/sales-funnel', [LabelController::class, 'salesFunnel'])->name('sales.funnel');
    Route::get('/single-label/print', [LabelController::class, 'printSingleLabelGet'])->name('label.single.print.get');
    Route::get('/bulk-filter', [LabelController::class, 'bulkFilter'])->name('bulk.filter');
    Route::get('/bulk-row-filter', [LabelController::class, 'bulkRowFilter'])->name('bulk.rowFilter');
    Route::get('/bulk-sort', [LabelController::class, 'bulkSort'])->name('bulk.sort');
    Route::get('/bulk-aggregate', [LabelController::class, 'bulkAggregate'])->name('bulk.aggregate');
    Route::post('/bulk-compute', [LabelController::class, 'bulkCompute'])->name('bulk.compute');
    Route::get('/bulk-chart', [LabelController::class, 'bulkChart'])->name('bulk.chart');
    Route::match(['get', 'post'], '/bulk-download', [LabelController::class, 'bulkDownload'])->name('bulk.download');
    Route::get('/charts/{model}', [ChartController::class, 'universalChart'])->name('charts.universal');
    Route::get('/shipment-sale', [InvoiceController::class, 'allBookings'])
        ->name('shipment.sale');
    // 3PL Routes
    Route::get('/3pl-booking', [OperationController::class, 'create'])->name('thirdparty.index');
    Route::post('/3pl-booking', [OperationController::class, 'store'])->name('thirdparty.store');
    Route::post('/3pl/merchant-advice', [OperationController::class, 'sendMerchantAdvice'])->name('3pl.merchant.advice');
    Route::get('/get-payment-status', [OperationController::class, 'getPaymentStatus'])->name('3pl.payment.status');

    // web.php
    Route::get('/3pl/upload', [OperationController::class, 'uploadForm'])->name('3pl.upload.step1');
    Route::post('/3pl/upload', [OperationController::class, 'handleUpload'])->name('3pl.upload.step1');
    Route::get('/3pl/upload/map', [OperationController::class, 'validateMapping'])->name('3pl.upload.step2');
    Route::post('/3pl/upload/final', [OperationController::class, 'uploadFinal'])->name('3pl.upload.final');
    Route::match(['get', 'post'], '/3pl/filter', [OperationController::class, 'create'])->name('thirdparty.filter');
    Route::match(['get', 'post'], '/3pl/row-filter', [OperationController::class, 'rowFilter'])->name('thirdparty.rowFilter');
    Route::post('/3pl/compute', [OperationController::class, 'compute'])->name('thirdparty.compute');
    Route::match(['get', 'post'], '/3pl/aggregate', [OperationController::class, 'aggregate'])->name('thirdparty.aggregate');
    Route::match(['get', 'post'], '/3pl/chart', [OperationController::class, 'chart'])->name('thirdparty.chart');
    Route::get('/3pl/download', [OperationController::class, 'download'])->name('thirdparty.download');
    Route::post('/thirdparty/flashback', [OperationController::class, 'flashback'])->name('thirdparty.flashback');
    Route::post('/thirdparty/highlight', [OperationController::class, 'highlight'])->name('thirdparty.highlight');
    Route::post('/thirdparty/rows-per-page', [OperationController::class, 'setRowsPerPage'])->name('thirdparty.rowsPerPage');
    Route::post('/thirdparty/settings', [OperationController::class, 'settings'])->name('thirdparty.settings');
    Route::get('3pl/upload/sample-csv', [OperationController::class, 'downloadSampleCsv'])
        ->name('3pl.upload.sample');



    // Scans
    Route::get('/scan/{type}', [ScanController::class, 'showScanForm'])->name('scan.form');


    // Financial
    Route::get('/shipment-cost', [FinancialController::class, 'shipmentCost'])->name('shipment.cost');
    Route::get('/shipment-cost/{bookNo}', [FinancialController::class, 'showCostDetail'])
        ->name('shipment.cost.detail');

    // Save new costing entry
    Route::post('/shipment-cost/store', [FinancialController::class, 'storeCost'])
        ->name('shipment.cost.store');

    // Edit existing costing entry
    Route::get('/shipment-cost/edit/{id}', [FinancialController::class, 'editCost'])
        ->name('shipment.cost.edit');

    // Update costing entry
    Route::put('/shipment-cost/update/{id}', [FinancialController::class, 'updateCost'])
        ->name('shipment.cost.update');

    // Delete costing entry
    Route::delete('/shipment-cost/delete/{id}', [FinancialController::class, 'deleteCost'])
        ->name('shipment.cost.delete');
    Route::get('financial/dashboard', [FinancialController::class, 'dashboard'])->name('financial.dashboard');
    // Manifest
    Route::get('/manifest-pl', [ManifestController::class, 'manifestPL'])->name('manifest.pl');


    // Dimensional Weight
    Route::get('/edit-dimensional-weight', [DimensionalWeightController::class, 'index'])->name('dim.weight.page');
    Route::get('/api/bookings', [DimensionalWeightController::class, 'getBookings'])->name('api.bookings');
    Route::put('/dim-weight/update/{id}', [DimensionalWeightController::class, 'updateDimWeight'])->name('dim.weight.update');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/download', [UserController::class, 'download'])->name('users.download');
    Route::get('/users/filter', [UserController::class, 'filter'])->name('users.filter');
    Route::get('/users/rowFilter', [UserController::class, 'rowFilter'])->name('users.rowFilter');
    Route::get('/users/sort', [UserController::class, 'sort'])->name('users.sort');
    Route::get('/users/aggregate', [UserController::class, 'aggregate'])->name('users.aggregate');
    Route::get('/users/compute', [UserController::class, 'compute'])->name('users.compute');
    Route::get('/users/download', [UserController::class, 'download'])->name('users.download');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');


    // Master Setup
    Route::prefix('master')->group(function () {
        Route::get('/city', [MasterController::class, 'city'])->name('city.index');
        Route::get('/city/create', [MasterController::class, 'create'])->name('city.create');
        Route::post('/city/store', [MasterController::class, 'store'])->name('city.store');
        Route::get('/city/{id}/edit', [MasterController::class, 'editCity'])->name('city.edit');
        Route::put('/city/{id}', [MasterController::class, 'updateCity'])->name('city.update');
        Route::delete('/city/{id}', [MasterController::class, 'destroyCity'])->name('city.destroy');
        // Route::post('/city/compute', [MasterController::class, 'compute'])->name('city.compute');

        // Customer
        Route::get('/customer', [MasterController::class, 'customer'])->name('customer.index');
        Route::get('/customer/create', [MasterController::class, 'createCustomer'])->name('customer.create');
        Route::post('/customer/store', [MasterController::class, 'storeCustomer'])->name('customer.store');
        Route::get('/customer/{id}/edit', [MasterController::class, 'editCustomer'])->name('customer.edit');
        Route::put('/customer/{id}', [MasterController::class, 'updateCustomer'])->name('customer.update');
        Route::delete('/customer/{id}', [MasterController::class, 'destroyCustomer'])->name('customer.destroy');
        Route::get('/customer/download/{format}', [MasterController::class, 'downloadCustomer'])->name('customer.download');
        Route::get('booking/attachments', [MasterController::class, 'bookingAttachments'])
            ->name('booking.attachments');

        Route::get('booking/attachments/download/{id}', [MasterController::class, 'downloadBookingAttachment'])
            ->name('booking.attachments.download');
    });

    // AJAX
    Route::get('/get-states/{country_id}', [MasterController::class, 'getStates'])->name('ajax.getStates');
    Route::get('/get-cities/{state_id}', [MasterController::class, 'getCities'])->name('ajax.getCities');
    Route::get('/get-country-by-city/{cityName}', [MasterController::class, 'getCountryByCity'])->name('ajax.getCountryByCity');
    Route::get('/city/download', [MasterController::class, 'download'])->name('city.download');
    Route::get('/city/chart', [MasterController::class, 'chart'])->name('city.chart');
    Route::post('/cities/columns', [MasterController::class, 'setColumns'])->name('city.columns');
    Route::get('/get-cities-by-country/{country_id}', [MasterController::class, 'getCitiesByCountry'])->name('ajax.getCitiesByCountry');
});
