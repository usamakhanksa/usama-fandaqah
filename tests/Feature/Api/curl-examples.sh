#!/usr/bin/env bash
# API smoke/regression examples for PMS flows.
# Usage: BASE_URL=http://127.0.0.1:8000 TOKEN=... bash tests/Feature/Api/curl-examples.sh

set -euo pipefail
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
TOKEN="${TOKEN:-REPLACE_WITH_BEARER_TOKEN}"
AUTH=(-H "Authorization: Bearer ${TOKEN}" -H "Accept: application/json")
JSON=(-H "Content-Type: application/json")

echo "01 login"
curl -X POST "${BASE_URL}/api/login" "${JSON[@]}" -d '{"email":"admin@fandaqah.test","password":"password"}'

echo "02 dashboard summary"
curl "${BASE_URL}/api/dashboard/summary" "${AUTH[@]}"

echo "03 dashboard analytics"
curl "${BASE_URL}/api/dashboard/customer-analytics" "${AUTH[@]}"

echo "04 dashboard revenue"
curl "${BASE_URL}/api/dashboard/revenue-metrics" "${AUTH[@]}"

echo "05 dashboard unit status"
curl "${BASE_URL}/api/dashboard/unit-status" "${AUTH[@]}"

echo "06 notifications"
curl "${BASE_URL}/api/notifications" "${AUTH[@]}"

echo "07 reservations list"
curl "${BASE_URL}/api/reservations" "${AUTH[@]}"

echo "08 reservations schedule"
curl "${BASE_URL}/api/reservations/schedule" "${AUTH[@]}"

echo "09 reservations availability"
curl "${BASE_URL}/api/rooms/availability" "${AUTH[@]}"

echo "10 reservation apply promo"
curl -X POST "${BASE_URL}/api/reservations/promo/apply" "${AUTH[@]}" "${JSON[@]}" -d '{"code":"WELCOME10"}'

echo "11 reservation draft save"
curl -X POST "${BASE_URL}/api/reservations/drafts" "${AUTH[@]}" "${JSON[@]}" -d '{"guest_name":"John Test"}'

echo "12 reservation draft read"
curl "${BASE_URL}/api/reservations/drafts/REF-1001" "${AUTH[@]}"

echo "13 reservation confirm"
curl -X POST "${BASE_URL}/api/reservations/confirm" "${AUTH[@]}" "${JSON[@]}" -d '{"draft_reference":"REF-1001"}'

echo "14 reservation success payload"
curl "${BASE_URL}/api/reservations/success/1" "${AUTH[@]}"

echo "15 reservation receipt"
curl "${BASE_URL}/api/reservations/receipt/1" "${AUTH[@]}"

echo "16 reservation booking details"
curl "${BASE_URL}/api/reservations/management/1" "${AUTH[@]}"

echo "17 reservation add note"
curl -X POST "${BASE_URL}/api/reservations/management/1/notes" "${AUTH[@]}" "${JSON[@]}" -d '{"note":"Late arrival"}'

echo "18 guests list"
curl "${BASE_URL}/api/guests" "${AUTH[@]}"

echo "19 guests create"
curl -X POST "${BASE_URL}/api/guests" "${AUTH[@]}" "${JSON[@]}" -d '{"first_name":"Jane","last_name":"Doe"}'

echo "20 guests update"
curl -X PUT "${BASE_URL}/api/guests/1" "${AUTH[@]}" "${JSON[@]}" -d '{"first_name":"Janet"}'

echo "21 companies list"
curl "${BASE_URL}/api/companies" "${AUTH[@]}"

echo "22 companies create"
curl -X POST "${BASE_URL}/api/companies" "${AUTH[@]}" "${JSON[@]}" -d '{"name":"Acme Corp"}'

echo "23 companies update"
curl -X PUT "${BASE_URL}/api/companies/1" "${AUTH[@]}" "${JSON[@]}" -d '{"name":"Acme Corp Intl"}'

echo "24 companies save draft"
curl -X POST "${BASE_URL}/api/companies/drafts" "${AUTH[@]}" "${JSON[@]}" -d '{"name":"Draft Co"}'

echo "25 companies latest draft"
curl "${BASE_URL}/api/companies/drafts/latest" "${AUTH[@]}"

echo "26 rooms metrics"
curl "${BASE_URL}/api/rooms/metrics" "${AUTH[@]}"

echo "27 rooms filters"
curl "${BASE_URL}/api/rooms/filters" "${AUTH[@]}"

echo "28 rooms list"
curl "${BASE_URL}/api/rooms" "${AUTH[@]}"

echo "29 rooms create"
curl -X POST "${BASE_URL}/api/rooms" "${AUTH[@]}" "${JSON[@]}" -d '{"number":"101","status":"vacant"}'

