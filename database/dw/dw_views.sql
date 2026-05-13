-- Fandaqah PMS Data Warehouse Presentation Views (MS SQL)

-- Daily Stats View
CREATE VIEW vw_daily_stats AS
SELECT 
    d.full_date,
    h.name AS hotel_name,
    h.city,
    f.total_rooms,
    f.occupied_rooms,
    f.available_rooms,
    f.occupancy_pct,
    f.adr,
    f.revpar,
    f.total_revenue
FROM fact_daily_occupancy f
JOIN dim_date d ON f.date_key = d.date_key
JOIN dim_hotel h ON f.hotel_key = h.hotel_key;

-- Reservation Detail View
CREATE VIEW vw_reservation_detail AS
SELECT 
    r.reservation_id,
    d.full_date,
    h.name AS hotel_name,
    g.name AS guest_name,
    g.nationality,
    c.name AS company_name,
    rm.room_number,
    s.name AS source_name,
    r.stay_status,
    r.room_revenue,
    r.service_revenue,
    (r.room_revenue + r.service_revenue) AS total_revenue
FROM fact_reservation_daily r
JOIN dim_date d ON r.date_key = d.date_key
JOIN dim_hotel h ON r.hotel_key = h.hotel_key
JOIN dim_guest g ON r.guest_key = g.guest_key
LEFT JOIN dim_company c ON r.company_key = c.company_key
JOIN dim_room rm ON r.room_key = rm.room_key
JOIN dim_source s ON r.source_key = s.source_key;

-- AR Aging Summary View
CREATE VIEW vw_ar_aging_summary AS
SELECT 
    h.name AS hotel_name,
    c.name AS company_name,
    f.aging_bucket,
    SUM(f.amount_due) AS total_amount_due,
    COUNT(DISTINCT f.invoice_id) AS invoice_count
FROM fact_ar_aging f
JOIN dim_hotel h ON f.hotel_key = h.hotel_key
JOIN dim_company c ON f.company_key = c.company_key
WHERE f.date_key = (SELECT MAX(date_key) FROM fact_ar_aging)
GROUP BY h.name, c.name, f.aging_bucket;

-- Guest Ledger View
CREATE VIEW vw_guest_ledger AS
SELECT 
    h.name AS hotel_name,
    g.name AS guest_name,
    r.reservation_id,
    SUM(r.room_revenue + r.service_revenue) AS total_charges,
    (SELECT SUM(amount) FROM fact_deposits WHERE reservation_id = r.reservation_id) AS total_payments,
    (SUM(r.room_revenue + r.service_revenue) - (SELECT COALESCE(SUM(amount),0) FROM fact_deposits WHERE reservation_id = r.reservation_id)) AS balance
FROM fact_reservation_daily r
JOIN dim_hotel h ON r.hotel_key = h.hotel_key
JOIN dim_guest g ON r.guest_key = g.guest_key
WHERE r.stay_status = 'confirmed'
GROUP BY h.name, g.name, r.reservation_id;

-- Deposit Ledger View
CREATE VIEW vw_deposit_ledger AS
SELECT 
    h.name AS hotel_name,
    d.full_date,
    g.name AS guest_name,
    f.amount,
    f.payment_method,
    f.is_refunded
FROM fact_deposits f
JOIN dim_date d ON f.date_key = d.date_key
JOIN dim_hotel h ON f.hotel_key = h.hotel_key
JOIN dim_guest g ON (SELECT guest_key FROM fact_reservation_daily WHERE reservation_id = f.reservation_id LIMIT 1) = g.guest_key;

-- Trial Balance View
CREATE VIEW vw_trial_balance AS
SELECT 
    h.name AS hotel_name,
    d.full_date,
    SUM(CASE WHEN r.stay_status = 'confirmed' THEN (r.room_revenue + r.service_revenue) ELSE 0 END) AS ledger_balance,
    SUM(r.room_revenue) AS room_revenue,
    SUM(r.service_revenue) AS service_revenue,
    (SELECT SUM(amount) FROM fact_deposits fd WHERE fd.hotel_key = h.hotel_key AND fd.date_key = d.date_key) AS payments_collected
FROM fact_reservation_daily r
JOIN dim_hotel h ON r.hotel_key = h.hotel_key
JOIN dim_date d ON r.date_key = d.date_key
GROUP BY h.name, d.full_date;

-- Cashier Summary View
CREATE VIEW vw_cashier_summary AS
SELECT 
    h.name AS hotel_name,
    d.full_date,
    u.name AS user_name,
    f.total_cash,
    f.total_mada,
    f.total_visa,
    f.discrepancy
FROM fact_cashier_shifts f
JOIN dim_date d ON f.date_key = d.date_key
JOIN dim_hotel h ON f.hotel_key = h.hotel_key
JOIN dim_user u ON f.user_key = u.user_key;

-- Commission Summary View
CREATE VIEW vw_commission_summary AS
SELECT 
    h.name AS hotel_name,
    s.name AS source_name,
    SUM(f.revenue_amount) AS total_revenue,
    SUM(f.commission_amount) AS total_commission,
    f.status
FROM fact_commissions f
JOIN dim_hotel h ON f.hotel_key = h.hotel_key
JOIN dim_source s ON f.source_key = s.source_key
GROUP BY h.name, s.name, f.status;

-- Company Credit Utilization View
CREATE VIEW vw_company_credit_utilization AS
SELECT 
    c.name AS company_name,
    c.credit_limit,
    COALESCE(SUM(f.amount_due), 0) AS current_utilization,
    (c.credit_limit - COALESCE(SUM(f.amount_due), 0)) AS available_credit,
    CASE WHEN c.credit_limit > 0 THEN (COALESCE(SUM(f.amount_due), 0) / c.credit_limit) * 100 ELSE 0 END AS utilization_pct
FROM dim_company c
LEFT JOIN fact_ar_aging f ON c.company_key = f.company_key AND f.date_key = (SELECT MAX(date_key) FROM fact_ar_aging)
GROUP BY c.name, c.credit_limit;
