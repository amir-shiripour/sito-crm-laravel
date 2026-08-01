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

        return view('wallet::user.index', compact('wallets'));
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

        return view('wallet::user.transactions', compact('transactions', 'types', 'statuses'));
    }

    public function deposit(Request $request)
    {
        $this->authorizePermission('wallet.deposit');

        $request->validate([
            'holder_type' => 'required|string|in:user,client',
            'holder_id'   => 'required|integer',
            'amount'      => 'required|numeric|min:1000',
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

        $request->validate([
            'holder_type' => 'required|string|in:user,client',
            'holder_id'   => 'required|integer',
            'amount'      => 'required|numeric|min:1000',
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
