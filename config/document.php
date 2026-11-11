<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Document sequence year
    |--------------------------------------------------------------------------
    |
    | Passed explicitly to DocumentNumberService. The architecture example
    | uses 1405. This application does not convert Gregorian dates to Jalali.
    |
    */

    'year' => (int) env('DOCUMENT_YEAR', 1405),

];
