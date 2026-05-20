/**
 * Fandaqah Hotel PMS — Comprehensive Mock Data Service
 * Provides realistic sample data for all modules when API is unavailable.
 */

import dayjs from 'dayjs';

// ─── Helpers ──────────────────────────────────────────────
const rand = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;
const pick = (arr) => arr[rand(0, arr.length - 1)];
const money = (min, max) => +(Math.random() * (max - min) + min).toFixed(2);
const dateStr = (daysAgo = 0) => dayjs().subtract(daysAgo, 'day').format('YYYY-MM-DD');
const dateTimeStr = (daysAgo = 0, hoursAgo = 0) => dayjs().subtract(daysAgo, 'day').subtract(hoursAgo, 'hour').format('YYYY-MM-DD HH:mm:ss');
const padNum = (n, len = 4) => String(n).padStart(len, '0');

// ─── Constants ────────────────────────────────────────────
const GUEST_NAMES = [
  'Ahmed Al-Rashidi', 'Fatima Al-Zahrani', 'Mohammed Al-Harbi', 'Noura Al-Otaibi',
  'Khalid Al-Shehri', 'Sara Al-Ghamdi', 'Abdullah Al-Qahtani', 'Maha Al-Dosari',
  'Omar Al-Maliki', 'Huda Al-Yami', 'Faisal Al-Mutairi', 'Reem Al-Subaie',
  'Turki Al-Dawsari', 'Layla Al-Shahrani', 'Mansour Al-Tamimi', 'Amira Al-Harthy',
  'Sultan Al-Enezi', 'Dina Al-Jubeir', 'Nasser Al-Shamrani', 'Ghada Al-Rajhi',
  'John Smith', 'Emma Wilson', 'James Brown', 'Sarah Davis', 'Michael Johnson',
  'David Lee', 'Lisa Chen', 'Robert Garcia', 'Maria Rodriguez', 'Thomas Anderson'
];

const COMPANY_NAMES = [
  'Saudi Aramco', 'SABIC Industries', 'Al Rajhi Group', 'Saudi Airlines',
  'Hilton Corporate', 'NEOM Development', 'Red Sea Global', 'Riyadh Air',
  'Saudi Telecom (STC)', 'Ma\'aden Mining', 'ACWA Power', 'Almarai Company',
  'Jabal Omar Development', 'Dar Al Arkan', 'Emaar Properties'
];

const SOURCES = [
  { id: 1, name: 'Direct', type: 'direct' },
  { id: 2, name: 'Booking.com', type: 'ota' },
  { id: 3, name: 'Expedia', type: 'ota' },
  { id: 4, name: 'Al Tayyar Travel', type: 'travel_agent', commission_rate: 10 },
  { id: 5, name: 'Almosafer', type: 'travel_agent', commission_rate: 12 },
  { id: 6, name: 'Website', type: 'online' },
  { id: 7, name: 'Walk-in', type: 'walk_in' },
  { id: 8, name: 'Corporate', type: 'corporate' },
  { id: 9, name: 'Agoda', type: 'ota' },
  { id: 10, name: 'Cleartrip', type: 'travel_agent', commission_rate: 8 },
];

const ROOM_TYPES = [
  { id: 1, name: 'Standard Single', name_ar: 'غرفة فردية عادية', base_rate: 350, capacity: 1 },
  { id: 2, name: 'Standard Double', name_ar: 'غرفة مزدوجة عادية', base_rate: 450, capacity: 2 },
  { id: 3, name: 'Deluxe King', name_ar: 'غرفة ديلوكس كينج', base_rate: 650, capacity: 2 },
  { id: 4, name: 'Executive Suite', name_ar: 'جناح تنفيذي', base_rate: 1200, capacity: 3 },
  { id: 5, name: 'Royal Suite', name_ar: 'الجناح الملكي', base_rate: 2500, capacity: 4 },
  { id: 6, name: 'Family Room', name_ar: 'غرفة عائلية', base_rate: 800, capacity: 4 },
];

const FLOORS = [
  { id: 1, name: 'Ground Floor', number: 0 },
  { id: 2, name: 'First Floor', number: 1 },
  { id: 3, name: 'Second Floor', number: 2 },
  { id: 4, name: 'Third Floor', number: 3 },
  { id: 5, name: 'Fourth Floor', number: 4 },
  { id: 6, name: 'Penthouse', number: 5 },
];

