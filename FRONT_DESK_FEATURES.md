# Front Desk Module Documentation

## Overview
The Front Desk module handles all guest-facing operations including check-in/check-out, guest registration, room assignments, and various quick actions. This module provides a comprehensive solution for hotel staff to manage daily front office operations.

## Features

### 1. Check-in/Check-out Operations

#### Check-in Process
- Seamless integration with reservation system
- Shomoos ID verification for guest validation
- Digital signature capture during check-in
- Automatic calculation of early check-in charges based on `early_late_charge_configs`
- Link to `digital_signatures` for capturing signatures

#### Check-out Process
- Records check-out time
- Calculates late check-out charges based on `early_late_charge_configs`
- Captures digital signature during check-out
- Updates reservation status to completed

#### API Endpoints
- `POST /front-desk/check-in/{reservationId}` - Process check-in
- `POST /front-desk/check-out/{reservationId}` - Process check-out

### 2. Guest Registration (Add/Edit/Delete)

#### Guest Management
- Full CRUD operations for guest records
- Link guests to reservations through `reservation_guests`
- Validate Shomoos ID against external service
- Capture visitor's ID scan, photo, and signature

#### API Endpoints
- `POST /front-desk/reservation/{reservationId}/guest` - Add guest to reservation
- `PUT /front-desk/guest/{guestId}` - Update guest information
- `DELETE /front-desk/guest/{guestId}` - Remove guest from reservation
- `POST /front-desk/validate-shomoos-id` - Validate Shomoos ID

### 3. Early Check-in / Late Check-out Charges

#### Charge Calculation
- Automatic calculation from `early_late_charge_configs`
- Different rate types: fixed, percentage of first night, percentage of nightly rate
- Configurable by hour tiers and applies to daily/monthly reservations

#### Configuration Options
- `charge_type`: early_checkin or late_checkout
- `tier_from_hour` to `tier_to_hour`: Time window for charge application
- `rate_type`: fixed, percentage_first_night, or percentage_nightly_rate
- `rate_amount`: Amount to charge based on rate type
- `applies_to`: all, daily, or monthly reservations

### 4. No-Show Handling & Auto-Charges

#### No-Show Processing
- Mark reservations as no-show
- Apply configured penalties based on business rules
- Update reservation status to canceled

#### API Endpoint
- `POST /front-desk/reservation/{reservationId}/no-show` - Handle no-show

### 5. Room Assignment / Re-assignment

#### Drag-and-Drop UI Support
- Assign rooms to reservations
- Check room availability during assignment
- Swap rooms between reservations
- Manage pending assignments

#### API Endpoint
- `POST /front-desk/assign-room` - Assign room to reservation

### 6. IPTV Guest Needs & Wake-up Calls

#### Request Management
- Create guest requests (IPTV needs, wake-up calls)
- Track request status (treated/untreated)
- Maintain history of requests
- Priority-based request handling

#### API Endpoints
- `POST /front-desk/iptv-request` - Create IPTV request
- `POST /front-desk/iptv-request/{requestId}/mark-treated` - Mark request as treated

### 7. Quick Actions

#### Walk-in Booking
- Create instant reservations for walk-in guests
- Validate room availability
- Automatically check-in upon creation

#### Same-day Extension
- Extend current stay for checked-in guests
- Validate room availability for extension
- Calculate additional charges

#### Print Registration Card
- Generate registration cards for guests
- (Implementation would be in frontend component)

#### API Endpoints
- `POST /front-desk/walk-in-booking` - Create walk-in reservation
- `PUT /front-desk/reservation/{reservationId}/extend` - Extend reservation

## Data Models

### Key Relationships
- `Reservation` ↔ `Guest` (many-to-many through customer_guest_reservation)
- `Reservation` ↔ `DigitalSignature` (one-to-many)
- `Reservation` ↔ `IptvGuestNeed` (one-to-many)
- `Reservation` ↔ `Unit` (many-to-one)

### Core Models
1. **Reservation**: Main reservation entity with check-in/out timestamps
2. **Guest**: Guest information linked to reservations
3. **DigitalSignature**: Stores digital signatures for check-in/out
4. **IptvGuestNeed**: Tracks guest requests via IPTV system
5. **EarlyLateChargeConfig**: Configuration for automatic charge calculations

## Security & Validation

### Input Validation
- Comprehensive validation for all input fields
- Unique constraints on ID numbers
- Foreign key validations
- Date validations for check-in/check-out times

### Authentication
- All endpoints require authentication
- Access controlled by user roles and permissions
- Team-based isolation of data

## Error Handling
- Comprehensive error responses
- Validation error details
- Transaction rollbacks on failures
- Proper HTTP status codes (200 for success, 400 for validation errors, 500 for server errors)

## Integration Points
- External Shomoos ID validation service
- Digital signature capture system
- Payment processing for additional charges
- Notification systems for guest requests
- Unit availability checking system

## Testing Considerations
- Unit tests for service methods
- Feature tests for API endpoints
- Integration tests for complete workflows
- Edge cases for overlapping reservations