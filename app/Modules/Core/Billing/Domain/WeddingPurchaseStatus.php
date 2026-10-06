<?php

namespace App\Modules\Core\Billing\Domain;

enum WeddingPurchaseStatus: string
{
    case PendingPayment = 'pending_payment';
    case PaymentExpired = 'payment_expired';
    case AwaitingPublish = 'awaiting_publish';
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
}