const PAYMENT_METHODS = ['cash', 'visa', 'mastercard', 'mada', 'apple_pay', 'bank_transfer', 'cheque', 'online'];
const CURRENCIES = ['SAR', 'USD', 'EUR', 'GBP'];
const ROOM_STATUSES = ['available', 'occupied', 'cleaning', 'maintenance', 'out_of_order'];
const RESERVATION_STATUSES = ['confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'];
const INVOICE_STATUSES = ['draft', 'sent', 'confirmed', 'paid', 'partially_paid', 'cancelled'];
const ZATCA_STATUSES = ['not_reported', 'pending', 'reported', 'accepted', 'rejected'];
const SHIFT_STATUSES = ['open', 'closed', 'approved'];

// ─── Generators ───────────────────────────────────────────

function generateId() {
  return rand(1000, 99999);
}

function generateGuests(count = 30) {
  return Array.from({ length: count }, (_, i) => ({
    id: i + 1,
    name: GUEST_NAMES[i % GUEST_NAMES.length],
    name_ar: GUEST_NAMES[i % GUEST_NAMES.length], // Simplified
    email: GUEST_NAMES[i % GUEST_NAMES.length].toLowerCase().replace(/[^a-z]/g, '.').replace(/\.+/g, '.') + '@email.com',
    phone: `+966${rand(500000000, 599999999)}`,
    nationality: pick(['SA', 'AE', 'EG', 'US', 'GB', 'IN', 'PK', 'JO']),
    id_type: pick(['national_id', 'passport', 'iqama']),
    id_number: `${rand(1000000000, 2999999999)}`,
    vip: i < 5,
    gender: i % 3 === 0 ? 'female' : 'male',
    total_stays: rand(1, 20),
    total_revenue: money(500, 50000),
    last_visit: dateStr(rand(0, 90)),
    created_at: dateTimeStr(rand(30, 365)),
  }));
}

function generateCompanies(count = 15) {
  return COMPANY_NAMES.slice(0, count).map((name, i) => ({
    id: i + 1,
    name,
    name_ar: name,
    email: name.toLowerCase().replace(/[^a-z]/g, '') + '@corp.sa',
    phone: `+966${rand(110000000, 119999999)}`,
    tax_number: `3${rand(10000000, 99999999)}00003`,
    credit_limit: pick([50000, 100000, 250000, 500000, 1000000]),
    current_balance: money(0, 200000),
    payment_terms_days: pick([15, 30, 45, 60, 90]),
    company_group_id: i < 5 ? 1 : i < 10 ? 2 : 3,
    is_active: true,
    contact_person: pick(GUEST_NAMES),
    city: pick(['Riyadh', 'Jeddah', 'Dammam', 'Makkah', 'Madinah']),
    created_at: dateTimeStr(rand(60, 365)),
  }));
}

function generateRooms(count = 60) {
  return Array.from({ length: count }, (_, i) => {
    const floor = FLOORS[Math.floor(i / 10) % FLOORS.length];
    const type = ROOM_TYPES[i % ROOM_TYPES.length];
    const status = pick(ROOM_STATUSES);
    return {
      id: i + 1,
      number: `${floor.number}${padNum(i + 1, 2)}`,
      name: `Room ${floor.number}${padNum(i + 1, 2)}`,
      floor_id: floor.id,
      floor_name: floor.name,
      room_type_id: type.id,
      room_type: type.name,
      base_rate: type.base_rate,
      capacity: type.capacity,
      status,
      is_clean: status !== 'cleaning',
      is_inspected: rand(0, 1) === 1,
      current_guest: status === 'occupied' ? pick(GUEST_NAMES) : null,
      features: pick([['WiFi', 'TV', 'Mini Bar'], ['WiFi', 'TV', 'Balcony'], ['WiFi', 'TV', 'Jacuzzi', 'Sea View']]),
    };
  });
}

