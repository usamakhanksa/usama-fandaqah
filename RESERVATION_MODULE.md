# Reservation Management Module

## Overview
The Reservation Management Module is a comprehensive solution for handling all aspects of hotel reservations, including individual bookings, group reservations, online bookings, and OTA integrations. It includes features for managing arrivals/departures, room transfers, extensions, digital contracts, and guest feedback.

## Features

### Core Reservation Management
- **List/View**: Advanced filtering, search, and pagination for all reservations
- **Create**: Full reservation form with guest/company selection and quick book option
- **Edit/Delete**: Modify or cancel reservations with proper validation
- **Special Actions**: Check-in/check-out, transfer units, extend stays, mark no-shows

### Reservation Types
- Individual reservations
- Group reservations
- Online reservations (website + public API)
- OTA/Channel reservations (including STA AH sync)

### Advanced Features
- **Calendar & Availability Grid**: Drag-and-drop calendar view with color-coded statuses
- **Arrivals & Departures Board**: Daily tracking of check-ins and check-outs
- **In-House Guests**: Real-time tracking of current guests
- **Reservation Transfers & Room Moves**: Track full history of changes
- **Reservation Extensions**: Extend stay with cost calculation
- **Digital Contracts & Signatures**: Create, view, sign, and download contracts
- **Ratings & Guest Feedback**: Collect and manage guest reviews
- **Bulk Actions**: Bulk check-in, cancel, export, and print invoices
- **Audit Trail**: Complete history log and verification status

## Database Structure

### Reservations Table
Added columns:
- `reservation_category_type`: Normal, Complimentary, HouseUse, DayUse
- `special_request`: Text field for guest requests
- `company_id`: Foreign key linking to companies table
- `audit_locks`: JSON field for night audit locks
- `shomoos_verification_status`: Verification status for Saudi compliance
- `noshow_flag`: Boolean indicating if reservation was no-show
- `extension_reason`: Reason for stay extension
- `cancellation_reason`: Reason for cancellation

### New Tables
- `reservation_extensions`: Track reservation extensions with cost and reason
- `reservation_ratings`: Store guest ratings and feedback

## Controllers

### ReservationController
Handles all basic CRUD operations for reservations with advanced filtering capabilities.

### ReservationCalendarController
Provides calendar view with drag-and-drop functionality for managing reservations visually.

### ArrivalsDeparturesController
Manages daily arrivals, departures, and in-house guest tracking.

### GroupReservationController
Handles group booking management with multiple linked reservations.

## Nova Resources

### Reservation Nova Resource
Complete admin interface with:
- Collapsible panels for organized information
- Advanced filtering options
- Custom actions for common operations
- Related data display (contracts, transfers, ratings, etc.)

### Custom Actions
- Export Reservations to Excel
- Send Check-in Reminders
- Mark As No-Show
- Bulk Check-In
- Bulk Cancel

## API Endpoints

### Basic Reservation Operations
- `GET /reservations` - List reservations with filters
- `POST /reservations` - Create new reservation
- `GET /reservations/{id}` - Get specific reservation
- `PUT /reservations/{id}` - Update reservation
- `DELETE /reservations/{id}` - Cancel reservation

### Special Actions
- `POST /reservations/{id}/check-in` - Check in guest
- `POST /reservations/{id}/check-out` - Check out guest
- `POST /reservations/{id}/extend` - Extend reservation
- `POST /reservations/{id}/no-show` - Mark as no-show
- `POST /reservations/{id}/transfer` - Transfer to different unit

### Calendar & Reporting
- `GET /calendar/reservations` - Calendar view
- `GET /calendar/reservations/events` - Calendar events data
- `GET /arrivals-departures` - Arrivals and departures board
- `GET /arrivals-departures/data` - Dynamic data for arrivals/departures

## Views

### Calendar View
Interactive calendar showing all reservations with color-coded statuses for easy visualization.

### Arrivals & Departures Board
Dashboard view showing today's arrivals, departures, and in-house guests with quick action buttons.

## Key Business Logic

### Reservation Categories
- **Normal**: Standard paid reservation
- **Complimentary**: Free accommodation
- **HouseUse**: Staff or management use
- **DayUse**: Same-day check-in/check-out

### Validation Rules
- Prevents double booking of same unit during overlapping dates
- Validates against night audit locks
- Enforces business rules for pricing and availability

### Extension Process
- Calculates additional costs based on current rates
- Updates reservation dates and pricing
- Maintains history of all changes

## Security & Permissions

The module integrates with the existing permission system and follows all security protocols for:
- Access control based on user roles
- Data isolation between teams/companies
- Audit logging of all significant changes
- Secure handling of personal data

## Integration Points

- Links with customer and company management
- Integrates with unit/inventory management
- Connects to financial systems for invoicing
- Supports OTA synchronization
- Includes digital signature workflows