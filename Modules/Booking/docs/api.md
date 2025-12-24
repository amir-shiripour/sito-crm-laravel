# API Contracts (نمونه Request/Response)

## 1) Slots

### GET /api/booking/availability/slots

Query:
- service_id (int) *
- provider_id (int) *
- from_local_date (Y-m-d) *
- to_local_date (Y-m-d) *
- viewer_timezone (optional)

Response 200:
```json
{
  "data": [
    {
      "local_date": "2025-12-14",
      "start_at_utc": "2025-12-14T06:30:00Z",
      "end_at_utc": "2025-12-14T07:00:00Z",
      "start_at_view": "2025-12-14T10:00:00+03:30",
      "end_at_view": "2025-12-14T10:30:00+03:30",
      "remaining_capacity": 2,
      "capacity_per_slot": 3,
      "capacity_per_day_remaining": 10
    }
  ]
}
```

## 2) Online booking - Start Hold

### POST /api/booking/appointments/online/start
Body:
```json
{
  "service_id": 10,
  "provider_user_id": 55,
  "start_at_utc": "2025-12-15 08:30:00",
  "end_at_utc": "2025-12-15 09:00:00",
  "client_temp_key": "anon-xyz"
}
```

Response 201:
```json
{
  "data": {
    "hold_id": 123,
    "expires_at_utc": "2025-12-15T08:40:00Z"
  }
}
```

## 3) Online booking - Confirm

### POST /api/booking/appointments/online/confirm
Body:
```json
{
  "hold_id": 123,
  "client": {
    "full_name": "علی رضایی",
    "phone": "0912...",
    "email": "ali@example.com",
    "meta": { "age": 30 }
  },
  "appointment_form_response_json": {
    "fld_123": "..."
  },
  "pay_now": true
}
```

Response 200:
```json
{
  "data": {
    "appointment": {...},
    "payment": {...},
    "gateway": { "payment_url": null, "gateway_ref": null }
  }
}
```

## 4) Operator create appointment

### POST /api/booking/appointments
Body:
```json
{
  "service_id": 10,
  "provider_user_id": 55,
  "client_id": 7,
  "start_at_utc": "2025-12-15 08:30:00",
  "end_at_utc": "2025-12-15 09:00:00",
  "notes": "..."
}
```

## 5) Cancel / Reschedule / No-show

### PATCH /api/booking/appointments/{id}
Body cancel:
```json
{ "action": "cancel", "cancel_status": "CANCELED_BY_ADMIN", "cancel_reason": "..." }
```

Body reschedule:
```json
{ "action": "reschedule", "new_start_at_utc": "2025-12-16 09:00:00", "new_end_at_utc": "2025-12-16 09:30:00" }
```

Body no_show:
```json
{ "action": "no_show" }
```

## 6) Mark payment paid (callback / admin)

### POST /api/booking/payments/{id}/paid
Body:
```json
{ "gateway_ref": "XYZ" }
```

---

## Validation Notes

- from_local_date/to_local_date: در timezone برنامه‌ریزی (Asia/Tehran)
- start_at_utc/end_at_utc: ذخیره و دریافت به UTC
- ظرفیت دقیق با `capacity_per_slot` و `capacity_per_day` اعمال می‌شود