function generateReservations(count = 80) {
  const guests = generateGuests();
  const companies = generateCompanies();
  const rooms = generateRooms();
  return Array.from({ length: count }, (_, i) => {
    const status = pick(RESERVATION_STATUSES);
    const daysAgo = rand(0, 60);
    const nights = rand(1, 7);
    const guest = guests[i % guests.length];
    const room = rooms[i % rooms.length];
    const source = pick(SOURCES);
    return {
      id: i + 1,
      code: `RES-${dayjs().format('YYMM')}-${padNum(i + 1)}`,
      guest_id: guest.id,
      guest_name: guest.name,
      guest_phone: guest.phone,
      room_id: room.id,
      room_number: room.number,
      room_type: room.room_type,
      company_id: i % 4 === 0 ? companies[i % companies.length].id : null,
      company_name: i % 4 === 0 ? companies[i % companies.length].name : null,
      source_id: source.id,
      source_name: source.name,
      date_in: dateStr(daysAgo),
      date_out: dateStr(daysAgo - nights),
      nights,
      adults: rand(1, 3),
      children: rand(0, 2),
      rate_per_night: room.base_rate,
      total_amount: room.base_rate * nights,
      paid_amount: status === 'checked_out' ? room.base_rate * nights : money(0, room.base_rate * nights),
      balance: status === 'checked_out' ? 0 : money(0, room.base_rate * nights * 0.5),
      status,
      special_requests: i % 3 === 0 ? 'Early check-in requested' : null,
      created_at: dateTimeStr(daysAgo + rand(1, 5)),
      checked_in_at: ['checked_in', 'checked_out'].includes(status) ? dateTimeStr(daysAgo, rand(0, 5)) : null,
      checked_out_at: status === 'checked_out' ? dateTimeStr(daysAgo - nights, rand(0, 3)) : null,
    };
  });
}

function generateReceipts(count = 50) {
  const guests = generateGuests(10);
  return Array.from({ length: count }, (_, i) => {
    const status = pick(['draft', 'confirmed', 'confirmed', 'confirmed', 'cancelled']);
    return {
      id: i + 1,
      receipt_number: `RCP-${dayjs().format('YYYYMM')}-${padNum(i + 1)}`,
      receipt_date: dateStr(rand(0, 90)),
      guest_id: guests[i % guests.length].id,
      guest: { name: guests[i % guests.length].name },
      company: i % 5 === 0 ? { name: pick(COMPANY_NAMES) } : null,
      reservation: i % 3 === 0 ? { code: `RES-${dayjs().format('YYMM')}-${padNum(rand(1, 80))}` } : null,
      amount: money(100, 15000),
      currency: i % 10 === 0 ? 'USD' : 'SAR',
      exchange_rate: i % 10 === 0 ? 3.75 : 1.0,
      payment_method: pick(PAYMENT_METHODS),
      reference_number: i % 4 === 0 ? `REF-${rand(100000, 999999)}` : null,
      status,
      description: pick(['Room payment', 'Deposit', 'Service charges', 'F&B charges', 'Mini bar']),
      created_by: { name: pick(['Admin', 'Receptionist Ali', 'Cashier Sara']) },
      created_at: dateTimeStr(rand(0, 90)),
    };
  });
}

function generatePayments(count = 80) {
  const guests = generateGuests(10);
  return Array.from({ length: count }, (_, i) => {
    const status = pick(['pending', 'confirmed', 'confirmed', 'confirmed', 'confirmed', 'cancelled', 'reversed']);
    const type = pick(['deposit', 'payment', 'payment', 'payment', 'partial_payment', 'advance', 'refund']);
    return {
      id: i + 1,
      payment_number: `PAY-${dayjs().format('YYYYMM')}-${padNum(i + 1)}`,
      payment_date: dateStr(rand(0, 90)),
      guest_id: guests[i % guests.length].id,
      guest: { name: guests[i % guests.length].name },
      reservation: { code: `RES-${dayjs().format('YYMM')}-${padNum(rand(1, 80))}` },
      amount: money(100, 20000),
      currency: 'SAR',
      payment_method: pick(PAYMENT_METHODS),
      payment_type: type,
      reference_number: i % 3 === 0 ? `REF-${rand(100000, 999999)}` : null,
      status,
      confirmed_at: status === 'confirmed' ? dateTimeStr(rand(0, 90)) : null,
      created_by: { name: pick(['Admin', 'Cashier Ali', 'Receptionist Noura']) },
      created_at: dateTimeStr(rand(0, 90)),
    };
  });
}

