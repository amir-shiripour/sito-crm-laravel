@extends('layouts.user')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        ثبت تراکنش جدید
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <form action="{{ route('admin.accounting.transactions.store') }}" method="POST" x-data="transactionForm()">
                    @csrf
                    <input type="hidden" name="type" value="transfer">

                    <div class="p-6 sm:px-20 bg-white">

                        @if($errors->any())
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <strong class="font-bold">خطا!</strong>
                                <ul class="mt-2 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- From Bank -->
                            <div>
                                <label for="from_bank_id" class="block font-medium text-sm text-gray-700">از حساب (مبدا)</label>
                                <select name="from_bank_id" id="from_bank_id" x-model="fromBankId" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">انتخاب کنید...</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}" {{ old('from_bank_id') == $bank->id ? 'selected' : '' }}>{{ $bank->bank_name }} - {{ $bank->account_number }}</option>
                                    @endforeach
                                </select>
                                <p x-show="fromBankBalance !== null" class="mt-1 text-sm text-gray-500" style="display: none;">
                                    موجودی فعلی: <span x-text="fromBankBalance" class="font-bold text-indigo-600"></span>
                                </p>
                            </div>

                            <!-- To Bank -->
                            <div>
                                <label for="to_bank_id" class="block font-medium text-sm text-gray-700">به حساب (مقصد)</label>
                                <select name="to_bank_id" id="to_bank_id" x-model="toBankId" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">انتخاب کنید...</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}" {{ old('to_bank_id') == $bank->id ? 'selected' : '' }}>{{ $bank->bank_name }} - {{ $bank->account_number }}</option>
                                    @endforeach
                                </select>
                                <p x-show="toBankBalance !== null" class="mt-1 text-sm text-gray-500" style="display: none;">
                                    موجودی فعلی: <span x-text="toBankBalance" class="font-bold text-indigo-600"></span>
                                </p>
                            </div>

                            <!-- Amount -->
                            <div class="md:col-span-2">
                                <label for="amount_display" class="block font-medium text-sm text-gray-700">مبلغ</label>
                                <input type="text" id="amount_display" x-model="formattedAmount" @input="formatAmount($event)" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <input type="hidden" name="amount" x-model="rawAmount">
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label for="description" class="block font-medium text-sm text-gray-700">توضیحات (الزامی می باشد)</label>
                                <textarea name="description" id="description" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-left sm:px-6">
                        <a href="{{ route('admin.accounting.transactions.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition ml-4">
                            انصراف
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                            ثبت تراکنش
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
function transactionForm() {
    return {
        fromBankId: '{{ old('from_bank_id') }}',
        toBankId: '{{ old('to_bank_id') }}',
        banks: {!! json_encode($banks->map(function($bank) {
            return [
                'id' => $bank->id,
                'balance' => $bank->current_balance
            ];
        })->keyBy('id')) !!},
        rawAmount: '{{ old('amount', 0) }}',
        formattedAmount: '{{ old('amount') ? number_format((float) old('amount')) : '' }}',

        get fromBankBalance() {
            if (this.fromBankId && this.banks[this.fromBankId]) {
                return this.banks[this.fromBankId].balance;
            }
            return null;
        },

        get toBankBalance() {
            if (this.toBankId && this.banks[this.toBankId]) {
                return this.banks[this.toBankId].balance;
            }
            return null;
        },

        formatAmount(event) {
            let value = event.target.value;
            const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            const arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            value = value.replace(/[۰-۹]/g, c => persian.indexOf(c).toString());
            value = value.replace(/[٠-٩]/g, c => arabic.indexOf(c).toString());

            let raw = value.replace(/[^0-9]/g, '');
            this.rawAmount = raw;

            if (raw) {
                this.formattedAmount = parseInt(raw, 10).toLocaleString('en-US');
            } else {
                this.formattedAmount = '';
            }
        }
    }
}
</script>
@endsection