echo "30 rooms update"
curl -X PUT "${BASE_URL}/api/rooms/1" "${AUTH[@]}" "${JSON[@]}" -d '{"status":"occupied"}'

echo "31 rooms export"
curl "${BASE_URL}/api/rooms/export" "${AUTH[@]}"

echo "32 units filters"
curl "${BASE_URL}/api/units/filters" "${AUTH[@]}"

echo "33 units floors"
curl "${BASE_URL}/api/units/floors" "${AUTH[@]}"

echo "34 units daily status"
curl "${BASE_URL}/api/units/daily-status" "${AUTH[@]}"

echo "35 units check in"
curl -X POST "${BASE_URL}/api/units/check-in" "${AUTH[@]}" "${JSON[@]}" -d '{"reservation_id":1}'

echo "36 units check out"
curl -X POST "${BASE_URL}/api/units/check-out" "${AUTH[@]}" "${JSON[@]}" -d '{"reservation_id":1}'

echo "37 financial receipts list"
curl "${BASE_URL}/api/financial/receipts" "${AUTH[@]}"

echo "38 financial expenses list"
curl "${BASE_URL}/api/financial/expenses" "${AUTH[@]}"

echo "39 financial draft store"
curl -X POST "${BASE_URL}/api/financial/expense/drafts" "${AUTH[@]}" "${JSON[@]}" -d '{"amount":100}'

echo "40 financial confirm"
curl -X POST "${BASE_URL}/api/financial/expense/confirm" "${AUTH[@]}" "${JSON[@]}" -d '{"draft_id":1}'

echo "41 reports deposits"
curl "${BASE_URL}/api/reports/deposits" "${AUTH[@]}"

echo "42 reports cleaning"
curl "${BASE_URL}/api/reports/cleaning" "${AUTH[@]}"

echo "43 reports maintenance"
curl "${BASE_URL}/api/reports/maintenance" "${AUTH[@]}"

echo "44 global search"
curl "${BASE_URL}/api/search?q=john" "${AUTH[@]}"

echo "45 lead index"
curl "${BASE_URL}/api/leads" "${AUTH[@]}"

echo "46 lead stats"
curl "${BASE_URL}/api/leads/stats" "${AUTH[@]}"

echo "47 update lead"
curl -X PUT "${BASE_URL}/api/leads/1" "${AUTH[@]}" "${JSON[@]}" -d '{"status":"contacted"}'

echo "48 delete lead"
curl -X DELETE "${BASE_URL}/api/leads/1" "${AUTH[@]}"

echo "49 POS stores"
curl "${BASE_URL}/api/pos/stores" "${AUTH[@]}"

echo "50 POS categories"
curl "${BASE_URL}/api/pos/categories" "${AUTH[@]}"

echo "51 POS sub categories"
curl "${BASE_URL}/api/pos/sub-categories" "${AUTH[@]}"

echo "52 POS brands"
curl "${BASE_URL}/api/pos/brands" "${AUTH[@]}"

echo "53 POS products"
curl "${BASE_URL}/api/pos/products" "${AUTH[@]}"

echo "54 POS services"
curl "${BASE_URL}/api/pos/services" "${AUTH[@]}"

echo "55 POS add service"
curl -X POST "${BASE_URL}/api/pos/services" "${AUTH[@]}" "${JSON[@]}" -d '{"name":"Spa"}'

echo "56 POS cart"
curl "${BASE_URL}/api/pos/cart" "${AUTH[@]}"

echo "57 POS cart update"
curl -X POST "${BASE_URL}/api/pos/cart/items" "${AUTH[@]}" "${JSON[@]}" -d '{"product_id":1,"qty":2}'

echo "58 POS cart clear"
curl -X DELETE "${BASE_URL}/api/pos/cart/items" "${AUTH[@]}"

echo "59 POS checkout"
curl -X POST "${BASE_URL}/api/pos/checkout" "${AUTH[@]}" "${JSON[@]}" -d '{"payment_method":"cash"}'

echo "60 setting fetch"
curl "${BASE_URL}/api/settings/general" "${AUTH[@]}"

echo "61 setting update"
curl -X POST "${BASE_URL}/api/settings/global" "${AUTH[@]}" "${JSON[@]}" -d '{"currency":"USD"}'

echo "62 lookups countries"
curl "${BASE_URL}/api/lookups/countries" "${AUTH[@]}"

echo "63 lookups cities"
curl "${BASE_URL}/api/lookups/cities?country=US" "${AUTH[@]}"

echo "64 upload media"
curl -X POST "${BASE_URL}/api/uploads" "${AUTH[@]}" -F "file=@/tmp/example.pdf"

echo "65 public lead submit"
curl -X POST "${BASE_URL}/api/leads/submit" "${JSON[@]}" -d '{"name":"Portal Guest","email":"guest@example.com"}'
