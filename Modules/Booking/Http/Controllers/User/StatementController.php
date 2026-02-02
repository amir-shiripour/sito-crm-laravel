<?php

namespace Modules\Booking\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Entities\BookingForm;
use App\Models\User;
use Carbon\Carbon;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;
use Spatie\Browsershot\Browsershot;
use Spatie\Permission\Models\Role;

class StatementController extends Controller
{
    public function index(Request $request)
    {
        $settings = BookingSetting::current();
        $user = $request->user();

        if (!$this->canViewStatement($user, $settings)) {
            abort(403);
        }

        // Get statement roles from settings
        $statementRoleIds = array_values(array_filter(
            array_map('intval', (array) ($settings->statement_roles ?? [])),
            fn($v) => $v > 0
        ));

        $statementRoles = [];
        if (!empty($statementRoleIds)) {
            $statementRoles = Role::whereIn('id', $statementRoleIds)->get();
        }

        // Get selected users for each role
        $selectedUsers = [];
        foreach ($statementRoles as $role) {
            $inputName = 'role_' . $role->id;
            $userId = $request->input($inputName);
            if ($userId) {
                $selectedUsers[$role->id] = User::find($userId);
            }
        }

        // Handle legacy provider_id if present (for backward compatibility or direct provider selection)
        $selectedProviderId = $request->input('provider_id');

        $appointments = null;
        $firstAppointmentTime = null;
        $lastAppointmentTime = null;

        $startDateLocal = $request->input('start_date', Jalalian::now()->format('Y/m/d'));
        $endDateLocal = $request->input('end_date', Jalalian::now()->format('Y/m/d'));

        // Collect all user IDs to filter appointments
        $allUserIds = [];
        if ($selectedProviderId) {
            $allUserIds[] = $selectedProviderId;
        }
        foreach ($selectedUsers as $u) {
            $allUserIds[] = $u->id;
        }
        $allUserIds = array_unique($allUserIds);

        // If any user is selected, fetch appointments
        if (!empty($allUserIds) && $startDateLocal && $endDateLocal) {
            $result = $this->getAppointments($allUserIds, $startDateLocal, $endDateLocal);
            $appointments = $result['grouped'];
            $firstAppointmentTime = $result['first'];
            $lastAppointmentTime = $result['last'];
        }

        return view('booking::user.statement.index', compact(
            'statementRoles',
            'selectedUsers',
            'selectedProviderId',
            'appointments',
            'startDateLocal',
            'endDateLocal',
            'firstAppointmentTime',
            'lastAppointmentTime'
        ));
    }