function generateInvoices(count = 40) {
  const companies = generateCompanies(8);
  return Array.from({ length: count }, (_, i) => {
    const status = pick(INVOICE_STATUSES);
    const subTotal = money(500, 25000);
    const vatAmount = +(subTotal * 0.15).toFixed(2);
    return {
      id: i + 1,
      invoice_number: `INV-${dayjs().format('YYYYMM')}-${padNum(i + 1)}`,
      invoice_date: dateStr(rand(0, 90)),
      due_date: dateStr(rand(0, 30) * -1),
      guest: { name: pick(GUEST_NAMES) },
      company: i % 3 === 0 ? companies[i % companies.length] : null,
      reservation: { code: `RES-${dayjs().format('YYMM')}-${padNum(rand(1, 80))}` },
      zatca_invoice_type: i % 3 === 0 ? 'standard' : 'simplified',
      sub_total: subTotal,
      discount_amount: i % 5 === 0 ? money(50, 500) : 0,
      vat_amount: vatAmount,
      total_amount: +(subTotal + vatAmount).toFixed(2),
      currency: 'SAR',
      status,
      zatca_status: status === 'paid' ? 'accepted' : status === 'confirmed' ? pick(ZATCA_STATUSES) : 'not_reported',
      items_count: rand(1, 8),
      created_at: dateTimeStr(rand(0, 90)),
    };
  });
}

function generateCashierShifts(count = 20) {
  return Array.from({ length: count }, (_, i) => {
    const status = i === 0 ? 'open' : pick(['closed', 'closed', 'approved']);
    const openingBalance = money(500, 5000);
    const systemBalance = openingBalance + money(1000, 15000);
    const closingBalance = status !== 'open' ? systemBalance + money(-200, 200) : null;
    return {
      id: i + 1,
      user: { name: pick(['Ali Mohammed', 'Sara Al-Ghamdi', 'Khalid Al-Shehri', 'Noura Al-Otaibi']) },
      opening_balance: openingBalance,
      system_balance: systemBalance,
      closing_balance: closingBalance,
      variance: closingBalance ? +(closingBalance - systemBalance).toFixed(2) : null,
      status,
      opened_at: dateTimeStr(i, rand(0, 8)),
      closed_at: status !== 'open' ? dateTimeStr(i, rand(0, 4)) : null,
      approved_by: status === 'approved' ? { name: 'Manager Ahmed' } : null,
      notes: i % 3 === 0 ? 'Regular shift' : null,
      transactions_count: rand(5, 50),
    };
  });
}

function generateNightAuditLogs(count = 30) {
  return Array.from({ length: count }, (_, i) => ({
    id: i + 1,
    business_date: dateStr(i),
    run_number: i < 3 ? 2 : 1,
    status: i === 0 ? 'pending' : pick(['completed', 'completed', 'completed', 'failed']),
    started_at: dateTimeStr(i, 1),
    completed_at: i === 0 ? null : dateTimeStr(i, 0),
    duration_seconds: rand(30, 300),
    steps_completed: i === 0 ? rand(1, 5) : 7,
    total_steps: 7,
    rooms_processed: rand(40, 60),
    revenue_calculated: money(10000, 80000),
    no_shows_processed: rand(0, 3),
    transactions_frozen: rand(20, 100),
    snapshots_created: 2,
    run_by: { name: pick(['Admin', 'Night Auditor Ali', 'System (Auto)']) },
    error_message: pick([null, null, null, 'Timeout on step 5', 'Revenue mismatch detected']),
  }));
}

function generatePromissoryNotes(count = 25) {
  return Array.from({ length: count }, (_, i) => ({
    id: i + 1,
    promissory_number: `PRN-${dayjs().format('YYYYMM')}-${padNum(i + 1)}`,
    guest: { name: pick(GUEST_NAMES) },
    company: i % 3 === 0 ? { name: pick(COMPANY_NAMES) } : null,
    reservation: { code: `RES-${dayjs().format('YYMM')}-${padNum(rand(1, 80))}` },
    total_amount: money(500, 15000),
    collected_amount: money(0, 10000),
    remaining_amount: money(0, 5000),
    signature_status: pick(['signed', 'signed', 'unsigned', 'waived']),
    status: pick(['pending', 'partial', 'fulfilled', 'cancelled']),
    unsigned_reason: i % 4 === 0 ? 'Guest refused to sign' : null,
    due_date: dateStr(rand(0, 30) * -1),
    created_at: dateTimeStr(rand(0, 60)),
  }));
}

