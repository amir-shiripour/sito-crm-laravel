<?php

namespace Modules\Booking\Services;

use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingPayment;
use Modules\Clients\Entities\Client;
use Modules\Wallet\App\Services\WalletService;
use Modules\Wallet\App\Enums\TransactionType;
use Illuminate\Support\Facades\Log;

class BookingPaymentWalletService
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Handle payment status transition.
     */
    public function handlePaymentStatusChange(BookingPayment $payment, ?string $oldStatus, string $newStatus): void
    {
        $client = Client::find($payment->client_id);
        if (!$client) {
            return;
        }

        $meta = $payment->meta ?? [];
        $amount = (float) $payment->amount;

        if ($amount <= 0) {
            return;
        }

        // Scenario 1: Payment becomes PAID (from non-PAID)
        if ($newStatus === BookingPayment::STATUS_PAID && $oldStatus !== BookingPayment::STATUS_PAID) {
            $isAlreadyCredited = (!empty($meta['wallet_credited_amount']) && (float) $meta['wallet_credited_amount'] > 0) || (!empty($meta['wallet_credited']) && $meta['wallet_credited'] === true);
            if ($isAlreadyCredited) {
                return; // Already credited
            }

            try {
                $payment->loadMissing('appointment.service');
                $serviceName = $payment->appointment?->service?->name ?? '';
                $desc = "شارژ/پرداخت نوبت #" . ($payment->appointment_id ?? '') . ($serviceName ? " (سرویس: {$serviceName})" : '');

                $tx = $this->walletService->deposit(
                    holder: $client,
                    amount: $amount,
                    type: TransactionType::DEPOSIT,
                    payable: $payment,
                    description: $desc,
                    meta: [
                        'payment_id' => $payment->id,
                        'appointment_id' => $payment->appointment_id,
                        'payment_type' => $payment->type,
                        'gateway_ref' => $payment->gateway_ref,
                    ]
                );

                $meta['wallet_credited_amount'] = $amount;
                $meta['wallet_credited'] = true;
                $meta['wallet_deposit_tx_id'] = $tx->id;
                unset($meta['wallet_deducted_amount']);
                
                $payment->meta = $meta;
                $payment->saveQuietly();
            } catch (\Exception $e) {
                Log::error('Error depositing to wallet on payment PAID', ['exception' => $e->getMessage()]);
            }
        }

        // Scenario 2: Payment changes FROM PAID to any non-PAID status (PENDING, FAILED, CANCELLED, REFUNDED)
        if ($oldStatus === BookingPayment::STATUS_PAID && $newStatus !== BookingPayment::STATUS_PAID) {
            $creditedAmount = (float) ($meta['wallet_credited_amount'] ?? $amount);
            $wasCredited = $creditedAmount > 0 || !empty($meta['wallet_credited']);

            if ($wasCredited) {
                try {
                    $reasonDesc = match($newStatus) {
                        BookingPayment::STATUS_PENDING => "تغییر وضعیت پرداخت به در انتظار پرداخت",
                        BookingPayment::STATUS_FAILED => "عدم تایید/رد پرداخت",
                        BookingPayment::STATUS_CANCELLED => "لغو پرداخت",
                        BookingPayment::STATUS_REFUNDED => "استرداد پرداخت",
                        default => "تغییر وضعیت پرداخت"
                    };

                    $withdrawAmt = $creditedAmount > 0 ? $creditedAmount : $amount;

                    $tx = $this->walletService->withdraw(
                        holder: $client,
                        amount: $withdrawAmt,
                        type: TransactionType::WITHDRAW,
                        payable: $payment,
                        description: "کسر از کیف پول به دلیل " . $reasonDesc . " #" . $payment->id,
                        meta: [
                            'payment_id' => $payment->id,
                            'appointment_id' => $payment->appointment_id,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus,
                        ]
                    );

                    $meta['wallet_credited_amount'] = 0; // Clear credit flag so it can be re-credited if marked PAID again
                    unset($meta['wallet_credited']);     // Completely unset legacy boolean flag too
                    $meta['wallet_deducted_amount'] = $withdrawAmt;
                    $meta['wallet_withdraw_tx_id'] = $tx->id;

                    $payment->meta = $meta;
                    $payment->saveQuietly();
                } catch (\Exception $e) {
                    Log::error('Error withdrawing from wallet on payment status change from PAID', ['exception' => $e->getMessage()]);
                }
            }
        }
    }

    /**
     * Handle appointment status transition (e.g. cancellation by client/admin).
     */
    public function handleAppointmentStatusChange(Appointment $appointment, ?string $oldStatus, string $newStatus): void
    {
        $canceledStatuses = [
            Appointment::STATUS_CANCELED_BY_ADMIN,
            Appointment::STATUS_CANCELED_BY_CLIENT,
        ];

        if (!in_array($newStatus, $canceledStatuses)) {
            return;
        }

        $appointment->loadMissing(['payments', 'client']);
        $client = $appointment->client;

        if (!$client) {
            return;
        }

        foreach ($appointment->payments as $payment) {
            if ($payment->status !== BookingPayment::STATUS_PAID && $payment->status !== BookingPayment::STATUS_CANCELLED && $payment->status !== BookingPayment::STATUS_FAILED) {
                continue;
            }

            $meta = $payment->meta ?? [];
            $amount = (float) $payment->amount;

            $isOnline = in_array($payment->type, ['online', 'zarinpal', 'zibal', 'gateway']);
            $isManualOrTransfer = in_array($payment->type, ['transfer', 'manual', 'pos', 'card']);

            if ($isOnline && $payment->status === BookingPayment::STATUS_PAID) {
                // Online payment: Client paid real money!
                // Money remains in client wallet.
                if (empty($meta['wallet_credited_amount'])) {
                    try {
                        $tx = $this->walletService->deposit(
                            holder: $client,
                            amount: $amount,
                            type: TransactionType::REFUND,
                            payable: $appointment,
                            description: "موجودی کیف پول بابت لغو نوبت #" . $appointment->id,
                            meta: ['appointment_id' => $appointment->id, 'payment_id' => $payment->id]
                        );
                        $meta['wallet_credited_amount'] = $amount;
                        $meta['wallet_deposit_tx_id'] = $tx->id;
                        $payment->update(['meta' => $meta]);
                    } catch (\Exception $e) {
                        Log::error('Error depositing online cancelled appointment to wallet', ['exception' => $e->getMessage()]);
                    }
                }
            } elseif ($isManualOrTransfer) {
                // If appointment was cancelled because transfer/receipt was rejected by admin:
                // Deduct any credited wallet amount!
                $creditedAmount = (float) ($meta['wallet_credited_amount'] ?? 0);
                if ($creditedAmount > 0 && empty($meta['wallet_deducted_amount'])) {
                    try {
                        $tx = $this->walletService->withdraw(
                            holder: $client,
                            amount: $creditedAmount,
                            type: TransactionType::WITHDRAW,
                            payable: $appointment,
                            description: "کسر از کیف پول بابت لغو نوبت غیرمعتبر #" . $appointment->id,
                            meta: ['appointment_id' => $appointment->id, 'payment_id' => $payment->id]
                        );
                        $meta['wallet_deducted_amount'] = $creditedAmount;
                        $meta['wallet_withdraw_tx_id'] = $tx->id;
                        $payment->update(['meta' => $meta]);
                    } catch (\Exception $e) {
                        Log::error('Error withdrawing unconfirmed transfer on appointment cancel', ['exception' => $e->getMessage()]);
                    }
                }
            }
        }
    }
}
