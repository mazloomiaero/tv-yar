<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Payment\VerifyGatewayPayment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class VerifyPaymentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $authority) {}

    public function handle(VerifyGatewayPayment $verify): void
    {
        $verify->execute($this->authority);
    }
}