function generateCompanyGroups(count = 5) {
  return [
    { id: 1, name: 'Saudi Government Entities', name_ar: 'الجهات الحكومية السعودية', credit_limit: 5000000, companies_count: 5, total_balance: money(100000, 1000000) },
    { id: 2, name: 'Oil & Gas Sector', name_ar: 'قطاع النفط والغاز', credit_limit: 3000000, companies_count: 4, total_balance: money(50000, 500000) },
    { id: 3, name: 'Tourism & Hospitality', name_ar: 'السياحة والضيافة', credit_limit: 1000000, companies_count: 3, total_balance: money(20000, 200000) },
    { id: 4, name: 'Technology Partners', name_ar: 'شركاء التقنية', credit_limit: 2000000, companies_count: 3, total_balance: money(30000, 300000) },
    { id: 5, name: 'Airlines & Aviation', name_ar: 'الطيران', credit_limit: 4000000, companies_count: 2, total_balance: money(80000, 800000) },
  ].slice(0, count);
}

function generateRoomStatusLogs(count = 50) {
  return Array.from({ length: count }, (_, i) => ({
    id: i + 1,
    room_number: `${rand(1, 5)}${padNum(rand(1, 20), 2)}`,
    from_status: pick(ROOM_STATUSES),
    to_status: pick(ROOM_STATUSES),
    changed_by: { name: pick(['Housekeeping Team', 'Front Desk', 'Maintenance', 'System']) },
    change_reason: pick(['Checkout', 'Cleaning completed', 'Maintenance started', 'Guest check-in', 'Manual update', 'Inspection passed']),
    changed_at: dateTimeStr(rand(0, 30), rand(0, 23)),
  }));
}

function generateCommissionPayments(count = 20) {
  return Array.from({ length: count }, (_, i) => ({
    id: i + 1,
    source: pick(SOURCES.filter(s => s.type === 'travel_agent')),
    period_from: dateStr(rand(30, 60)),
    period_to: dateStr(rand(0, 29)),
    room_revenue_base: money(5000, 50000),
    commission_rate: pick([8, 10, 12, 15]),
    commission_amount: money(500, 5000),
    status: pick(['pending', 'approved', 'paid', 'cancelled']),
    approved_by: i % 2 === 0 ? { name: 'Finance Manager' } : null,
    paid_at: i % 3 === 0 ? dateStr(rand(0, 15)) : null,
    reservations_count: rand(3, 20),
    created_at: dateTimeStr(rand(0, 60)),
  }));
}

function generateCreditNotes(count = 15) {
  return Array.from({ length: count }, (_, i) => ({
    id: i + 1,
    credit_note_number: `CN-${dayjs().format('YYYYMM')}-${padNum(i + 1)}`,
    original_invoice: { invoice_number: `INV-${dayjs().format('YYYYMM')}-${padNum(rand(1, 40))}` },
    guest: { name: pick(GUEST_NAMES) },
    amount: money(100, 5000),
    reason: pick(['Overcharge correction', 'Service not provided', 'Room downgrade', 'Billing error', 'Guest complaint']),
    status: pick(['draft', 'confirmed', 'applied']),
    created_at: dateTimeStr(rand(0, 60)),
  }));
}

function generateInvoiceTransfers(count = 15) {
  return Array.from({ length: count }, (_, i) => ({
    id: i + 1,
    transfer_number: `TRF-${dayjs().format('YYYYMM')}-${padNum(i + 1)}`,
    reservation: { code: `RES-${dayjs().format('YYMM')}-${padNum(rand(1, 80))}` },
    guest: { name: pick(GUEST_NAMES) },
    company: { name: pick(COMPANY_NAMES) },
    amount: money(1000, 30000),
    status: pick(['pending', 'completed', 'rejected']),
    transferred_by: { name: pick(['Finance Team', 'AR Manager', 'Receptionist']) },
    notes: i % 2 === 0 ? 'Corporate billing transfer' : null,
    created_at: dateTimeStr(rand(0, 60)),
  }));
}

function generateActivities(count = 20) {
  return Array.from({ length: count }, (_, i) => ({
    id: i + 1,
    event: pick(['created', 'updated', 'deleted']),
    description: pick([
      'created a new reservation #RES-2506-0042',
      'checked in guest Ahmed Al-Rashidi to room 302',
      'processed payment of SAR 2,500',
      'updated room status to Clean',
      'cancelled reservation #RES-2506-0015',
      'approved cashier shift #12',
      'ran night audit for 2026-05-19',
      'created invoice INV-202605-0023',
      'transferred balance to AR - Saudi Aramco',
      'generated commission report for May 2026'
    ]),
    causer: { name: pick(['Admin', 'Ali Mohammed', 'Sara Al-Ghamdi', 'System']) },
    created_at: dateTimeStr(0, i),
  }));
}

