<?php

namespace Modules\FieldService\App\Livewire\Technician;

use Livewire\Component;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\BackupRequest;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Services\DispatchRadarService;
use Modules\FieldService\App\Services\FieldServiceWorkflowBridgeService;

class MissionFeed extends Component
{
    public ?int $technicianId = null;
    public bool $showBackupModal = false;
    public ?int $backupOrderId = null;
    public string $backupReason = 'heavy_lifting';
    public string $backupNotes = '';

    // Request Review & Details Modal State
    public ?int $previewOrderId = null;
    public array $dismissedOrderIds = [];

    // Cancellation Modal State
    public bool $showCancelModal = false;
    public string $cancelAction = 'redispatch';
    public string $cancelReason = 'vehicle_breakdown';
    public string $cancelNote = '';

    public function getListeners()
    {
        return [
            'technician-authenticated'                          => 'refreshTechnician',
            'refresh-mission-feed'                              => '$refresh',
            'order-pin-clicked'                                 => 'selectMissionFromMap',
            'echo:fieldservice.fleet,ServiceOrderCreated'       => 'onServiceOrderCreated',
            'echo:fieldservice.fleet,.ServiceOrderCreated'      => 'onServiceOrderCreated',
            'echo:fieldservice.fleet,ServiceOrderStatusChanged'  => 'onServiceOrderStatusChanged',
            'echo:fieldservice.fleet,.ServiceOrderStatusChanged' => 'onServiceOrderStatusChanged',
        ];
    }

    public function onServiceOrderCreated(array $payload)
    {
        $tech = Technician::find($this->technicianId);
        if (!$tech || !$tech->is_online || $tech->is_busy) {
            return;
        }

        // Check if category matches technician skills
        if (!empty($payload['category_id']) && !empty($tech->skills)) {
            $catId = (int) $payload['category_id'];
            if (!in_array($catId, (array) $tech->skills, true)) {
                return;
            }
        }

        // Check city
        if (!empty($payload['city']) && !empty($tech->city) && $payload['city'] !== $tech->city) {
            return;
        }

        // Check distance if coordinates present
        if (!empty($payload['lat']) && !empty($payload['lng']) && $tech->lat && $tech->lng) {
            $dist = self::calculateDistance((float)$tech->lat, (float)$tech->lng, (float)$payload['lat'], (float)$payload['lng']);
            $maxRadius = (float) ($tech->operational_radius_km ?: 25);
            if ($dist > $maxRadius) {
                return;
            }
            $payload['distance_km'] = round($dist, 1);
        }

        // Dispatch to map
        $this->dispatch('new-mission-received-on-map', $payload);

        // Notify technician
        $this->dispatch('notify', [
            'type' => 'info',
            'text' => "سفارش جدید #{$payload['order_number']} در محدوده کاری شما ثبت شد!",
        ]);
    }

