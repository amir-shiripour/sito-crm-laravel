<!-- Quick Client Create Modal -->
<div x-show="clientModalOpen" class="fixed z-20 inset-0 overflow-y-auto" style="display: none;">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="clientModalOpen" @click.away="clientModalOpen = false" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block align-bottom bg-white rounded-lg text-right overflow-hidden shadow-xl transform sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">افزودن سریع مشتری</h3>
                <div class="space-y-4">
                    <div>
                        <label for="new_client_full_name" class="block text-sm font-medium text-gray-700">نام کامل <span class="text-red-500">*</span></label>
                        <input type="text" x-model="newClient.full_name" id="new_client_full_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <template x-if="clientErrors.full_name"><p class="text-red-500 text-xs mt-1" x-text="clientErrors.full_name[0]"></p></template>
                    </div>
                    <div>
                        <label for="new_client_username" class="block text-sm font-medium text-gray-700">نام کاربری (برای ورود) <span class="text-red-500">*</span></label>
                        <input type="text" x-model="newClient.username" id="new_client_username" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <template x-if="clientErrors.username"><p class="text-red-500 text-xs mt-1" x-text="clientErrors.username[0]"></p></template>
                    </div>
                    <div>
                        <label for="new_client_phone" class="block text-sm font-medium text-gray-700">شماره تلفن</label>
                        <input type="text" x-model="newClient.phone" id="new_client_phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <template x-if="clientErrors.phone"><p class="text-red-500 text-xs mt-1" x-text="clientErrors.phone[0]"></p></template>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button @click.prevent="quickStoreClient()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                    ذخیره مشتری
                </button>
                <button @click="clientModalOpen = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                    انصراف
                </button>
            </div>
        </div>
    </div>
</div>