function generateBanks(count = 8) {
  return [
    { id: 1, name: 'Al Rajhi Bank', name_ar: 'مصرف الراجحي', code: 'RJHI', account_number: '***4521', is_active: true },
    { id: 2, name: 'Saudi National Bank (SNB)', name_ar: 'البنك الأهلي السعودي', code: 'SNB', account_number: '***8832', is_active: true },
    { id: 3, name: 'Riyad Bank', name_ar: 'بنك الرياض', code: 'RIBL', account_number: '***1156', is_active: true },
    { id: 4, name: 'Banque Saudi Fransi', name_ar: 'البنك السعودي الفرنسي', code: 'BSFR', account_number: '***7744', is_active: true },
    { id: 5, name: 'Saudi British Bank (SABB)', name_ar: 'ساب', code: 'SABB', account_number: '***3398', is_active: true },
    { id: 6, name: 'Alinma Bank', name_ar: 'مصرف الإنماء', code: 'ALINMA', account_number: '***6672', is_active: false },
    { id: 7, name: 'Bank AlBilad', name_ar: 'بنك البلاد', code: 'BALB', account_number: '***2215', is_active: true },
    { id: 8, name: 'Bank AlJazira', name_ar: 'بنك الجزيرة', code: 'BJAZ', account_number: '***9901', is_active: true },
  ].slice(0, count);
}

function generateSenders(count = 6) {
  return [
    { id: 1, name: 'Reception SMS', type: 'sms', provider: 'Jawaly', is_active: true },
    { id: 2, name: 'Booking Confirmation', type: 'email', provider: 'SMTP', is_active: true },
    { id: 3, name: 'WhatsApp Notifications', type: 'whatsapp', provider: 'Let Link', is_active: true },
    { id: 4, name: 'Invoice Email', type: 'email', provider: 'SMTP', is_active: true },
    { id: 5, name: 'Marketing SMS', type: 'sms', provider: 'Jawaly', is_active: false },
    { id: 6, name: 'Guest Feedback', type: 'email', provider: 'SMTP', is_active: true },
  ].slice(0, count);
}

// ─── Dashboard Metrics ────────────────────────────────────
function generateDashboardMetrics() {
  return {
    metrics: {
      totalRevenue: rand(15000, 85000),
      occupancyRate: rand(55, 95),
      availableRooms: rand(8, 25),
      arrivalsToday: rand(3, 15),
      departuresToday: rand(2, 12),
      inHouseGuests: rand(20, 50),
      mtdRevenue: rand(200000, 800000),
      adr: rand(400, 1200),
      revpar: rand(300, 900),
    },
    rooms: {
      occupied: rand(30, 50),
      available: rand(8, 20),
      maintenance: rand(1, 5),
      cleaning: rand(2, 8),
      out_of_order: rand(0, 3),
    },
    alerts: [
      { type: 'warning', message: `${rand(2, 5)} reservations arriving today without room assignment` },
      { type: 'info', message: `Night audit completed successfully at ${rand(1, 3)}:00 AM` },
    ],
    recentActivity: generateActivities(10),
    chart: {
      dates: Array.from({ length: 7 }, (_, i) => dayjs().subtract(6 - i, 'day').format('MMM DD')),
      revenue: Array.from({ length: 7 }, () => rand(10000, 50000)),
      occupancy: Array.from({ length: 7 }, () => rand(60, 95)),
    },
  };
}

function generateFinanceDashboardData() {
  return {
    todayCollection: money(15000, 80000),
    cashCollected: money(5000, 30000),
    cardCollected: money(5000, 40000),
    bankTransfer: money(1000, 10000),
    openShifts: rand(1, 3),
    promissoryOutstanding: money(10000, 100000),
    invoicesPendingZatca: rand(2, 15),
    creditNotesToday: rand(0, 5),
    paymentBreakdown: [
      { method: 'Cash', amount: money(5000, 30000), color: '#10b981' },
      { method: 'Mada', amount: money(8000, 40000), color: '#3b82f6' },
      { method: 'Visa', amount: money(3000, 20000), color: '#8b5cf6' },
      { method: 'MasterCard', amount: money(2000, 15000), color: '#f59e0b' },
      { method: 'Bank Transfer', amount: money(1000, 10000), color: '#6366f1' },
    ],
  };
}