    public function onServiceOrderStatusChanged(array $payload)
    {
        $orderId = (int) ($payload['order_id'] ?? 0);
        $newStatus = $payload['status'] ?? '';
        $assignedTechId = isset($payload['technician_id']) ? (int) $payload['technician_id'] : null;

        if ($newStatus === 'cancelled') {
            if ($assignedTechId === (int) $this->technicianId) {
                // This technician's active mission was cancelled!
                $this->previewOrderId = null;
                $this->dispatch('mission-cancelled-by-admin');
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'text' => "ماموریت #{$payload['order_number']} توسط مدیریت یا مشتری لغو گردید.",
                ]);
            } else {
                $this->dispatch('mission-removed-from-map', ['orderId' => $orderId]);
            }
        } elseif (in_array($newStatus, ['accepted', 'en_route', 'arrived', 'in_progress', 'completed'])) {
            if ($assignedTechId !== (int) $this->technicianId) {
                if ($this->previewOrderId === $orderId) {
                    $this->previewOrderId = null;
                }
                $this->dispatch('mission-removed-from-map', ['orderId' => $orderId]);
            }
        }
    }

    public function heartbeat()
    {
        // Periodic sync trigger for polling fallback
        $this->refreshTechnician();
    }

    public function mount(?int $technicianId = null)
    {
        if (!$technicianId && auth()->check()) {
            $tech = Technician::where('user_id', auth()->id())->first();
            $technicianId = $tech?->id;
        }

        $this->technicianId = $technicianId;
    }

    public function refreshTechnician()
    {
        if (auth()->check()) {
            $tech = Technician::where('user_id', auth()->id())->first();
            $this->technicianId = $tech?->id;
        }
    }

    public function focusMission(int $orderId)
    {
        $this->previewOrderId = $orderId;
        $order = ServiceOrder::with('category')->find($orderId);

        if ($order && $order->lat && $order->lng) {
            $this->dispatch('focus-mission-on-map', [
                'orderId'  => $order->id,
                'lat'      => (float) $order->lat,
                'lng'      => (float) $order->lng,
                'title'    => $order->category?->name ?: $order->customer_name,
                'distance' => $order->distance_km ?? null,
            ]);
        }
    }

    public function selectMissionFromMap(int $orderId)
    {
        $this->previewOrderId = $orderId;
        $this->dispatch('mission-card-selected', ['orderId' => $orderId]);
    }

    public function viewOrderDetails(int $orderId)
    {
        $this->focusMission($orderId);
    }

    public function closeOrderDetails()
    {
        $this->previewOrderId = null;
        $this->dispatch('clear-mission-focus-on-map');
    }

    public function dismissOrder(int $orderId)
    {
        if (!in_array($orderId, $this->dismissedOrderIds, true)) {
            $this->dismissedOrderIds[] = $orderId;
        }

        if ($this->previewOrderId === $orderId) {
            $this->previewOrderId = null;
        }

        $this->dispatch('mission-dismissed-from-map', ['orderId' => $orderId]);

        $this->dispatch('notify', [
            'type' => 'info',
            'text' => 'سفارش از لیست پیشنهادات فعال شما کنار گذاشته شد.',
        ]);
    }

    public function acceptMission(int $orderId)
    {
        $order = ServiceOrder::find($orderId);
        $tech = Technician::find($this->technicianId);

        if ($order && $tech) {
            $radar = app(DispatchRadarService::class);
            if ($radar->acceptOrder($order, $tech)) {
                $this->previewOrderId = null;

                $this->dispatch('mission-accepted', [
                    'orderId'  => $order->id,
                    'lat'      => (float) $order->lat,
                    'lng'      => (float) $order->lng,
                    'address'  => $order->address,
                    'customer' => $order->customer_name,
                    'phone'    => $order->customer_phone,
                ]);

                $this->dispatch('notify', [
                    'type' => 'success',
                    'text' => "سرویس #{$order->order_number} با موفقیت توسط شما پذیرفته شد.",
                ]);
            }
        }
    }

    public function startRoute(int $orderId)
    {
        $order = ServiceOrder::find($orderId);
        if ($order) {
            $order->status = OrderStatus::EN_ROUTE;
            $order->en_route_at = now();
            $order->save();

            $this->dispatch('mission-route-started', [
                'orderId' => $order->id,
                'lat'     => (float) $order->lat,
                'lng'     => (float) $order->lng,
            ]);

            $this->dispatch('notify', [
                'type' => 'info',
                'text' => 'وضعیت به «در مسیر به سمت مشتری» تغییر یافت. مسیریابی فعال شد.',
            ]);
        }
    }

    public function markArrived(int $orderId)
    {
        $order = ServiceOrder::find($orderId);
        if ($order) {
            $order->status = OrderStatus::ARRIVED;
            $order->arrived_at = now();
            $order->save();

            app(FieldServiceWorkflowBridgeService::class)->triggerTechnicianArrived($order);

            $this->dispatch('mission-arrived', [
                'orderId' => $order->id,
                'lat'     => (float) $order->lat,
                'lng'     => (float) $order->lng,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => 'اعلام حضور در محل مشتری ثبت و پیامک اطلاع‌رسانی ارسال گردید.',
            ]);
        }
    }

    public function completeOrder(int $orderId)
    {
        $order = ServiceOrder::find($orderId);
        if ($order) {
            $order->status = OrderStatus::COMPLETED;
            $order->completed_at = now();
            $order->save();

            $tech = Technician::find($this->technicianId);
            if ($tech) {
                $tech->increment('completed_orders_count');
                $tech->is_busy = false;
                $tech->save();
            }

            $this->dispatch('mission-completed');

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => "ماموریت #{$order->order_number} با موفقیت خاتمه یافت و در سوابق امروز ثبت شد.",
            ]);
        }
    }

    public function openBackupModal(int $orderId)
    {
        $this->backupOrderId = $orderId;
        $this->showBackupModal = true;
    }

    public function submitBackupRequest()
    {
        if (!$this->backupOrderId) return;

        $order = ServiceOrder::find($this->backupOrderId);
        $tech = Technician::find($this->technicianId);

        if ($order && $tech) {
            $backup = BackupRequest::create([
                'order_id'              => $order->id,
                'primary_technician_id' => $tech->id,
                'reason'                => $this->backupReason,
                'notes'                 => $this->backupNotes,
                'status'                => 'pending',
                'requested_at'          => now(),
            ]);

            app(FieldServiceWorkflowBridgeService::class)->triggerBackupRequested($backup);

            $this->showBackupModal = false;
            $this->reset(['backupReason', 'backupNotes']);

            $this->dispatch('notify', [
                'type' => 'success',
                'text' => 'درخواست اعزام تکنسین کمکی در رادار ارسال شد.',
            ]);
        }
    }

    public function openCancelModal(): void
    {
        $this->cancelAction = 'redispatch';
        $this->cancelReason = 'vehicle_breakdown';
        $this->cancelNote = '';
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
    }

    public function confirmTechnicianCancel(): void
    {
        $tech = Technician::find($this->technicianId);
        if (!$tech) {
            return;
        }

        $order = ServiceOrder::where('technician_id', $tech->id)
            ->whereIn('status', [
                OrderStatus::ACCEPTED,
                OrderStatus::EN_ROUTE,
            ])
            ->latest()
            ->first();

        if (!$order) {
            $this->showCancelModal = false;
            return;
        }

        $reasons = ServiceOrder::technicianCancellationReasons();
        $reasonTitle = $reasons[$this->cancelReason] ?? 'انصراف تکنسین';
        $fullNote = $reasonTitle . (!empty($this->cancelNote) ? ' - ' . trim($this->cancelNote) : '');

        if ($this->cancelAction === 'redispatch') {
            // 1. Release technician and re-queue order in radar for other technicians
            $order->technician_id = null;
            $order->status = OrderStatus::SEARCHING;
            $order->dispatched_at = now();
            $meta = $order->meta ?? [];
            $meta['unassigned_history'][] = [
                'technician_id'   => $tech->id,
                'technician_name' => $tech->full_name,
                'reason'          => $fullNote,
                'at'              => now()->toIso8601String(),
            ];
            $order->meta = $meta;
            $order->save();

            $tech->is_busy = false;
            $tech->save();

            $this->previewOrderId = null;
            $this->showCancelModal = false;

            // Clear route line and focus on map
            $this->dispatch('mission-cancelled-by-admin');

            $this->dispatch('notify', [
                'type' => 'info',
                'text' => "ماموریت #{$order->order_number} از کارتابل شما آزاد و مجدداً به رادار همکاران واگذار شد.",
            ]);
        } else {
            // 2. Full cancellation
            $order->status = OrderStatus::CANCELLED;
            $order->cancelled_at = now();
            $order->cancellation_reason = "لغو توسط تکنسین: {$fullNote}";
            $meta = $order->meta ?? [];
            $meta['cancelled_by'] = 'technician';
            $meta['cancel_reason_key'] = $this->cancelReason;
            $order->meta = $meta;
            $order->save();

            $tech->is_busy = false;
            $tech->save();

            $this->previewOrderId = null;
            $this->showCancelModal = false;

            $this->dispatch('mission-cancelled-by-admin');

            $this->dispatch('notify', [
                'type' => 'warning',
                'text' => "سفارش #{$order->order_number} به عنوان لغو نهایی ثبت گردید.",
            ]);
        }
    }

    private static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 1);
    }

    public function render()
    {
        $tech = $this->technicianId ? Technician::find($this->technicianId) : null;

        $availableMissions = collect();
        $activeMission = null;
        $previewOrder = $this->previewOrderId ? ServiceOrder::with(['client', 'category'])->find($this->previewOrderId) : null;

        if ($tech) {
            // Only search for available missions if technician is online
            if ($tech->is_online) {
                $techCategoryIds = $tech->categories()->pluck('field_service_categories.id')->toArray();

                $query = ServiceOrder::whereIn('status', [OrderStatus::SEARCHING, OrderStatus::DISPATCHED])
                    ->whereNull('technician_id')
                    ->with(['client', 'category'])
                    ->latest();

                if (!empty($this->dismissedOrderIds)) {
                    $query->whereNotIn('id', $this->dismissedOrderIds);
                }

                if (!empty($techCategoryIds)) {
                    $query->whereIn('category_id', $techCategoryIds);
                }

                if (!empty($tech->city)) {
                    $query->where(function ($q) use ($tech) {
                        $q->where('city', $tech->city)
                          ->orWhereNull('city');
                    });
                }

                $availableMissions = $query->take(12)->get();

                // Calculate distance for each available mission and filter by operational radius
                $techLat = (float) $tech->lat ?: 36.5444;
                $techLng = (float) $tech->lng ?: 52.6780;
                $maxRadius = (int) ($tech->operational_radius_km ?: 25);

                $availableMissions = $availableMissions->map(function ($order) use ($techLat, $techLng) {
                    if ($order->lat && $order->lng) {
                        $order->distance_km = self::calculateDistance($techLat, $techLng, (float) $order->lat, (float) $order->lng);
                    } else {
                        $order->distance_km = null;
                    }
                    return $order;
                })->filter(function ($order) use ($maxRadius) {
                    return is_null($order->distance_km) || $order->distance_km <= $maxRadius;
                })->take(8)->values();
            }

            $activeMission = ServiceOrder::where('technician_id', $tech->id)
                ->whereIn('status', [
                    OrderStatus::ACCEPTED,
                    OrderStatus::EN_ROUTE,
                    OrderStatus::ARRIVED,
                    OrderStatus::IN_PROGRESS,
                    OrderStatus::INVOICE_PENDING
                ])
                ->with(['client', 'category', 'invoice.items'])
                ->latest()
                ->first();
        }

        $activeDist = null;
        $activeEta = null;
        if ($activeMission && $activeMission->lat && $activeMission->lng) {
            $tLat = (float) ($tech?->lat ?: 36.5444);
            $tLng = (float) ($tech?->lng ?: 52.6780);
            $routeData = app(\Modules\FieldService\App\Services\RoutingService::class)->getRoute(
                $tLat,
                $tLng,
                (float) $activeMission->lat,
                (float) $activeMission->lng
            );
            $activeDist = $routeData['distance_km'] ?? null;
            $activeEta = $routeData['eta_minutes'] ?? null;
            $activeMission->distance_km = $activeDist;
            $activeMission->eta_minutes = $activeEta;
        }

        $missionsPayload = $availableMissions->map(function ($m) {
            return [
                'id'            => $m->id,
                'order_number'  => $m->order_number,
                'lat'           => (float) $m->lat,
                'lng'           => (float) $m->lng,
                'is_emergency'  => (bool) $m->is_emergency,
                'category_name' => $m->category?->name ?: 'سفارش',
                'customer_name' => $m->customer_name,
                'distance_km'   => $m->distance_km ?? null,
            ];
        })->values()->toArray();

        // Synchronize outer Alpine peek bar and Leaflet map with latest mission state & markers
        $this->dispatch('active-mission-state-changed', [
            'hasActiveMission' => (bool) $activeMission,
            'title'            => $activeMission ? ($activeMission->category?->name ?: $activeMission->customer_name) : '',
            'status'           => $activeMission ? $activeMission->status->label() : '',
            'statusCode'       => $activeMission ? $activeMission->status->value : '',
            'city'             => $activeMission ? $activeMission->city : '',
            'customerName'     => $activeMission ? $activeMission->customer_name : '',
            'customerPhone'    => $activeMission ? $activeMission->customer_phone : '',
            'destLat'          => $activeMission ? (float) $activeMission->lat : null,
            'destLng'          => $activeMission ? (float) $activeMission->lng : null,
            'distanceKm'       => $activeDist,
            'etaMinutes'       => $activeEta,
            'etaText'          => $activeEta ? ('حدود ' . $activeEta . ' دقیقه') : '',
            'availableCount'   => $availableMissions->count(),
            'missions'         => $missionsPayload,
        ]);

        return view('fieldservice::livewire.technician.mission-feed', [
            'tech'                          => $tech,
            'availableMissions'             => $availableMissions,
            'activeMission'                 => $activeMission,
            'previewOrder'                  => $previewOrder,
            'technicianCancellationReasons' => ServiceOrder::technicianCancellationReasons(),
        ]);
    }
}