    public function print(Request $request)
    {
        $settings = BookingSetting::current();
        $user = $request->user();

        if (!$this->canViewStatement($user, $settings)) {
            abort(403);
        }

        // 1. Identify Required Roles from Settings
        $statementRoleIds = array_values(array_filter(
            array_map('intval', (array) ($settings->statement_roles ?? [])),
            fn($v) => $v > 0
        ));

        // 2. Validate that a user is selected for EACH required role
        $missingRoleNames = [];
        $statementRoles = Role::whereIn('id', $statementRoleIds)->get();
        $selectedUsers = [];

        foreach ($statementRoles as $role) {
            $inputName = 'role_' . $role->id;
            $userId = $request->input($inputName);

            if (!$userId) {
                $missingRoleNames[] = $role->name;
            } else {
                $userObj = User::find($userId);
                if ($userObj) {
                    $selectedUsers[$role->id] = $userObj;
                } else {
                    // User ID provided but not found in DB
                    $missingRoleNames[] = $role->name . ' (کاربر نامعتبر)';
                }
            }
        }

        if (!empty($missingRoleNames)) {
            return redirect()->back()->with('error', 'جهت دریافت خروجی PDF، انتخاب کاربر برای نقش‌های زیر الزامی است: ' . implode('، ', $missingRoleNames));
        }

        // 3. Prepare Data for PDF
        $selectedProviderId = $request->input('provider_id');
        $startDateLocal = $request->input('start_date');
        $endDateLocal = $request->input('end_date');

        if (!$startDateLocal || !$endDateLocal) {
            return redirect()->back()->with('error', 'لطفاً بازه زمانی را انتخاب کنید.');
        }

        $allUserIds = [];
        if ($selectedProviderId) {
            $allUserIds[] = $selectedProviderId;
            // Add provider to selectedUsers for display in PDF header if not already there
            $providerUser = User::find($selectedProviderId);
            if ($providerUser) {
                 $alreadyIn = false;
                 foreach($selectedUsers as $u) {
                     if($u->id == $providerUser->id) $alreadyIn = true;
                 }
                 if(!$alreadyIn) {
                     // Use a special key for provider to display it
                     $selectedUsers['provider'] = $providerUser;
                 }
            }
        }

        foreach ($selectedUsers as $key => $u) {
            if ($key !== 'provider') {
                 $allUserIds[] = $u->id;
            }
        }
        $allUserIds = array_unique($allUserIds);

        if (empty($allUserIds)) {
             return redirect()->back()->with('error', 'هیچ کاربری (پزشک یا نقش‌های دیگر) برای گزارش انتخاب نشده است.');
        }

        $result = $this->getAppointments($allUserIds, $startDateLocal, $endDateLocal);

        $html = view('booking::user.statement.pdf', [
            'appointments' => $result['grouped'],
            'firstAppointmentTime' => $result['first'],
            'lastAppointmentTime' => $result['last'],
            'startDateLocal' => $startDateLocal,
            'endDateLocal' => $endDateLocal,
            'selectedUsers' => $selectedUsers,
            'statementRoles' => $statementRoles,
            'selectedProviderId' => $selectedProviderId,
        ])->render();

        // Set Node.js and NPM paths explicitly
        // You can change these paths based on your server configuration
        // Or read from .env file: env('NODE_PATH', 'C:\\Program Files\\nodejs\\node.exe')

        $browsershot = Browsershot::html($html);

        // Only set Windows paths if running on Windows
        if (PHP_OS_FAMILY === 'Windows') {
            $browsershot->setNodeBinary('C:\\Program Files\\nodejs\\node.exe')
                ->setNpmBinary('C:\\Program Files\\nodejs\\npm.cmd')
                ->setChromePath('C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe');
        } else {
            // Linux / Server configuration
            $browsershot->noSandbox()
                ->setOption('args', ['--disable-web-security']);
        }

        $pdf = $browsershot
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->pdf();


        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="statement.pdf"',
        ]);
    }

    public function searchUsers(Request $request)
    {
        $settings = BookingSetting::current();
        $user = $request->user();

        if (!$this->canViewStatement($user, $settings)) {
            return response()->json(['data' => []]);
        }

        $q = trim((string)$request->query('q', ''));
        $roleId = (int)$request->query('role_id');

        $usersQuery = User::query();

        if ($roleId) {
            $usersQuery->whereHas('roles', fn($r) => $r->where('id', $roleId));
        }

        // If user is a provider and not admin, only show themselves if they have the role
        if ($this->userIsProvider($user, $settings) && !$this->isAdminUser($user)) {
             $usersQuery->where('id', $user->id);
        }

        if ($q !== '') {
            $usersQuery->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('mobile', 'like', "%{$q}%");
            });
        }

        $users = $usersQuery->orderBy('name')->limit(50)->get(['id', 'name', 'mobile']);

        return response()->json(['data' => $users]);
    }

    // Kept for the main provider search box
    public function searchProviders(Request $request)
    {
        $settings = BookingSetting::current();
        $user = $request->user();

        if (!$this->canViewStatement($user, $settings)) {
            return response()->json(['data' => []]);
        }

        $q = trim((string)$request->query('q', ''));

        $providersQuery = User::query();
        $roleIds = array_values(array_filter(
            array_map('intval', (array) ($settings->allowed_roles ?? [])),
            fn($v) => $v > 0
        ));

        if (!$this->isAdminUser($user) && !empty($roleIds)) {
            $providersQuery->whereHas('roles', fn($r) => $r->whereIn('id', $roleIds));
        }

        if ($this->userIsProvider($user, $settings) && !$this->isAdminUser($user)) {
             $providersQuery->where('id', $user->id);
        } else {
             if (!empty($roleIds)) {
                 $providersQuery->whereHas('roles', fn($r) => $r->whereIn('id', $roleIds));
             }
        }

        if ($q !== '') {
            $providersQuery->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('mobile', 'like', "%{$q}%");
            });
        }

        $providers = $providersQuery->orderBy('name')->limit(50)->get(['id', 'name', 'mobile']);

        return response()->json(['data' => $providers]);
    }

    protected function getAppointments(array $providerIds, $startDateLocal, $endDateLocal)
    {
        $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');

        $startUtc = $this->convertJalaliToUtc($startDateLocal, $scheduleTz, true);
        $endUtc = $this->convertJalaliToUtc($endDateLocal, $scheduleTz, false);

        if (!$startUtc || !$endUtc) {
            return ['grouped' => collect(), 'first' => null, 'last' => null];
        }

        $appointments = Appointment::query()
            ->with(['service.appointmentForm', 'service.category', 'client', 'provider'])
            ->whereIn('provider_user_id', $providerIds)
            ->where('start_at_utc', '>=', $startUtc)
            ->where('start_at_utc', '<=', $endUtc)
            ->orderBy('start_at_utc')
            ->get();

        $first = $appointments->first()?->start_at_utc;
        $last = $appointments->last()?->start_at_utc;

        // Process appointments (same logic as before)
        foreach ($appointments as $appointment) {
            $appointment->unit_count = null;

            if (!empty($appointment->appointment_form_response_json) && $appointment->service && $appointment->service->appointmentForm) {
                $form = $appointment->service->appointmentForm;
                $formSchema = $form->schema_json;
                $fieldMeta = [];
                if (isset($formSchema['fields']) && is_array($formSchema['fields'])) {
                    foreach ($formSchema['fields'] as $field) {
                        if (isset($field['name'])) {
                            $fieldMeta[$field['name']] = [
                                'label' => $field['label'] ?? $field['name'],
                                'icon' => $field['icon'] ?? null
                            ];
                        }
                    }
                }

                $newResponse = [];
                $totalUnits = 0;

                foreach ($appointment->appointment_form_response_json as $key => $value) {
                    $meta = $fieldMeta[$key] ?? ['label' => $key, 'icon' => null];
                    $newResponse[] = [
                        'key' => $key,
                        'label' => $meta['label'],
                        'icon' => $meta['icon'],
                        'value' => $value
                    ];

                    if ($form->form_type === BookingForm::TYPE_TOOTH_NUMBER && !empty($value)) {
                        $items = array_filter(array_map('trim', explode(',', (string)$value)), fn($v) => $v !== '');
                        $totalUnits += count($items);
                    }
                }
                $appointment->processed_form_response = $newResponse;

                if ($form->form_type === BookingForm::TYPE_TOOTH_NUMBER && $totalUnits > 0) {
                    $appointment->unit_count = $totalUnits;
                }

            } else {
                $newResponse = [];
                if (is_array($appointment->appointment_form_response_json)) {
                    foreach ($appointment->appointment_form_response_json as $key => $value) {
                        $newResponse[] = [
                            'key' => $key,
                            'label' => $key,
                            'icon' => null,
                            'value' => $value
                        ];
                    }
                }
                $appointment->processed_form_response = $newResponse;
            }
        }

        $grouped = $appointments->groupBy(function ($appointment) {
            return $appointment->service && $appointment->service->category
                ? $appointment->service->category->name
                : 'بدون دسته‌بندی';
        });

        return [
            'grouped' => $grouped,
            'first' => $first,
            'last' => $last
        ];
    }

    // ... (rest of helper methods: convertJalaliToUtc, getProviders, canViewStatement, isAdminUser, userIsProvider)

    protected function convertJalaliToUtc($jalaliDate, $tz, $isStartOfDay)
    {
        try {
            $datePieces = preg_split('/[^\d]+/', trim($jalaliDate));
            if (count($datePieces) < 3) {
                return null;
            }

            [$jy, $jm, $jd] = array_map('intval', array_slice($datePieces, 0, 3));
            [$gy, $gm, $gd] = CalendarUtils::toGregorian($jy, $jm, $jd);

            $carbon = Carbon::create($gy, $gm, $gd, 0, 0, 0, $tz);

            if ($isStartOfDay) {
                $carbon->startOfDay();
            } else {
                $carbon->endOfDay();
            }

            return $carbon->timezone('UTC');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function getProviders($user, $settings)
    {
        // This method might be redundant now if we use searchUsers, but kept for compatibility if needed
        $providersQuery = User::query();
        $roleIds = array_values(array_filter(
            array_map('intval', (array) ($settings->allowed_roles ?? [])),
            fn($v) => $v > 0
        ));

        if (!$this->isAdminUser($user) && !empty($roleIds)) {
            $providersQuery->whereHas('roles', fn($r) => $r->whereIn('id', $roleIds));
        }

        if ($this->userIsProvider($user, $settings) && !$this->isAdminUser($user)) {
             $providersQuery->where('id', $user->id);
        }

        return $providersQuery->orderBy('name')->get(['id', 'name']);
    }

    protected function canViewStatement($user, $settings)
    {
        return $user->can('booking.appointments.view') ||
               $this->isAdminUser($user) ||
               $this->userIsProvider($user, $settings);
    }

    protected function isAdminUser($user)
    {
        if (!$user) return false;
        return $user->hasAnyRole(['super-admin', 'admin']) ||
               $user->can('booking.manage') ||
               $user->can('booking.appointments.manage');
    }

    protected function userIsProvider($user, $settings)
    {
        if (!$user) return false;

        $providerRoleIds = array_values(array_filter(
            array_map('intval', (array) ($settings->allowed_roles ?? [])),
            fn($v) => $v > 0
        ));

        if (empty($providerRoleIds)) return false;

        $userRoleIds = $user->roles()->pluck('id')->map(fn($v) => (int) $v)->all();
        return count(array_intersect($providerRoleIds, $userRoleIds)) > 0;
    }
}