function generateOccupancyDashboardData() {
  return {
    currentOccupancy: rand(60, 95),
    totalRooms: 60,
    occupiedRooms: rand(35, 55),
    availableRooms: rand(5, 20),
    outOfOrder: rand(0, 3),
    byRoomType: ROOM_TYPES.map(t => ({
      name: t.name,
      total: rand(5, 15),
      occupied: rand(2, 12),
      rate: rand(40, 100),
    })),
    trend: Array.from({ length: 30 }, (_, i) => ({
      date: dayjs().subtract(29 - i, 'day').format('MMM DD'),
      occupancy: rand(50, 98),
    })),
    forecast: Array.from({ length: 14 }, (_, i) => ({
      date: dayjs().add(i, 'day').format('MMM DD'),
      expected: rand(55, 95),
    })),
  };
}

function generateARDashboardData() {
  return {
    totalReceivables: money(200000, 800000),
    overdue: money(50000, 200000),
    avgCollectionDays: rand(25, 60),
    aging: {
      current: money(100000, 300000),
      days30: money(50000, 150000),
      days60: money(20000, 80000),
      days90: money(10000, 50000),
      over90: money(5000, 30000),
    },
    topDebtors: generateCompanies(5).map(c => ({
      ...c,
      outstanding: money(20000, 200000),
      overdue_invoices: rand(1, 10),
    })),
  };
}

// ─── Integration Health ──────────────────────────────────
function generateIntegrations() {
  return [
    { id: 1, name: 'ZATCA E-Invoicing', status: 'connected', last_sync: dateTimeStr(0, rand(0, 4)), failed_count: rand(0, 2), type: 'zatca' },
    { id: 2, name: 'Qoyod Accounting', status: 'connected', last_sync: dateTimeStr(0, rand(0, 6)), failed_count: 0, type: 'accounting' },
    { id: 3, name: 'STA AH (Shomoos)', status: pick(['connected', 'disconnected']), last_sync: dateTimeStr(0, rand(0, 12)), failed_count: rand(0, 5), type: 'government' },
    { id: 4, name: 'Jawaly SMS', status: 'connected', last_sync: dateTimeStr(0, rand(0, 2)), failed_count: 0, type: 'messaging' },
    { id: 5, name: 'IPTV System', status: pick(['connected', 'disconnected']), last_sync: dateTimeStr(1, rand(0, 12)), failed_count: rand(0, 3), type: 'iptv' },
    { id: 6, name: 'Let Link WhatsApp', status: 'connected', last_sync: dateTimeStr(0, rand(0, 8)), failed_count: 0, type: 'messaging' },
    { id: 7, name: 'Booking.com Channel', status: 'connected', last_sync: dateTimeStr(0, rand(0, 1)), failed_count: 0, type: 'channel' },
    { id: 8, name: 'Metabase Analytics', status: 'connected', last_sync: dateTimeStr(0, rand(0, 3)), failed_count: 0, type: 'analytics' },
  ];
}

// ─── NoShow Rules ────────────────────────────────────────
function generateNoShowRules(count = 8) {
  return Array.from({ length: count }, (_, i) => ({
    id: i + 1,
    name: pick(['Peak Season Rule', 'Standard Rule', 'Low Season Rule', 'Holiday Rule', 'Weekend Rule']),
    start_date: dateStr(rand(0, 30)),
    end_date: dateStr(rand(0, 30) * -1),
    charge_type: pick(['fixed', 'percentage']),
    charge_amount: pick([0, 100, 200, 350, 50, 100]),
    applies_to: pick(['all', 'daily', 'monthly']),
    is_active: i < 5,
    created_by: { name: 'Admin' },
    created_at: dateTimeStr(rand(10, 90)),
  }));
}

