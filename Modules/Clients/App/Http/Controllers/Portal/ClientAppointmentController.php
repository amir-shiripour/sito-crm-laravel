<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingSetting;

class ClientAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $client = auth('client')->user();

        $query = Appointment::where('client_id', $client->id)
            ->with(['service', 'provider', 'payments'])
            ->orderBy('start_at_utc', 'desc');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $appointments = $query->paginate(15)->withQueryString();

        $bookingCurrencyUnit = 'IRR';
        $bookingCurrencyLabel = 'ریال';
        if (class_exists(BookingSetting::class)) {
            try {
                $bookingSettings = BookingSetting::current();
                $bookingCurrencyUnit = $bookingSettings->currency_unit ?? 'IRR';
                $bookingCurrencyLabel = $bookingCurrencyUnit === 'IRT' ? 'تومان' : 'ریال';
            } catch (\Exception $e) {}
        }

        return view('clients::portal.appointments.index', compact(
            'appointments',
            'bookingCurrencyUnit',
            'bookingCurrencyLabel'
        ));
    }
    public function show(Appointment $appointment)
    {
        // Ensure the client can only view their own appointments
        if ($appointment->client_id !== auth('client')->id()) {
            abort(403);
        }

        // Load necessary relations
        $appointment->load(['service.appointmentForm', 'provider', 'payments']);

        $rawFormResponses = $appointment->appointment_form_response_json ?? [];
        $formResponses = [];

        if (!empty($rawFormResponses)) {
            $fieldMeta = [];

            if ($appointment->service && $appointment->service->appointmentForm) {
                $form = $appointment->service->appointmentForm;
                $formSchema = $form->schema_json;

                if (isset($formSchema['fields']) && is_array($formSchema['fields'])) {
                    foreach ($formSchema['fields'] as $field) {
                        $label = $field['label'] ?? $field['name'] ?? $field['id'] ?? '';
                        $type  = $field['type'] ?? 'text';

                        if (isset($field['name'])) {
                            $fieldMeta[$field['name']] = ['label' => $label, 'type' => $type];
                        }
                        if (isset($field['id'])) {
                            $fieldMeta[$field['id']] = ['label' => $label, 'type' => $type];
                        }
                    }
                }
            }

            // Collect user IDs for select-user-by-role or user select fields
            $roleUserIds = [];
            foreach ($rawFormResponses as $key => $value) {
                $type = $fieldMeta[$key]['type'] ?? '';
                $isUserField = $type === 'select-user-by-role' || $type === 'select-user' || str_contains(strtolower((string)$key), 'user') || str_contains(strtolower((string)$key), 'doctor') || str_contains(strtolower((string)$key), 'provider');
                if ($isUserField) {
                    if (is_array($value)) {
                        foreach ($value as $v) {
                            if (is_numeric($v)) $roleUserIds[] = (int) $v;
                        }
                    } elseif (is_numeric($value)) {
                        $roleUserIds[] = (int) $value;
                    }
                }
            }

            $userNamesMap = [];
            if (!empty($roleUserIds)) {
                $userNamesMap = User::whereIn('id', array_unique($roleUserIds))->pluck('name', 'id')->toArray();
            }

            foreach ($rawFormResponses as $key => $value) {
                $meta = $fieldMeta[$key] ?? ['label' => $key, 'type' => 'text'];
                $type = $meta['type'] ?? 'text';
                $displayValue = $value;

                $isUserField = $type === 'select-user-by-role' || $type === 'select-user' || str_contains(strtolower((string)$key), 'user') || str_contains(strtolower((string)$key), 'doctor') || str_contains(strtolower((string)$key), 'provider');

                if ($isUserField) {
                    if (is_array($value)) {
                        $names = array_map(fn($id) => $userNamesMap[$id] ?? (is_numeric($id) ? "کاربر #{$id}" : $id), $value);
                        $displayValue = implode('، ', $names);
                    } elseif (is_numeric($value) && isset($userNamesMap[$value])) {
                        $displayValue = $userNamesMap[$value];
                    }
                }

                $formResponses[] = [
                    'label' => $meta['label'],
                    'value' => $displayValue,
                ];
            }
        }

        // Load booking settings for currency display
        $bookingSettings = null;
        $bookingCurrencyUnit = 'IRR';
        $bookingCurrencyLabel = 'ریال';
        if (class_exists(\Modules\Booking\Entities\BookingSetting::class)) {
            try {
                $bookingSettings = \Modules\Booking\Entities\BookingSetting::current();
                $bookingCurrencyUnit = $bookingSettings->currency_unit ?? 'IRR';
                $bookingCurrencyLabel = $bookingCurrencyUnit === 'IRT' ? 'تومان' : 'ریال';
            } catch (\Exception $e) {
                // fallback to defaults
            }
        }

        $settingsMap = \Illuminate\Support\Facades\Schema::hasTable('settings')
            ? \Modules\Settings\Entities\Setting::query()->pluck('value', 'key')->toArray()
            : [];

        $activeRaw = $settingsMap['active_payment_methods'] ?? '[]';
        $activeMethods = is_string($activeRaw) ? json_decode($activeRaw, true) : (array) $activeRaw;
        if (empty($activeMethods) || !is_array($activeMethods)) {
            $activeMethods = ['online', 'pos', 'transfer', 'cod', 'installment'];
        }

        $allMethodLabels = [
            'online' => 'درگاه پرداخت آنلاین',
            'pos' => 'دستگاه کارتخوان (POS)',
            'transfer' => 'انتقال بانکی / کارت به کارت (شبا)',
            'cod' => 'پرداخت در محل (نقد)',
            'installment' => 'پرداخت قسطی / چک',
        ];

        $availablePaymentMethods = [];
        foreach ($activeMethods as $m) {
            if (isset($allMethodLabels[$m])) {
                $availablePaymentMethods[$m] = $allMethodLabels[$m];
            }
        }

        $posDevices = is_string($settingsMap['pos_devices'] ?? null) ? (json_decode($settingsMap['pos_devices'], true) ?: []) : [];
        $bankAccounts = is_string($settingsMap['bank_transfer_accounts'] ?? null) ? (json_decode($settingsMap['bank_transfer_accounts'], true) ?: []) : [];
        $installmentTypes = is_string($settingsMap['installment_types'] ?? null) ? (json_decode($settingsMap['installment_types'], true) ?: []) : [];

        $onlineGateways = [];
        if (($settingsMap['zarinpal_status'] ?? '') === 'active') {
            $onlineGateways[] = ['id' => 'zarinpal', 'label' => 'درگاه زرین‌پال'];
        }
        if (($settingsMap['zibal_status'] ?? '') === 'active') {
            $onlineGateways[] = ['id' => 'zibal', 'label' => 'درگاه زیبال'];
        }
        if (($settingsMap['behpardakht_status'] ?? '') === 'active') {
            $onlineGateways[] = ['id' => 'بهپرداخت ملت', 'label' => 'درگاه بهپرداخت ملت'];
        }

        $paymentSubItems = [
            'pos' => array_values(array_map(fn($d) => [
                'id' => $d['id'] ?? ($d['name'] ?? ''),
                'label' => ($d['name'] ?? 'کارتخوان') . (!empty($d['account_number']) ? ' (حساب: ' . $d['account_number'] . ')' : '')
            ], $posDevices)),
            'transfer' => array_values(array_map(fn($a) => [
                'id' => $a['id'] ?? ($a['bank_name'] ?? ''),
                'label' => ($a['bank_name'] ?? 'بانک') . (!empty($a['owner_name']) ? ' - ' . $a['owner_name'] : '') . (!empty($a['card_number']) ? ' (' . $a['card_number'] . ')' : (!empty($a['iban']) ? ' (' . $a['iban'] . ')' : ''))
            ], $bankAccounts)),
            'installment' => array_values(array_map(fn($i) => [
                'id' => $i['id'] ?? ($i['title'] ?? ''),
                'label' => ($i['title'] ?? 'طرح اقساطی') . (!empty($i['default_tier_config']['max_months']) ? ' (' . $i['default_tier_config']['max_months'] . ' ماهه)' : '')
            ], $installmentTypes)),
            'online' => $onlineGateways,
        ];

        return view('clients::portal.appointments.show', compact(
            'appointment',
            'formResponses',
            'bookingCurrencyUnit',
            'bookingCurrencyLabel',
            'availablePaymentMethods',
            'paymentSubItems',
            'bankAccounts'
        ));
    }

    public function cancel(Appointment $appointment)
    {
        if ($appointment->client_id !== auth('client')->id()) {
            abort(403);
        }

        if (in_array($appointment->status, [Appointment::STATUS_CONFIRMED, Appointment::STATUS_PENDING, Appointment::STATUS_PENDING_PAYMENT, Appointment::STATUS_RESCHEDULED])) {
            $appointment->status = Appointment::STATUS_CANCELED_BY_CLIENT;
            $appointment->save();

            return back()->with('success', 'نوبت شما با موفقیت لغو شد.');
        }

        return back()->with('error', 'امکان لغو این نوبت وجود ندارد.');
    }
}
