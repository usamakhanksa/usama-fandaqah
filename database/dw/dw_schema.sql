-- Fandaqah PMS Data Warehouse Schema (MS SQL Server)
-- Dimensions and Fact Tables

-- Dimensions
CREATE TABLE dim_date (
    date_key INT PRIMARY KEY, -- YYYYMMDD
    full_date DATE NOT NULL,
    day_of_week TINYINT,
    day_name VARCHAR(10),
    month TINYINT,
    month_name VARCHAR(10),
    quarter TINYINT,
    year INT,
    is_weekend BIT
);

CREATE TABLE dim_hotel (
    hotel_key INT IDENTITY(1,1) PRIMARY KEY,
    source_id INT NOT NULL, -- team_id from production
    name NVARCHAR(255),
    city NVARCHAR(100),
    country NVARCHAR(100),
    category NVARCHAR(50),
    is_active BIT DEFAULT 1,
    created_at DATETIME2,
    updated_at DATETIME2
);

CREATE TABLE dim_guest (
    guest_key INT IDENTITY(1,1) PRIMARY KEY,
    source_id INT NOT NULL,
    name NVARCHAR(255),
    email NVARCHAR(255),
    phone NVARCHAR(50),
    nationality NVARCHAR(100),
    gender NVARCHAR(20),
    created_at DATETIME2
);

CREATE TABLE dim_company (
    company_key INT IDENTITY(1,1) PRIMARY KEY,
    source_id INT NOT NULL,
    name NVARCHAR(255),
    tax_number NVARCHAR(100),
    credit_limit DECIMAL(18,2),
    created_at DATETIME2
);

CREATE TABLE dim_room (
    room_key INT IDENTITY(1,1) PRIMARY KEY,
    source_id INT NOT NULL,
    room_number NVARCHAR(50),
    hotel_key INT,
    room_type_key INT,
    floor NVARCHAR(10),
    status NVARCHAR(50)
);

CREATE TABLE dim_room_type (
    room_type_key INT IDENTITY(1,1) PRIMARY KEY,
    source_id INT NOT NULL,
    name NVARCHAR(100),
    capacity INT
);

CREATE TABLE dim_source (
    source_key INT IDENTITY(1,1) PRIMARY KEY,
    source_id INT NOT NULL,
    name NVARCHAR(100), -- OTA, Walk-in, Website
    type NVARCHAR(50)
);

CREATE TABLE dim_user (
    user_key INT IDENTITY(1,1) PRIMARY KEY,
    source_id INT NOT NULL,
    name NVARCHAR(255),
    role NVARCHAR(100),
    email NVARCHAR(255)
);

-- Fact Tables
CREATE TABLE fact_reservation_daily (
    fact_id BIGINT IDENTITY(1,1) PRIMARY KEY,
    date_key INT NOT NULL,
    hotel_key INT NOT NULL,
    reservation_id INT NOT NULL,
    guest_key INT,
    company_key INT,
    room_key INT,
    source_key INT,
    stay_status NVARCHAR(50), -- confirmed, stayed, canceled
    room_revenue DECIMAL(18,4),
    service_revenue DECIMAL(18,4),
    tax_amount DECIMAL(18,4),
    is_noshow BIT DEFAULT 0,
    is_canceled BIT DEFAULT 0,
    INDEX idx_res_date (date_key, hotel_key)
);

CREATE TABLE fact_daily_occupancy (
    fact_id BIGINT IDENTITY(1,1) PRIMARY KEY,
    date_key INT NOT NULL,
    hotel_key INT NOT NULL,
    total_rooms INT,
    occupied_rooms INT,
    available_rooms INT,
    maintenance_rooms INT,
    revpar DECIMAL(18,4),
    adr DECIMAL(18,4),
    occupancy_pct DECIMAL(5,2)
);

CREATE TABLE fact_service_revenue (
    fact_id BIGINT IDENTITY(1,1) PRIMARY KEY,
    date_key INT NOT NULL,
    hotel_key INT NOT NULL,
    reservation_id INT,
    service_id INT,
    category_id INT,
    amount DECIMAL(18,4),
    vat_amount DECIMAL(18,4),
    user_key INT
);

CREATE TABLE fact_ar_aging (
    fact_id BIGINT IDENTITY(1,1) PRIMARY KEY,
    date_key INT NOT NULL,
    hotel_key INT NOT NULL,
    company_key INT NOT NULL,
    invoice_id INT,
    amount_due DECIMAL(18,4),
    days_overdue INT,
    aging_bucket NVARCHAR(20) -- 0-30, 31-60, 61-90, 90+
);

CREATE TABLE fact_deposits (
    fact_id BIGINT IDENTITY(1,1) PRIMARY KEY,
    date_key INT NOT NULL,
    hotel_key INT NOT NULL,
    reservation_id INT,
    amount DECIMAL(18,4),
    payment_method NVARCHAR(50),
    is_refunded BIT DEFAULT 0
);

CREATE TABLE fact_commissions (
    fact_id BIGINT IDENTITY(1,1) PRIMARY KEY,
    date_key INT NOT NULL,
    hotel_key INT NOT NULL,
    source_key INT NOT NULL,
    reservation_id INT,
    revenue_amount DECIMAL(18,4),
    commission_amount DECIMAL(18,4),
    status NVARCHAR(50)
);

CREATE TABLE fact_cashier_shifts (
    fact_id BIGINT IDENTITY(1,1) PRIMARY KEY,
    date_key INT NOT NULL,
    hotel_key INT NOT NULL,
    user_key INT NOT NULL,
    shift_start DATETIME2,
    shift_end DATETIME2,
    opening_balance DECIMAL(18,4),
    closing_balance DECIMAL(18,4),
    total_cash DECIMAL(18,4),
    total_mada DECIMAL(18,4),
    total_visa DECIMAL(18,4),
    discrepancy DECIMAL(18,4)
);

-- Support Tables
CREATE TABLE dw_groups (
    group_id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(255)
);

CREATE TABLE dw_hotel_group_mapping (
    hotel_key INT,
    group_id INT,
    PRIMARY KEY (hotel_key, group_id)
);

CREATE TABLE dw_service_category_classification (
    source_category_id INT PRIMARY KEY,
    revenue_bucket NVARCHAR(50), -- Room, F&B, Laundry, etc.
    is_vat_applicable BIT
);

CREATE TABLE etl_watermark (
    table_name NVARCHAR(100) PRIMARY KEY,
    last_load_timestamp DATETIME2,
    last_load_id BIGINT
);

CREATE TABLE etl_run_log (
    run_id UNIQUEIDENTIFIER PRIMARY KEY DEFAULT NEWID(),
    dag_name NVARCHAR(100),
    start_time DATETIME2,
    end_time DATETIME2,
    status NVARCHAR(20), -- Success, Failure
    records_processed INT,
    error_message NVARCHAR(MAX)
);
