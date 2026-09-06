<?php

namespace Modules\Wallet\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Clients\Entities\Client;
use Modules\Wallet\App\Enums\TransactionStatus;
use Modules\Wallet\App\Enums\TransactionType;
use Modules\Wallet\App\Models\Wallet;
use Modules\Wallet\App\Models\WalletTransaction;
use Modules\Wallet\App\Services\WalletService;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    public function index(Request $request)
    {
        $this->authorizePermission('wallet.view');

        $query = Wallet::with('holder');

        // 1. Text Search (Wallets + Morph Holders: User & Client)
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($mainQ) use ($search) {
                $mainQ->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhere('id', $search)
                      ->orWhereHasMorph('holder', [User::class, Client::class], function ($holderQ, string $type) use ($search) {
                          if ($type === User::class) {
                              $holderQ->where('name', 'like', "%{$search}%")
                                      ->orWhere('email', 'like', "%{$search}%")
                                      ->orWhere('mobile', 'like', "%{$search}%");
                          } elseif ($type === Client::class) {
                              $holderQ->where('full_name', 'like', "%{$search}%")
                                      ->orWhere('username', 'like', "%{$search}%")
                                      ->orWhere('phone', 'like', "%{$search}%")
                                      ->orWhere('email', 'like', "%{$search}%")
                                      ->orWhere('national_code', 'like', "%{$search}%");
                          }
                      });
            });
        }

        // 2. Holder Type
        if ($request->filled('holder_type')) {
            if ($request->holder_type === 'user') {
                $query->where('holder_type', (new User())->getMorphClass());
            } elseif ($request->holder_type === 'client') {
                $query->where('holder_type', (new Client())->getMorphClass());
            }
        }

        // 3. Status (Active / Inactive)
        if ($request->filled('status')) {
            if ($request->status === 'active' || $request->status === '1') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive' || $request->status === '0') {
                $query->where('is_active', false);
            }
        }

        // 4. Balance Status
        if ($request->filled('balance_status')) {
            if ($request->balance_status === 'positive' || $request->balance_status === 'has_balance') {
                $query->where('balance', '>', 0);
            } elseif ($request->balance_status === 'zero') {
                $query->where('balance', '<=', 0);
            }
        }

        // 5. Min / Max Balance Range
        $cleanNumber = function ($val) {
            if ($val === null || $val === '') {
                return null;
            }
            $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            $num = range(0, 9);
            $converted = str_replace($persian, $num, (string) $val);
            $converted = str_replace($arabic, $num, $converted);
            $cleaned = preg_replace('/[^\d.]/', '', $converted);
            return is_numeric($cleaned) ? (float) $cleaned : null;
        };

        if ($request->filled('min_balance')) {
            $min = $cleanNumber($request->min_balance);
            if ($min !== null) {
                $query->where('balance', '>=', $min);
            }
        }

        if ($request->filled('max_balance')) {
            $max = $cleanNumber($request->max_balance);
            if ($max !== null) {
                $query->where('balance', '<=', $max);
            }
        }

        // 6. Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'balance_desc':
                $query->orderByDesc('balance');
                break;
            case 'balance_asc':
                $query->orderBy('balance');
                break;
            case 'name_asc':
                $query->orderBy('name');
                break;
            case 'name_desc':
                $query->orderByDesc('name');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        // 7. Per-page pagination
        $perPage = in_array((int) $request->input('per_page'), [10, 15, 20, 30, 50, 100], true)
            ? (int) $request->input('per_page')
            : 20;

        $wallets = $query->paginate($perPage)->withQueryString();

        // 8. Overview KPIs / Statistics
        $systemCurrency = $this->walletService->getSystemCurrency();
        $currencyLabel = ($systemCurrency === 'rial' || $systemCurrency === 'IRR') ? 'ریال' : 'تومان';

        $stats = [
            'total_wallets'    => Wallet::count(),
            'active_wallets'   => Wallet::where('is_active', true)->count(),
            'inactive_wallets' => Wallet::where('is_active', false)->count(),
            'total_balance'    => (float) Wallet::where('is_active', true)->sum('balance'),
            'clients_balance'  => (float) Wallet::where('is_active', true)->where('holder_type', (new Client())->getMorphClass())->sum('balance'),
            'users_balance'    => (float) Wallet::where('is_active', true)->where('holder_type', (new User())->getMorphClass())->sum('balance'),
            'positive_count'   => Wallet::where('balance', '>', 0)->count(),
        ];

        return view('wallet::user.index', compact('wallets', 'stats', 'systemCurrency', 'currencyLabel'));
    }

    public function transactions(Request $request)
    {
        $this->authorizePermission('wallet.transactions.view');

        $query = WalletTransaction::with(['wallet.holder', 'payable']);

        // 1. Filter by specific Wallet ID
        $selectedWallet = null;
        if ($request->filled('wallet_id')) {
            $query->where('wallet_id', (int) $request->wallet_id);
            $selectedWallet = Wallet::with('holder')->find((int) $request->wallet_id);
        }

        // 2. Filter by Holder Type
        if ($request->filled('holder_type')) {
            if ($request->holder_type === 'user') {
                $query->whereHas('wallet', function ($wq) {
                    $wq->where('holder_type', (new User())->getMorphClass());
                });
            } elseif ($request->holder_type === 'client') {
                $query->whereHas('wallet', function ($wq) {
                    $wq->where('holder_type', (new Client())->getMorphClass());
                });
            }
        }

        // 3. Smart Full Text Search (UUID, Description, Wallet Name/Slug, Holder User/Client)
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('uuid', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhereHas('wallet', function ($wq) use ($search) {
                      $wq->where('name', 'like', "%{$search}%")
                         ->orWhere('slug', 'like', "%{$search}%")
                         ->orWhereHasMorph('holder', [User::class, Client::class], function ($hq, string $type) use ($search) {
                             if ($type === User::class) {
                                 $hq->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->orWhere('mobile', 'like', "%{$search}%");
                             } elseif ($type === Client::class) {
                                 $hq->where('full_name', 'like', "%{$search}%")
                                    ->orWhere('username', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->orWhere('national_code', 'like', "%{$search}%");
                             }
                         });
                  });
            });
        }

        // 4. Transaction Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // 5. Transaction Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 6. Cash Flow Direction (Inflow / Outflow)
        if ($request->filled('flow')) {
            if ($request->flow === 'inflow') {
                $query->whereIn('type', [
                    TransactionType::DEPOSIT->value,
                    TransactionType::REFUND->value,
                    TransactionType::COMMISSION->value,
                    TransactionType::BONUS->value,
                ]);
            } elseif ($request->flow === 'outflow') {
                $query->whereIn('type', [
                    TransactionType::WITHDRAW->value,
                    TransactionType::PAYMENT->value,
                    TransactionType::TRANSFER->value,
                ]);
            }
        }

        // 7. Amount Range Filters
        $cleanNumber = function ($val) {
            if ($val === null || $val === '') {
                return null;
            }
            $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            $num = range(0, 9);
            $converted = str_replace($persian, $num, (string) $val);
            $converted = str_replace($arabic, $num, $converted);
            $cleaned = preg_replace('/[^\d.]/', '', $converted);
            return is_numeric($cleaned) ? (float) $cleaned : null;
        };

        if ($request->filled('min_amount')) {
            $min = $cleanNumber($request->min_amount);
            if ($min !== null) {
                $query->where('amount', '>=', $min);
            }
        }

        if ($request->filled('max_amount')) {
            $max = $cleanNumber($request->max_amount);
            if ($max !== null) {
                $query->where('amount', '<=', $max);
            }
        }

        // 8. Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'amount_desc':
                $query->orderByDesc('amount');
                break;
            case 'amount_asc':
                $query->orderBy('amount');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        // 9. Statistics Calculation
        $inflowTypes = [
            TransactionType::DEPOSIT->value,
            TransactionType::REFUND->value,
            TransactionType::COMMISSION->value,
            TransactionType::BONUS->value,
        ];
        $outflowTypes = [
            TransactionType::WITHDRAW->value,
            TransactionType::PAYMENT->value,
            TransactionType::TRANSFER->value,
        ];

        $statsQuery = clone $query;
        $totalInflow = (float) (clone $statsQuery)->whereIn('type', $inflowTypes)->sum('amount');
        $totalOutflow = (float) (clone $statsQuery)->whereIn('type', $outflowTypes)->sum('amount');

        $stats = [
            'total_count'       => (clone $statsQuery)->count(),
            'total_inflow'      => $totalInflow,
            'total_outflow'     => $totalOutflow,
            'net_flow'          => $totalInflow - $totalOutflow,
            'completed_count'   => (clone $statsQuery)->where('status', TransactionStatus::COMPLETED->value)->count(),
        ];

        // 10. Per-page Pagination
        $perPage = in_array((int) $request->input('per_page'), [10, 15, 20, 25, 30, 50, 100], true)
            ? (int) $request->input('per_page')
            : 20;

        $transactions = $query->paginate($perPage)->withQueryString();
        $types = TransactionType::cases();
        $statuses = TransactionStatus::cases();
        $systemCurrency = $this->walletService->getSystemCurrency();
        $currencyLabel = ($systemCurrency === 'rial' || $systemCurrency === 'IRR') ? 'ریال' : 'تومان';

        return view('wallet::user.transactions', compact(
            'transactions',
            'types',
            'statuses',
            'systemCurrency',
            'currencyLabel',
            'stats',
            'selectedWallet'
        ));
    }

    public function searchHolders(Request $request)
    {
        if (auth()->check()) {
            $canView = auth()->user()->can('wallet.view') 
                    || auth()->user()->can('wallet.deposit') 
                    || auth()->user()->can('wallet.withdraw');
            if (! $canView) {
                abort(403, 'شما مجاز به انجام این عملیات نمی‌باشید.');
            }
        }

        $query = trim((string) $request->input('q', ''));
        $type = $request->input('type', 'all'); // all, client, user
        $limit = min(50, max(5, (int) $request->input('limit', 20)));

        $sysCurrency = $this->walletService->getSystemCurrency();
        $sysLabel = ($sysCurrency === 'rial' || $sysCurrency === 'IRR') ? 'ریال' : 'تومان';

        $results = [];

        if ($type === 'all' || $type === 'client') {
            $clientsQuery = Client::query();
            if ($query !== '') {
                $clientsQuery->where(function ($q) use ($query) {
                    $q->where('full_name', 'like', "%{$query}%")
                      ->orWhere('username', 'like', "%{$query}%")
                      ->orWhere('phone', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%")
                      ->orWhere('national_code', 'like', "%{$query}%")
                      ->orWhere('id', $query);
                });
            }
            $clients = $clientsQuery->limit($limit)->get();

            foreach ($clients as $client) {
                $wallet = $client->defaultWallet;
                $balance = $wallet ? (float) $wallet->balance : (float) $client->getBalance();
                $currencyCode = $wallet ? $wallet->currency : $sysCurrency;
                $currLabel = ($currencyCode === 'rial' || $currencyCode === 'IRR') ? 'ریال' : 'تومان';

                $results[] = [
                    'id'             => $client->id,
                    'holder_type'    => 'client',
                    'holder_name'    => $client->full_name ?: ($client->username ?: 'کلاینت #' . $client->id),
                    'phone'          => $client->phone ?: '—',
                    'email'          => $client->email ?: '',
                    'badge'          => 'کلاینت',
                    'balance'        => $balance,
                    'currency'       => $currencyCode,
                    'currency_label' => $currLabel,
                    'is_active'      => $wallet ? (bool) $wallet->is_active : true,
                    'wallet_id'      => $wallet?->id,
                ];
            }
        }

        if ($type === 'all' || $type === 'user') {
            $usersQuery = User::query();
            if ($query !== '') {
                $usersQuery->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('mobile', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%")
                      ->orWhere('id', $query);
                });
            }
            $users = $usersQuery->limit($limit)->get();

            foreach ($users as $user) {
                $wallet = $user->defaultWallet;
                $balance = $wallet ? (float) $wallet->balance : (float) $user->getBalance();
                $currencyCode = $wallet ? $wallet->currency : $sysCurrency;
                $currLabel = ($currencyCode === 'rial' || $currencyCode === 'IRR') ? 'ریال' : 'تومان';

                $results[] = [
                    'id'             => $user->id,
                    'holder_type'    => 'user',
                    'holder_name'    => $user->name ?: ('کاربر #' . $user->id),
                    'phone'          => $user->mobile ?: '—',
                    'email'          => $user->email ?: '',
                    'badge'          => 'کاربر سیستم',
                    'balance'        => $balance,
                    'currency'       => $currencyCode,
                    'currency_label' => $currLabel,
                    'is_active'      => $wallet ? (bool) $wallet->is_active : true,
                    'wallet_id'      => $wallet?->id,
                ];
            }
        }

        return response()->json($results);
    }

    public function deposit(Request $request)
    {
        $this->authorizePermission('wallet.deposit');

        if ($request->has('amount')) {
            $rawAmount = (string) $request->input('amount');
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $num = range(0, 9);
            $converted = str_replace($persian, $num, $rawAmount);
            $converted = str_replace($arabic, $num, $converted);
            $cleaned = preg_replace('/[^\d.]/', '', $converted);
            $request->merge(['amount' => $cleaned]);
        }

        $request->validate([
            'holder_type' => 'required|string|in:user,client',
            'holder_id'   => 'required|integer',
            'amount'      => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $holder = $request->holder_type === 'user'
            ? User::findOrFail($request->holder_id)
            : Client::findOrFail($request->holder_id);

        try {
            $this->walletService->deposit(
                holder: $holder,
                amount: (float) $request->amount,
                type: TransactionType::DEPOSIT,
                description: $request->description ?? 'شارژ دستی توسط مدیریت',
                meta: ['admin_user_id' => auth()->id()]
            );

            return redirect()->back()->with('success', 'شارژ کیف پول با موفقیت انجام شد.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'خطا در شارژ کیف پول: ' . $e->getMessage());
        }
    }

    public function withdraw(Request $request)
    {
        $this->authorizePermission('wallet.withdraw');

        if ($request->has('amount')) {
            $rawAmount = (string) $request->input('amount');
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $num = range(0, 9);
            $converted = str_replace($persian, $num, $rawAmount);
            $converted = str_replace($arabic, $num, $converted);
            $cleaned = preg_replace('/[^\d.]/', '', $converted);
            $request->merge(['amount' => $cleaned]);
        }

        $request->validate([
            'holder_type' => 'required|string|in:user,client',
            'holder_id'   => 'required|integer',
            'amount'      => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $holder = $request->holder_type === 'user'
            ? User::findOrFail($request->holder_id)
            : Client::findOrFail($request->holder_id);

        try {
            $this->walletService->withdraw(
                holder: $holder,
                amount: (float) $request->amount,
                type: TransactionType::WITHDRAW,
                description: $request->description ?? 'برداشت دستی توسط مدیریت',
                meta: ['admin_user_id' => auth()->id()]
            );

            return redirect()->back()->with('success', 'برداشت از کیف پول با موفقیت انجام شد.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'خطا در برداشت از کیف پول: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Wallet $wallet)
    {
        $this->authorizePermission('wallet.manage');

        $wallet->is_active = ! $wallet->is_active;
        $wallet->save();

        $statusStr = $wallet->is_active ? 'فعال' : 'غیرفعال';
        return redirect()->back()->with('success', "وضعیت کیف پول با موفقیت {$statusStr} شد.");
    }

    protected function authorizePermission(string $permission): void
    {
        if (auth()->check() && auth()->user()->can($permission)) {
            return;
        }

        abort(403, 'شما مجاز به انجام این عملیات نمی‌باشید.');
    }
}