// ─── Early/Late Charge Configs ───────────────────────────
function generateEarlyLateConfigs() {
  return [
    { id: 1, charge_type: 'early_checkin', tier_from_hour: 0, tier_to_hour: 6, rate_type: 'percentage_first_night', rate_amount: 100, is_active: true },
    { id: 2, charge_type: 'early_checkin', tier_from_hour: 6, tier_to_hour: 10, rate_type: 'percentage_first_night', rate_amount: 50, is_active: true },
    { id: 3, charge_type: 'early_checkin', tier_from_hour: 10, tier_to_hour: 14, rate_type: 'fixed', rate_amount: 0, is_active: true },
    { id: 4, charge_type: 'late_checkout', tier_from_hour: 12, tier_to_hour: 14, rate_type: 'fixed', rate_amount: 0, is_active: true },
    { id: 5, charge_type: 'late_checkout', tier_from_hour: 14, tier_to_hour: 18, rate_type: 'percentage_nightly_rate', rate_amount: 50, is_active: true },
    { id: 6, charge_type: 'late_checkout', tier_from_hour: 18, tier_to_hour: 24, rate_type: 'percentage_nightly_rate', rate_amount: 100, is_active: true },
  ];
}

// ─── Guest / Deposit Ledger ──────────────────────────────
function generateGuestLedger(count = 25) {
  return Array.from({ length: count }, (_, i) => ({
    id: i + 1,
    reservation_code: `RES-${dayjs().format('YYMM')}-${padNum(i + 1)}`,
    room_number: `${rand(1, 5)}${padNum(rand(1, 20), 2)}`,
    guest_name: pick(GUEST_NAMES),
    check_in_date: dateStr(rand(1, 10)),
    nights_so_far: rand(1, 7),
    room_charges: money(500, 5000),
    service_charges: money(0, 1500),
    payments: money(0, 5000),
    balance: money(-500, 3000),
  }));
}

function generateDepositLedger(count = 20) {
  return Array.from({ length: count }, (_, i) => ({
    id: i + 1,
    reservation_code: `RES-${dayjs().format('YYMM')}-${padNum(i + 1)}`,
    guest_name: pick(GUEST_NAMES),
    deposit_amount: money(500, 10000),
    applied_amount: money(0, 8000),
    remaining: money(0, 5000),
    payment_method: pick(PAYMENT_METHODS),
    status: pick(['unapplied', 'partially_applied', 'fully_applied']),
    deposit_date: dateStr(rand(0, 30)),
  }));
}

// ─── Reports Data ────────────────────────────────────────
function generateReportData(type) {
  const dates = Array.from({ length: 30 }, (_, i) => dayjs().subtract(29 - i, 'day').format('YYYY-MM-DD'));
  switch (type) {
    case 'daily':
      return dates.map(d => ({
        date: d,
        occupancy: rand(50, 98),
        revenue: money(10000, 80000),
        arrivals: rand(2, 15),
        departures: rand(2, 12),
        no_shows: rand(0, 3),
        adr: money(350, 1200),
      }));
    case 'revenue':
      return dates.map(d => ({
        date: d,
        room_revenue: money(8000, 50000),
        pos_revenue: money(1000, 10000),
        other_revenue: money(500, 5000),
        total: money(10000, 65000),
      }));
    default:
      return [];
  }
}

// ─── Master Export ────────────────────────────────────────
export const mockData = {
  guests: generateGuests(),
  companies: generateCompanies(),
  companyGroups: generateCompanyGroups(),
  rooms: generateRooms(),
  roomTypes: ROOM_TYPES,
  floors: FLOORS,
  sources: SOURCES,
  reservations: generateReservations(),
  receipts: generateReceipts(),
  payments: generatePayments(),
  invoices: generateInvoices(),
  cashierShifts: generateCashierShifts(),
  nightAuditLogs: generateNightAuditLogs(),
  promissoryNotes: generatePromissoryNotes(),
  roomStatusLogs: generateRoomStatusLogs(),
  commissionPayments: generateCommissionPayments(),
  creditNotes: generateCreditNotes(),
  invoiceTransfers: generateInvoiceTransfers(),
  banks: generateBanks(),
  senders: generateSenders(),
  noShowRules: generateNoShowRules(),
  earlyLateConfigs: generateEarlyLateConfigs(),
  guestLedger: generateGuestLedger(),
  depositLedger: generateDepositLedger(),
  integrations: generateIntegrations(),
  activities: generateActivities(),
  dashboardOverview: generateDashboardMetrics(),
  financeDashboard: generateFinanceDashboardData(),
  occupancyDashboard: generateOccupancyDashboardData(),
  arDashboard: generateARDashboardData(),
  paymentMethods: PAYMENT_METHODS,
  currencies: CURRENCIES,
  reservationStatuses: RESERVATION_STATUSES,
  invoiceStatuses: INVOICE_STATUSES,
};

export default mockData;
