<?php

namespace Modules\Booking\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Booking\Entities\Appointment;
use Modules\Booking\Entities\BookingSetting;
use Modules\Booking\Entities\BookingForm;
use Modules\Booking\Entities\BookingStatement;
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

        // If user is a provider and not admin, force provider_id to be their own ID
        // BUT ONLY if they don't have permission to view all
        if ($this->userIsProvider($user, $settings) && !$this->isAdminUser($user) && !$user->can('booking.statement.view.all')) {
            $selectedProviderId = $user->id;
        }

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
            // Security check: if user is provider and not admin, ensure they are only fetching their own appointments
            // UNLESS they have view.all permission
            if ($this->userIsProvider($user, $settings) && !$this->isAdminUser($user) && !$user->can('booking.statement.view.all')) {
                if (!in_array($user->id, $allUserIds)) {
                     // If provider is not in the list (which shouldn't happen due to forced ID above), force it
                }
            }

            $result = $this->getAppointments($allUserIds, $startDateLocal, $endDateLocal);
            $appointments = $result['grouped'];
            $firstAppointmentTime = $result['first'];
            $lastAppointmentTime = $result['last'];
        }

        // Fetch existing statements for the list
        $statementsQuery = BookingStatement::with(['provider', 'user'])
            ->orderBy('created_at', 'desc');

        // If user is a provider and not admin, only show their own statements
        // UNLESS they have view.all permission
        if ($this->userIsProvider($user, $settings) && !$this->isAdminUser($user) && !$user->can('booking.statement.view.all')) {
            $statementsQuery->where('provider_id', $user->id);
        }

        $statements = $statementsQuery->paginate(10);

        // Calculate live appointment times for each statement
        foreach ($statements as $statement) {
            $statementUserIds = [];
            if ($statement->provider_id) {
                $statementUserIds[] = $statement->provider_id;
            }
            if ($statement->roles_data) {
                foreach ($statement->roles_data as $uId) {
                    $statementUserIds[] = $uId;
                }
            }
            $statementUserIds = array_unique($statementUserIds);

            if (!empty($statementUserIds)) {
                // Use stored Gregorian dates directly to avoid conversion issues
                $sDate = $statement->start_date->format('Y-m-d');
                $eDate = $statement->end_date->format('Y-m-d');

                $scheduleTz = config('booking.timezones.schedule', 'Asia/Tehran');

                // Manually create UTC range from Gregorian dates
                // Start of day in schedule timezone -> converted to UTC
                $startUtc = Carbon::createFromFormat('Y-m-d H:i:s', $sDate . ' 00:00:00', $scheduleTz)->timezone('UTC');
                $endUtc = Carbon::createFromFormat('Y-m-d H:i:s', $eDate . ' 23:59:59', $scheduleTz)->timezone('UTC');

                if ($startUtc && $endUtc) {
                    $times = Appointment::query()
                        ->whereIn('provider_user_id', $statementUserIds)
                        ->where('start_at_utc', '>=', $startUtc)
                        ->where('start_at_utc', '<=', $endUtc)
                        ->selectRaw('MIN(start_at_utc) as first_at, MAX(end_at_utc) as last_at')
                        ->first();

                    if ($times) {
                        // Parse as UTC first, then convert to display timezone
                        $statement->live_first_time = $times->first_at
                            ? Carbon::createFromFormat('Y-m-d H:i:s', $times->first_at, 'UTC')->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('H:i')
                            : null;

                        $statement->live_last_time = $times->last_at
                            ? Carbon::createFromFormat('Y-m-d H:i:s', $times->last_at, 'UTC')->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('H:i')
                            : null;
                    }
                }
            }
        }

        return view('booking::user.statement.index', compact(
            'statementRoles',
            'selectedUsers',
            'selectedProviderId',
            'appointments',
            'startDateLocal',
            'endDateLocal',
            'firstAppointmentTime',
            'lastAppointmentTime',
            'statements'
        ));
    }

    public function store(Request $request)
    {
        $settings = BookingSetting::current();
        $user = $request->user();

        if (!$this->canCreateStatement($user, $settings)) {
            abort(403);
        }

        $request->validate([
            'provider_id' => 'required|exists:users,id',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required|in:' . implode(',', array_keys(BookingStatement::getStatuses())),
        ]);

        // Security check: if user is provider and not admin, ensure they are saving for themselves
        if ($this->userIsProvider($user, $settings) && !$this->isAdminUser($user) && !$user->can('booking.statement.manage')) {
             if ($request->input('provider_id') != $user->id) {
                abort(403, 'شما مجاز به ثبت صورت وضعیت برای دیگران نیستید.');
            }
        }

        // Collect roles data
        $statementRoleIds = array_values(array_filter(
            array_map('intval', (array) ($settings->statement_roles ?? [])),
            fn($v) => $v > 0
        ));
        $statementRoles = Role::whereIn('id', $statementRoleIds)->get();
        $rolesData = [];
        foreach ($statementRoles as $role) {
            $inputName = 'role_' . $role->id;
            $userId = $request->input($inputName);
            if ($userId) {
                $rolesData[$role->id] = $userId;
            }
        }

        // Convert Jalali dates to Gregorian for storage if needed, but model casts to date so standard format Y-m-d is expected by Eloquent usually.
        // However, the input is likely Jalali (Y/m/d). We should convert it to Gregorian Y-m-d.
        $startDate = $this->convertJalaliToGregorian($request->input('start_date'));
        $endDate = $this->convertJalaliToGregorian($request->input('end_date'));

        // Calculate first and last appointment times
        $allUserIds = [];
        if ($request->input('provider_id')) {
            $allUserIds[] = $request->input('provider_id');
        }
        foreach ($rolesData as $uId) {
            $allUserIds[] = $uId;
        }
        $allUserIds = array_unique($allUserIds);

        $firstAppointmentTime = null;
        $lastAppointmentTime = null;

        if (!empty($allUserIds)) {
            $result = $this->getAppointments($allUserIds, $request->input('start_date'), $request->input('end_date'));
            if ($result['first']) {
                $firstAppointmentTime = $result['first']->copy()->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('H:i:s');
            }
            if ($result['last']) {
                $lastAppointmentTime = $result['last']->copy()->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('H:i:s');
            }
        }

        $statement = BookingStatement::create([
            'user_id' => $user->id,
            'provider_id' => $request->input('provider_id'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'first_appointment_time' => $firstAppointmentTime,
            'last_appointment_time' => $lastAppointmentTime,
            'status' => $request->input('status'),
            'roles_data' => $rolesData,
            'notes' => $request->input('notes'),
        ]);

        // Trigger Workflows
        $this->triggerWorkflows('statement_created', $statement);
        $this->triggerWorkflows('statement_created_' . $statement->status, $statement);

        // Also trigger the general status event if it's not draft (e.g. created as approved directly)
        if ($statement->status !== 'draft') {
             $this->triggerWorkflows('statement_' . $statement->status, $statement);
        }

        return redirect()->route('user.booking.statement.index')->with('success', 'صورت وضعیت با موفقیت ثبت شد.');
    }

    public function updateStatus(Request $request, BookingStatement $statement)
    {
        $settings = BookingSetting::current();
        $user = $request->user();

        if (!$this->canEditStatement($user, $settings)) {
            abort(403);
        }

        // Security check: providers can only update their own statements unless they have manage permission
        if ($this->userIsProvider($user, $settings) && !$this->isAdminUser($user) && !$user->can('booking.statement.manage')) {
            if ($statement->provider_id != $user->id) {
                abort(403);
            }
        }

        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(BookingStatement::getStatuses())),
        ]);

        $oldStatus = $statement->status;
        $newStatus = $request->input('status');

        $statement->update(['status' => $newStatus]);

        // Trigger Workflows
        if ($oldStatus !== $newStatus) {
            $this->triggerWorkflows('statement_status_changed', $statement);
            $this->triggerWorkflows('statement_' . $newStatus, $statement);
        }

        return redirect()->back()->with('success', 'وضعیت تغییر کرد.');
    }

    public function destroy(BookingStatement $statement)
    {
        $settings = BookingSetting::current();
        $user = request()->user();

        if (!$this->canDeleteStatement($user, $settings)) {
            abort(403);
        }

        // Security check: providers can only delete their own statements unless they have manage permission
        if ($this->userIsProvider($user, $settings) && !$this->isAdminUser($user) && !$user->can('booking.statement.manage')) {
            if ($statement->provider_id != $user->id) {
                abort(403);
            }
        }

        $statement->delete();
        return redirect()->back()->with('success', 'صورت وضعیت حذف شد.');
    }

    public function print(Request $request)
    {
        $settings = BookingSetting::current();
        $user = $request->user();

        if (!$this->canViewStatement($user, $settings)) {
            abort(403);
        }

        // Check if printing from a saved statement
        if ($request->has('statement_id')) {
            $statement = BookingStatement::findOrFail($request->input('statement_id'));

            // Security check for saved statement
            if ($this->userIsProvider($user, $settings) && !$this->isAdminUser($user) && !$user->can('booking.statement.view.all')) {
                if ($statement->provider_id != $user->id) {
                    abort(403);
                }
            }

            $selectedProviderId = $statement->provider_id;
            // Convert stored Y-m-d to Jalali Y/m/d for display/logic
            $startDateLocal = Jalalian::fromCarbon(Carbon::parse($statement->start_date))->format('Y/m/d');
            $endDateLocal = Jalalian::fromCarbon(Carbon::parse($statement->end_date))->format('Y/m/d');

            $rolesData = $statement->roles_data ?? [];
            $selectedUsers = [];

            // Reconstruct selectedUsers from roles_data
            if ($rolesData) {
                foreach ($rolesData as $roleId => $userId) {
                    $u = User::find($userId);
                    if ($u) {
                        $selectedUsers[$roleId] = $u;
                    }
                }
            }

            // Need statementRoles to pass to view
            $statementRoleIds = array_values(array_filter(
                array_map('intval', (array) ($settings->statement_roles ?? [])),
                fn($v) => $v > 0
            ));
            $statementRoles = Role::whereIn('id', $statementRoleIds)->get();

        } else {
            // Standard print logic (existing)
            // 1. Identify Required Roles from Settings
            $statementRoleIds = array_values(array_filter(
                array_map('intval', (array) ($settings->statement_roles ?? [])),
                fn($v) => $v > 0
            ));

            // 2. Get selected users (Optional now)
            $statementRoles = Role::whereIn('id', $statementRoleIds)->get();
            $selectedUsers = [];

            foreach ($statementRoles as $role) {
                $inputName = 'role_' . $role->id;
                $userId = $request->input($inputName);

                if ($userId) {
                    $userObj = User::find($userId);
                    if ($userObj) {
                        $selectedUsers[$role->id] = $userObj;
                    }
                }
            }

            // 3. Prepare Data for PDF
            $selectedProviderId = $request->input('provider_id');

            // Security check for manual print
            if ($this->userIsProvider($user, $settings) && !$this->isAdminUser($user) && !$user->can('booking.statement.view.all')) {
                if ($selectedProviderId != $user->id) {
                     // Force to own ID or abort? Let's force to own ID to be safe, or check if they tried to access another
                     if ($selectedProviderId && $selectedProviderId != $user->id) {
                         abort(403);
                     }
                     $selectedProviderId = $user->id;
                }
            }

            $startDateLocal = $request->input('start_date');
            $endDateLocal = $request->input('end_date');
        }

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
                ->setOption('args', ['--disable-web-security'])
                // Explicitly set node_modules path for Linux server
                ->setNodeModulePath(base_path('node_modules'))
                // Set Chrome path for Linux
                ->setChromePath('/usr/bin/google-chrome');
        }

        $pdf = $browsershot
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->waitUntilNetworkIdle() // Wait for fonts and resources to load
            ->pdf();

        $filenameDate = str_replace('/', '-', $startDateLocal);
        if ($startDateLocal !== $endDateLocal) {
            $filenameDate .= '_to_' . str_replace('/', '-', $endDateLocal);
        }

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="statement_' . $filenameDate . '.pdf"',
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
        if ($this->userIsProvider($user, $settings) && !$this->isAdminUser($user) && !$user->can('booking.statement.view.all')) {
             // Let's check if the requested roleId is one of the allowed provider roles
             $providerRoleIds = array_values(array_filter(
                array_map('intval', (array) ($settings->allowed_roles ?? [])),
                fn($v) => $v > 0
            ));

            if (in_array($roleId, $providerRoleIds)) {
                $usersQuery->where('id', $user->id);
            }
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

        if ($this->userIsProvider($user, $settings) && !$this->isAdminUser($user) && !$user->can('booking.statement.view.all')) {
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
        $settings = BookingSetting::current();
        $user = request()->user();

        // Security check: if user is provider and not admin, ensure they are only fetching their own appointments
        if ($this->userIsProvider($user, $settings) && !$this->isAdminUser($user) && !$user->can('booking.statement.view.all')) {
            // Filter providerIds to only include the user's ID
            $providerIds = array_intersect($providerIds, [$user->id]);

            // If no valid provider ID remains (e.g. they tried to fetch someone else's), force it to their own ID
            // Or return empty if they shouldn't see anything.
            // Since the main logic forces provider_id to be user->id in index(), this array should contain user->id.
            // If it's empty, it means they didn't select themselves (or selected someone else and we filtered it out).
            if (empty($providerIds)) {
                // If we want to be strict: return empty
                // return ['grouped' => collect(), 'first' => null, 'last' => null];

                // If we want to be helpful: default to themselves
                $providerIds = [$user->id];
            }
        }

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

        $first = $appointments->min('start_at_utc');
        $last = $appointments->max('end_at_utc');

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

    protected function convertJalaliToGregorian($jalaliDate)
    {
        try {
            $datePieces = preg_split('/[^\d]+/', trim($jalaliDate));
            if (count($datePieces) < 3) {
                return null;
            }
            [$jy, $jm, $jd] = array_map('intval', array_slice($datePieces, 0, 3));
            [$gy, $gm, $gd] = CalendarUtils::toGregorian($jy, $jm, $jd);
            return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
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
        return $user->can('booking.statement.view') ||
               $user->can('booking.statement.view.own') ||
               $this->isAdminUser($user);
               // REMOVED: || $this->userIsProvider($user, $settings);
    }

    protected function canCreateStatement($user, $settings)
    {
        return $user->can('booking.statement.create') ||
               $this->isAdminUser($user);
               // REMOVED: || $this->userIsProvider($user, $settings);
    }

    protected function canEditStatement($user, $settings)
    {
        return $user->can('booking.statement.edit') ||
               $this->isAdminUser($user);
               // REMOVED: || $this->userIsProvider($user, $settings);
    }

    protected function canDeleteStatement($user, $settings)
    {
        return $user->can('booking.statement.delete') ||
               $this->isAdminUser($user);
               // REMOVED: || $this->userIsProvider($user, $settings);
    }

    protected function isAdminUser($user)
    {
        if (!$user) return false;
        return $user->hasAnyRole(['super-admin', 'admin']) ||
               $user->can('booking.manage') ||
               $user->can('booking.statement.manage');
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

    protected function triggerWorkflows(string $key, BookingStatement $statement)
    {
        if (class_exists('Modules\Workflows\Services\WorkflowEngine')) {
            $engine = app('Modules\Workflows\Services\WorkflowEngine');

            // Prepare payload for tokens
            $payload = [
                'statement_id' => $statement->id,
                'provider_name' => $statement->provider?->name,
                'provider_phone' => $statement->provider?->mobile ?? $statement->provider?->phone,
                'start_date' => Jalalian::fromCarbon(Carbon::parse($statement->start_date))->format('Y/m/d'),
                'end_date' => Jalalian::fromCarbon(Carbon::parse($statement->end_date))->format('Y/m/d'),
                'status' => $statement->status,
                'first_appointment_time' => $statement->first_appointment_time,
                'last_appointment_time' => $statement->last_appointment_time,
                'notes' => $statement->notes,
            ];

            $engine->start($key, 'STATEMENT', $statement->id, $payload);
        }
    }
}
