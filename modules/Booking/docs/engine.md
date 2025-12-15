# Booking Engine (Slot generation + merge policy)

## Inputs
- service_id
- provider_user_id
- from_local_date / to_local_date
- viewer_timezone (for view-friendly timestamps)

## Merge policy priority
1) GLOBAL rule
2) SERVICE rule (scope_id = service_id)
3) SERVICE_PROVIDER rule (scope_id = booking_service_providers.id)
4) Exceptions (local_date): GLOBAL + SERVICE + SERVICE_PROVIDER

## Null = INHERIT
در ruleها:
- اگر slot_duration_minutes/capacity_per_slot/capacity_per_day null باشد -> از لایه قبلی inherit می‌شود.

## Slot generation
- work window (local) -> split by slot duration
- remove breaks
- convert each slot to UTC
- compute remaining_capacity:
  - start with capacity_per_slot
  - subtract count(appointments consuming capacity in that slot)
  - subtract count(slot_holds not expired in that slot)
- enforce capacity_per_day (remaining per local_date)

## Overbooking prevention
- online: start hold with TTL
- confirm: DB transaction + day lock + slot lock (unique insert) + re-check counts
