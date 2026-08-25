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

        $query = Wallet::with('holder')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('holder_type')) {
            if ($request->holder_type === 'user') {
                $query->where('holder_type', (new User())->getMorphClass());
            } elseif ($request->holder_type === 'client') {
                $query->where('holder_type', (new Client())->getMorphClass());
            }
        }

        $wallets = $query->paginate(20)->withQueryString();
        $systemCurrency = $this->walletService->getSystemCurrency();
        $currencyLabel = ($systemCurrency === 'rial' || $systemCurrency === 'IRR') ? 'ریال' : 'تومان';

        return view('wallet::user.index', compact('wallets', 'systemCurrency', 'currencyLabel'));
    }

    public function transactions(Request $request)
    {
        $this->authorizePermission('wallet.transactions.view');

        $query = WalletTransaction::with(['wallet.holder', 'payable'])->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('uuid', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(25)->withQueryString();
        $types = TransactionType::cases();
        $statuses = TransactionStatus::cases();
        $systemCurrency = $this->walletService->getSystemCurrency();
        $currencyLabel = ($systemCurrency === 'rial' || $systemCurrency === 'IRR') ? 'ریال' : 'تومان';

        return view('wallet::user.transactions', compact('transactions', 'types', 'statuses', 'systemCurrency', 'currencyLabel'));
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
            $cleaned = preg_replace('/[^\d.]/', '', (string) $request->input('amount'));
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
            $cleaned = preg_replace('/[^\d.]/', '', (string) $request->input('amount'));
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
