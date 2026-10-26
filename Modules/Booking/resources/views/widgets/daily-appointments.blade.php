{{-- modules/Booking/resources/views/widgets/daily-appointments.blade.php --}}
@if(class_exists(\Modules\Booking\Entities\Appointment::class) && \Illuminate\Support\Facades\Schema::hasTable('appointments'))
    <livewire:booking.user.daily-appointments-widget />
@else
    <div class="p-6 text-center text-sm text-slate-500 dark:text-slate-400">
        ماژول نوبت‌دهی در دسترس نیست.
    </div>
@endif
