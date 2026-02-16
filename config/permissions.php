<?php
// config/permissions.php

return [

    /*
    |-----------------------------------------------------------------------
    | Application permissions
    |-----------------------------------------------------------------------
    |
    | Add or remove permissions here. Use dot notation for sub-permissions.
    | Example: 'booking.domestic', 'label-print.single', etc.
    |
    */
    'all' => [
        'dashboard',
        'book-tracking',

        // Booking (main + sub)
        'booking',
        'booking.domestic',
        'booking.export',
        'booking.import',
        'booking.cross-border',
        'booking.bulk-attachments',

        // Label Print
        'label-print',
        'label-print.single',
        'label-print.bulk',
        'label-print.pdo-single',
        'label-print.pdo-bulk',
        'label-print.sticker',
        'label-print.undertaking',

        // Operation
        'operation',
        'operation.3pl-booking',
        'operation.3pl-upload',
        'operation.scanning.arrival',
        'operation.scanning.delivery',
        'operation.assigning',
        'operation.edit-weight',
        'operation.shipment-status',
        'operation.bulk-status',

        // Reports
        'reports',
        'reports.pending',
        'reports.booking-edit',
        'reports.search-data',
        'reports.booking-void',
        'reports.analysis',
        'reports.sales-funnel',
        'reports.manifest',
        'reports.void-booking',
        'reports.attachments',

        // Financials
        'financials',
        'financials.shipment-cost',
        'financials.invoicing',
        'financials.dashboard',
        'financials.shipment-sale',

        // Master setup
        'master-setup',
        'master-setup.city',
        'master-setup.customer',
        'master-setup.user',
    ],
];
