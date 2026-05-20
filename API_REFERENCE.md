# Usama Fandaqah API Reference

This document provides information about the key API endpoints available in the Usama Fandaqah application, specifically focusing on the Finance and Accounting modules.

## Authentication

All API requests (except login) require an `Authorization` header with a Bearer token.

```bash
curl -X POST http://127.0.0.1:8000/api/login \
     -H "Content-Type: application/json" \
     -d '{"email": "admin@example.com", "password": "password"}'
```

Response:
```json
{
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

## Finance & Accounting APIs

### 1. Invoice Transfers (Folio to AR)

Manage transfers from guest folios to corporate city ledger.

**List Transfers**
```bash
curl -X GET http://127.0.0.1:8000/api/ar/invoice-transfers \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept: application/json"
```

**Create Transfer**
```bash
curl -X POST http://127.0.0.1:8000/api/ar/invoice-transfers \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
        "reservation_id": 1,
        "company_id": 5,
        "amount": 1500.00,
        "notes": "Moving stay charges to corporate account"
     }'
```

### 2. Invoices

**List Invoices**
```bash
curl -X GET http://127.0.0.1:8000/api/finance/invoices \
     -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Receipts

**List Receipts**
```bash
curl -X GET http://127.0.0.1:8000/api/finance/receipts \
     -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. Dashboards

**Overview Dashboard Data**
```bash
curl -X GET http://127.0.0.1:8000/api/dashboard/overview \
     -H "Authorization: Bearer YOUR_TOKEN"
```

Expected Response Structure:
```json
{
    "metrics": {
        "totalRevenue": 45200,
        "occupancyRate": 85.5,
        "availableRooms": 12,
        "arrivalsToday": 8,
        "departuresToday": 5,
        "inHouseGuests": 42,
        "mtdRevenue": 1250000
    },
    "alerts": [],
    "recentActivity": [],
    "rooms": {
        "occupied": 38,
        "available": 12,
        "maintenance": 2
    },
    "chart": {
        "revenue": [45000, 52000, 48000, 61000, 55000, 58000, 62000],
        "occupancy": [85, 88, 82, 92, 87, 90, 95],
        "dates": ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"]
    }
}
```

## Global JSON Response Standards

All paginated responses follow this standard structure:

```json
{
    "data": [...],
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "path": "...",
        "per_page": 20,
        "to": 20,
        "total": 100
    }
}
```
