<?php

namespace Modules\Accounting\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\App\Models\Transaction;
use Modules\Accounting\App\Models\Cheque;
use Modules\Accounting\App\Models\Bank;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. آماده‌سازی کوئری برای تمام اسناد (هزینه، درآمد، انتقالات)
        $documentsQuery = Document::select(
            'id',
            'bank_id',
            'amount',
            'type',
            'document_date as date',
            DB::raw("COALESCE(NULLIF(description, ''), 'سند حسابداری') as description"),
            DB::raw("'document' as source_type"),
            'created_at'
        );

        // 2. آماده‌سازی کوئری برای تمام تراکنش‌ها
        $transactionsQuery = Transaction::select(
            'id',
            'bank_id',
            'amount',
            'type',
            'transaction_date as date',
            DB::raw("COALESCE(NULLIF(description, ''), IF(type = 'income', 'تراکنش درآمد', 'تراکنش هزینه')) as description"),
            DB::raw("'transaction' as source_type"),
            'created_at'
        );

        // 3. آماده‌سازی کوئری برای تمام چک‌های وصول شده
        $chequesQuery = Cheque::select(
            'id',
            'reconciled_bank_id as bank_id',
            'amount',
            DB::raw("IF(type = 'received', 'income', 'expense') as type"),
            'reconciliation_date as date',
            DB::raw("COALESCE(NULLIF(description, ''), CONCAT('چک ', IF(type = 'received', 'دریافتی', 'پرداختی'), ' شماره ', cheque_number)) as description"),
            DB::raw("'cheque' as source_type"),
            'updated_at as created_at' // Using updated_at as it represents when it was reconciled/passed
        )->where('status', 'passed');

        // 4. ترکیب سه کوئری (UNION)، مرتب‌سازی بر اساس تاریخ ثبت/وصول و صفحه‌بندی
        $records = $documentsQuery->union($transactionsQuery)->union($chequesQuery)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc') // مرتب سازی ثانویه برای رکوردهای هم‌تاریخ
            ->paginate(20);

        // 5. واکشی اطلاعات بانک‌ها برای رکوردهای استخراج شده جهت جلوگیری از مشکل N+1
        $bankIds = $records->pluck('bank_id')->unique()->filter();
        $banks = Bank::whereIn('id', $bankIds)->get()->keyBy('id');

        // اتصال بانک به هر رکورد
        foreach ($records as $record) {
            $record->bank = $banks->get($record->bank_id);
        }

        return view('accounting::documents.index', compact('records'));
    }

    // You can add other resource methods (create, store, show, edit, update, destroy) here as needed.
}
