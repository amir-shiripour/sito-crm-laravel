@php
    /** @var \Modules\Clients\Entities\Client $client */
@endphp

<div class="space-y-6">
    @livewire('booking.user.client-weekly-schedule', ['clientId' => $client->id])
</div>
