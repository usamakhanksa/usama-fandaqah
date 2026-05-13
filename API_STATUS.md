# API Status Documentation - Usama Fandaqah Hotel Management System

**Generated:** May 11, 2026  
**Total Endpoints:** 150+  
**Authentication:** Laravel Sanctum (Bearer Token)

---

## Table of Contents

1. [Public Routes](#1-public-routes-no-authentication)
2. [Authentication](#2-authentication)
3. [Dashboard](#3-dashboard)
4. [Reservations](#4-reservations)
5. [Rooms & Housekeeping](#5-rooms--housekeeping)
6. [Front Desk](#6-front-desk)
7. [POS & Services](#7-pos--services)
8. [Guests & Companies](#8-guests--companies)
9. [Finance](#9-finance)
10. [Master Data](#10-master-data)
11. [Reports](#11-reports)
12. [System](#12-system)

---

## 1. Public Routes (No Authentication)

| Method | Endpoint | Controller | Description |
|--------|----------|------------|-------------|
| POST | `/api/leads/submit` | `LeadController@store` | Public lead submission form |
| POST | `/api/login` | `Auth\LoginController@login` | User login - returns Sanctum token |
| GET | `/api/settings/{category}` | `SettingsController@index` | Get settings by category |
| POST | `/api/settings/global` | `SettingsController@updateGlobal` | Update global settings |

---

## 2. Authentication

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| POST | `/api/logout` | ✓ | `Auth\LoginController@logout` | User logout (invalidate token) |
| GET | `/api/sidebar` | ✓ | `SidebarController@index` | Get sidebar menu for authenticated user |

---

## 3. Dashboard

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/dashboard/overview` | ✓ | `DashboardController@overview` | Main dashboard metrics |
| GET | `/api/dashboard/overview/export` | ✓ | `DashboardController@exportOverview` | Export overview data |
| GET | `/api/dashboard/front-desk` | ✓ | `DashboardController@frontDesk` | Front desk dashboard |
| GET | `/api/dashboard/front-desk/export` | ✓ | `DashboardController@exportFrontDesk` | Export front desk data |
| GET | `/api/dashboard/occupancy` | ✓ | `DashboardController@occupancy` | Occupancy statistics |
| GET | `/api/dashboard/occupancy/export` | ✓ | `DashboardController@exportOccupancy` | Export occupancy data |
| GET | `/api/dashboard/housekeeping` | ✓ | `DashboardController@housekeeping` | Housekeeping dashboard |
| GET | `/api/dashboard/finance` | ✓ | `DashboardController@finance` | Financial dashboard |
| GET | `/api/dashboard/night-audit` | ✓ | `DashboardController@nightAuditDashboard` | Night audit dashboard |
| GET | `/api/dashboard/revenue` | ✓ | `DashboardController@revenue` | Revenue analytics |
| GET | `/api/dashboard/revenue/export` | ✓ | `DashboardController@exportRevenue` | Export revenue data |
| GET | `/api/dashboard/ar` | ✓ | `DashboardController@ar` | Accounts receivable dashboard |
| GET | `/api/dashboard/cashier` | ✓ | `DashboardController@cashier` | Cashier dashboard |
| GET | `/api/dashboard/commissions` | ✓ | `DashboardController@commissions` | Commissions dashboard |
| GET | `/api/dashboard/metabase` | ✓ | `DashboardController@metabase` | Metabase integration |

---

## 4. Reservations

### 4.1 Core Reservation Management

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/reservations` | ✓ | `ReservationController@index` | List all reservations with filters |
| POST | `/api/reservations` | ✓ | `ReservationController@store` | Create new reservation |
| GET | `/api/reservations/{reservation}` | ✓ | `ReservationController@show` | Get single reservation details |
| PUT | `/api/reservations/{reservation}` | ✓ | `ReservationController@update` | Update reservation |
| DELETE | `/api/reservations/{reservation}` | ✓ | `ReservationController@destroy` | Cancel/delete reservation |
| GET | `/api/reservations/export` | ✓ | `ReservationController@export` | Export reservations to CSV |
| POST | `/api/reservations/quick-create` | ✓ | `ReservationController@quickStore` | Quick create reservation |

### 4.2 Reservation Views & Filters

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/reservations/calendar` | ✓ | `ReservationController@calendar` | Calendar view data |
| GET | `/api/reservations/arrivals` | ✓ | `ReservationController@arrivals` | Today's arrivals |
| GET | `/api/reservations/departures` | ✓ | `ReservationController@departures` | Today's departures |
| GET | `/api/reservations/in-house` | ✓ | `ReservationController@inHouse` | Currently checked-in guests |
| GET | `/api/reservations/online` | ✓ | `ReservationController@online` | Online/web reservations |
| GET | `/api/reservations/ota` | ✓ | `ReservationController@ota` | **OTA reservations (Booking.com, Expedia, etc.)** |
| GET | `/api/reservations/groups` | ✓ | `ReservationController@groupIndex` | Group reservations |
| POST | `/api/reservations/groups` | ✓ | `ReservationController@groupStore` | Create group reservation |
| POST | `/api/reservations/groups/{id}/cancel` | ✓ | `ReservationController@groupCancel` | Cancel group reservation |

### 4.3 Reservation Actions

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| POST | `/api/reservations/{id}/confirm` | ✓ | `ReservationController@confirm` | Confirm online reservation |
| POST | `/api/reservations/{id}/reject` | ✓ | `ReservationController@reject` | Reject online reservation |
| POST | `/api/reservations/{id}/sync-status` | ✓ | `ReservationController@syncStatus` | Sync OTA status with channel |
| POST | `/api/reservations/{id}/check-in` | ✓ | `ReservationController@checkIn` | Check-in guest |
| POST | `/api/reservations/{id}/check-out` | ✓ | `ReservationController@checkOut` | Check-out guest |

### 4.4 Reservation Guests

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/reservations/{reservation}/guests` | ✓ | `ReservationGuestController@index` | List reservation guests |
| POST | `/api/reservations/{reservation}/guests` | ✓ | `ReservationGuestController@store` | Add guest to reservation |
| DELETE | `/api/reservations/{reservation}/guests/{guest}` | ✓ | `ReservationGuestController@destroy` | Remove guest from reservation |

### 4.5 Reservation Rooms

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/reservations/{reservation}/rooms` | ✓ | `ReservationRoomController@index` | List reservation rooms |
| POST | `/api/reservations/{reservation}/rooms` | ✓ | `ReservationRoomController@store` | Add room to reservation |
| DELETE | `/api/reservations/{reservation}/rooms/{subReservation}` | ✓ | `ReservationRoomController@destroy` | Remove room from reservation |

### 4.6 Specialized Reservation Endpoints

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/reservations/transfers` | ✓ | `ReservationTransferController@index` | List reservation transfers |
| GET | `/api/reservations/transfers/{id}` | ✓ | `ReservationTransferController@show` | Get transfer details |
| GET | `/api/reservations/extensions` | ✓ | `ReservationExtensionController@index` | List stay extensions |
| GET | `/api/reservations/extensions/{id}` | ✓ | `ReservationExtensionController@show` | Get extension details |
| GET | `/api/reservations/cancellations` | ✓ | `ReservationCancellationController@index` | List cancellations |
| GET | `/api/reservations/cancellations/{id}` | ✓ | `ReservationCancellationController@show` | Get cancellation details |
| GET | `/api/reservations/messages` | ✓ | `ReservationMessageController@index` | List reservation messages |
| GET | `/api/reservations/messages/{id}` | ✓ | `ReservationMessageController@show` | Get message details |
| POST | `/api/reservations/messages` | ✓ | `ReservationMessageController@store` | Create message |
| GET | `/api/reservations/audit-locks` | ✓ | `ReservationAuditLockController@index` | List audit locks |
| GET | `/api/reservations/audit-locks/{id}` | ✓ | `ReservationAuditLockController@show` | Get audit lock details |
| GET | `/api/reservations/contracts` | ✓ | `ReservationContractController@index` | List contracts |
| GET | `/api/reservations/contracts/{id}` | ✓ | `ReservationContractController@show` | Get contract details |
| POST | `/api/reservations/contracts` | ✓ | `ReservationContractController@store` | Create contract |
| GET | `/api/reservations/contracts/{id}/download` | ✓ | `ReservationContractController@download` | Download contract PDF |
| POST | `/api/reservations/contracts/{id}/sign` | ✓ | `ReservationContractController@sign` | Sign contract digitally |
| GET | `/api/reservations/signatures` | ✓ | `DigitalSignatureController@index` | List digital signatures |
| GET | `/api/reservations/signatures/{id}` | ✓ | `DigitalSignatureController@show` | Get signature details |
| GET | `/api/reservations/ratings` | ✓ | `ReservationController@ratings` | List guest ratings |
| GET | `/api/reservations/ratings/{id}` | ✓ | `ReservationController@ratingShow` | Get rating details |

### 4.7 Workflow & Scheduling

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/reservations/schedule` | ✓ | `ReservationWorkflowController@schedule` | Get reservation schedule |
| POST | `/api/reservations/drafts` | ✓ | `ReservationWorkflowController@saveDraft` | Save reservation draft |
| GET | `/api/reservations/drafts/{reference}` | ✓ | `ReservationWorkflowController@showDraft` | Get draft by reference |
| POST | `/api/reservations/promo/apply` | ✓ | `ReservationWorkflowController@applyPromo` | Apply promotion code |
| POST | `/api/reservations/confirm` | ✓ | `ReservationWorkflowController@confirm` | Confirm reservation |
| GET | `/api/reservations/success/{booking}` | ✓ | `ReservationWorkflowController@successData` | Get success page data |
| GET | `/api/reservations/receipt/{booking}` | ✓ | `ReservationWorkflowController@receipt` | Get booking receipt |
| GET | `/api/reservations/management/{booking}` | ✓ | `ReservationWorkflowController@bookingDetails` | Get booking management details |
| POST | `/api/reservations/management/{booking}/notes` | ✓ | `ReservationWorkflowController@addNote` | Add note to booking |

---

## 5. Rooms & Housekeeping

### 5.1 Units (Rooms)

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/rooms-module/units` | ✓ | `RoomsHousekeepingController@unitsIndex` | List all units |
| POST | `/api/rooms-module/units` | ✓ | `RoomsHousekeepingController@unitsStore` | Create new unit |
| GET | `/api/rooms-module/units/{id}` | ✓ | `RoomsHousekeepingController@unitsShow` | Get unit details |
| PUT | `/api/rooms-module/units/{id}` | ✓ | `RoomsHousekeepingController@unitsUpdate` | Update unit |
| DELETE | `/api/rooms-module/units/{id}` | ✓ | `RoomsHousekeepingController@unitsDestroy` | Delete unit |
| POST | `/api/rooms-module/units/{id}/restore` | ✓ | `RoomsHousekeepingController@unitsRestore` | Restore deleted unit |

### 5.2 Unit Categories

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/rooms-module/unit-categories` | ✓ | `RoomsHousekeepingController@categoriesIndex` | List categories |
| POST | `/api/rooms-module/unit-categories` | ✓ | `RoomsHousekeepingController@categoriesStore` | Create category |
| GET | `/api/rooms-module/unit-categories/{id}` | ✓ | `RoomsHousekeepingController@categoriesShow` | Get category details |
| PUT | `/api/rooms-module/unit-categories/{id}` | ✓ | `RoomsHousekeepingController@categoriesUpdate` | Update category |
| DELETE | `/api/rooms-module/unit-categories/{id}` | ✓ | `RoomsHousekeepingController@categoriesDestroy` | Delete category |

### 5.3 Boards & Status

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/rooms-module/availability-board` | ✓ | `RoomsHousekeepingController@availabilityBoard` | Availability board view |
| GET | `/api/rooms-module/status-board` | ✓ | `RoomsHousekeepingController@statusBoard` | Room status board |
| PUT | `/api/rooms-module/units/{id}/status` | ✓ | `RoomsHousekeepingController@updateUnitStatus` | Update room status |
| GET | `/api/rooms-module/housekeeping-board` | ✓ | `RoomsHousekeepingController@housekeepingBoard` | Housekeeping task board |
| PUT | `/api/rooms-module/housekeeping-tasks/{id}` | ✓ | `RoomsHousekeepingController@updateHousekeepingTask` | Update housekeeping task |

### 5.4 Cleaning & Maintenance

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/rooms-module/unit-cleanings` | ✓ | `RoomsHousekeepingController@cleaningsIndex` | List cleaning tasks |
| POST | `/api/rooms-module/unit-cleanings/{id}/start` | ✓ | `RoomsHousekeepingController@startCleaning` | Start cleaning |
| POST | `/api/rooms-module/unit-cleanings/{id}/complete` | ✓ | `RoomsHousekeepingController@completeCleaning` | Complete cleaning |
| GET | `/api/rooms-module/unit-maintenances` | ✓ | `RoomsHousekeepingController@maintenancesIndex` | List maintenance requests |
| POST | `/api/rooms-module/unit-maintenances` | ✓ | `RoomsHousekeepingController@maintenancesStore` | Create maintenance request |
| POST | `/api/rooms-module/unit-maintenances/{id}/assign` | ✓ | `RoomsHousekeepingController@assignMaintenance` | Assign maintenance staff |
| POST | `/api/rooms-module/unit-maintenances/{id}/complete` | ✓ | `RoomsHousekeepingController@completeMaintenance` | Complete maintenance |

### 5.5 Logs & Media

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/rooms-module/room-status-log` | ✓ | `RoomsHousekeepingController@statusLog` | Room status change log |
| GET | `/api/rooms-module/room-status-log/timeline/{unitId}` | ✓ | `RoomsHousekeepingController@statusLogTimeline` | Status timeline for unit |
| GET | `/api/rooms-module/units/{unitId}/media` | ✓ | `RoomsHousekeepingController@mediaIndex` | List unit media |
| POST | `/api/rooms-module/units/{unitId}/media` | ✓ | `RoomsHousekeepingController@mediaStore` | Upload unit media |
| DELETE | `/api/rooms-module/units/{unitId}/media/{mediaId}` | ✓ | `RoomsHousekeepingController@mediaDestroy` | Delete unit media |

### 5.6 Features & Options

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/rooms-module/unit-features` | ✓ | `RoomsHousekeepingController@featuresIndex` | List unit features |
| POST | `/api/rooms-module/unit-features` | ✓ | `RoomsHousekeepingController@featuresStore` | Create feature |
| PUT | `/api/rooms-module/unit-features/{id}` | ✓ | `RoomsHousekeepingController@featuresUpdate` | Update feature |
| DELETE | `/api/rooms-module/unit-features/{id}` | ✓ | `RoomsHousekeepingController@featuresDestroy` | Delete feature |
| GET | `/api/rooms-module/unit-options` | ✓ | `RoomsHousekeepingController@optionsIndex` | List unit options |
| POST | `/api/rooms-module/unit-options` | ✓ | `RoomsHousekeepingController@optionsStore` | Create option |
| PUT | `/api/rooms-module/unit-options/{id}` | ✓ | `RoomsHousekeepingController@optionsUpdate` | Update option |
| DELETE | `/api/rooms-module/unit-options/{id}` | ✓ | `RoomsHousekeepingController@optionsDestroy` | Delete option |
| GET | `/api/rooms-module/unit-category-services` | ✓ | `RoomsHousekeepingController@categoryServicesIndex` | List category services |
| POST | `/api/rooms-module/unit-category-services` | ✓ | `RoomsHousekeepingController@categoryServicesStore` | Add service to category |
| DELETE | `/api/rooms-module/unit-category-services/{id}` | ✓ | `RoomsHousekeepingController@categoryServicesDestroy` | Remove service |

---

## 6. Front Desk

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/front-desk/arrivals` | ✓ | `FrontDeskApiController@arrivals` | Today's arrivals |
| POST | `/api/front-desk/check-in/{id}` | ✓ | `FrontDeskApiController@checkIn` | Process check-in |
| GET | `/api/front-desk/departures` | ✓ | `FrontDeskApiController@departures` | Today's departures |
| POST | `/api/front-desk/check-out/{id}` | ✓ | `FrontDeskApiController@checkOut` | Process check-out |
| GET | `/api/front-desk/folio/{id}` | ✓ | `FrontDeskApiController@folio` | Get guest folio |
| POST | `/api/front-desk/walk-in` | ✓ | `FrontDeskApiController@walkIn` | Create walk-in reservation |
| POST | `/api/front-desk/registration/{reservationId}` | ✓ | `FrontDeskApiController@saveRegistration` | Save guest registration |
| GET | `/api/front-desk/unassigned-reservations` | ✓ | `FrontDeskApiController@unassignedReservations` | List unassigned reservations |
| GET | `/api/front-desk/available-rooms` | ✓ | `FrontDeskApiController@availableRooms` | List available rooms |
| POST | `/api/front-desk/room-assignment` | ✓ | `FrontDeskApiController@assignRoom` | Assign room to reservation |
| POST | `/api/front-desk/room-swap` | ✓ | `FrontDeskApiController@swapRoom` | Swap rooms between guests |
| GET | `/api/front-desk/early-check-in/charge` | ✓ | `FrontDeskApiController@earlyCheckinCharge` | Calculate early check-in fee |
| POST | `/api/front-desk/early-check-in` | ✓ | `FrontDeskApiController@applyEarlyCheckin` | Apply early check-in |
| GET | `/api/front-desk/late-checkout/charge` | ✓ | `FrontDeskApiController@lateCheckoutCharge` | Calculate late checkout fee |
| POST | `/api/front-desk/late-checkout` | ✓ | `FrontDeskApiController@applyLateCheckout` | Apply late checkout |
| POST | `/api/front-desk/no-show/{id}` | ✓ | `FrontDeskApiController@markNoShow` | Mark reservation as no-show |
| GET | `/api/front-desk/wake-up-calls` | ✓ | `FrontDeskApiController@wakeUpCalls` | List wake-up calls |
| POST | `/api/front-desk/wake-up-calls` | ✓ | `FrontDeskApiController@storeWakeUpCall` | Create wake-up call |
| PUT | `/api/front-desk/wake-up-calls/{id}` | ✓ | `FrontDeskApiController@updateWakeUpCall` | Update wake-up call |
| POST | `/api/front-desk/wake-up-calls/{id}/complete` | ✓ | `FrontDeskApiController@completeWakeUpCall` | Mark wake-up complete |
| DELETE | `/api/front-desk/wake-up-calls/{id}` | ✓ | `FrontDeskApiController@deleteWakeUpCall` | Delete wake-up call |
| GET | `/api/front-desk/iptv-needs` | ✓ | `FrontDeskApiController@iptvNeeds` | List IPTV guest needs |
| POST | `/api/front-desk/iptv-needs` | ✓ | `FrontDeskApiController@storeIptvNeed` | Create IPTV need |
| PUT | `/api/front-desk/iptv-needs/{id}` | ✓ | `FrontDeskApiController@updateIptvNeed` | Update IPTV need |
| DELETE | `/api/front-desk/iptv-needs/{id}` | ✓ | `FrontDeskApiController@deleteIptvNeed` | Delete IPTV need |
| GET | `/api/front-desk/registration-cards` | ✓ | `FrontDeskApiController@registrationCards` | List registration cards |
| GET | `/api/front-desk/registration-cards/{id}` | ✓ | `FrontDeskApiController@registrationCardData` | Get registration card data |
| POST | `/api/front-desk/balance-transfer` | ✓ | `FrontDeskApiController@balanceTransfer` | Transfer balance between reservations |

---

## 7. POS & Services

### 7.1 POS Dashboard & Catalog

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/pos-module/dashboard` | ✓ | `PosServicesController@dashboard` | POS dashboard data |
| GET | `/api/pos-module/service-categories` | ✓ | `PosServicesController@categoriesIndex` | List service categories |
| POST | `/api/pos-module/service-categories` | ✓ | `PosServicesController@categoriesStore` | Create service category |
| PUT | `/api/pos-module/service-categories/{id}` | ✓ | `PosServicesController@categoriesUpdate` | Update category |
| DELETE | `/api/pos-module/service-categories/{id}` | ✓ | `PosServicesController@categoriesDestroy` | Delete category |
| GET | `/api/pos-module/services` | ✓ | `PosServicesController@servicesIndex` | List services |
| POST | `/api/pos-module/services` | ✓ | `PosServicesController@servicesStore` | Create service |
| GET | `/api/pos-module/services/{id}` | ✓ | `PosServicesController@servicesShow` | Get service details |
| PUT | `/api/pos-module/services/{id}` | ✓ | `PosServicesController@servicesUpdate` | Update service |
| DELETE | `/api/pos-module/services/{id}` | ✓ | `PosServicesController@servicesDestroy` | Delete service |

### 7.2 POS Operations

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| POST | `/api/pos-module/sale` | ✓ | `PosServicesController@saleStore` | Process POS sale |
| GET | `/api/pos-module/service-logs` | ✓ | `PosServicesController@serviceLogsIndex` | List service logs |
| GET | `/api/pos-module/service-logs/{id}` | ✓ | `PosServicesController@serviceLogsShow` | Get service log details |
| POST | `/api/pos-module/service-logs/{logId}/notes` | ✓ | `PosServicesController@serviceLogNoteStore` | Add note to service log |
| GET | `/api/pos-module/quick-payments` | ✓ | `PosServicesController@quickPaymentsIndex` | List quick payments |
| POST | `/api/pos-module/quick-payments` | ✓ | `PosServicesController@quickPaymentsStore` | Process quick payment |
| GET | `/api/pos-module/pos-transactions` | ✓ | `PosServicesController@posTransactionsIndex` | List POS transactions |
| POST | `/api/pos-module/pos-transactions/{id}/void` | ✓ | `PosServicesController@voidTransaction` | Void transaction |
| POST | `/api/pos-module/pos-transactions/{id}/refund` | ✓ | `PosServicesController@refundTransaction` | Process refund |
| GET | `/api/pos-module/reservations/{reservationId}/services` | ✓ | `PosServicesController@reservationServicesIndex` | List reservation services |
| POST | `/api/pos-module/reservations/{reservationId}/services` | ✓ | `PosServicesController@reservationServicesStore` | Add service to reservation |

### 7.3 Qoyod Integration

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/pos-module/service-qoyods` | ✓ | `PosServicesController@qoyodIndex` | List Qoyod mappings |
| POST | `/api/pos-module/service-qoyods` | ✓ | `PosServicesController@qoyodStore` | Create Qoyod mapping |
| PUT | `/api/pos-module/service-qoyods/{id}` | ✓ | `PosServicesController@qoyodUpdate` | Update Qoyod mapping |
| DELETE | `/api/pos-module/service-qoyods/{id}` | ✓ | `PosServicesController@qoyodDestroy` | Delete Qoyod mapping |

---

## 8. Guests & Companies

### 8.1 Guest Directory

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/guests-module/guests` | ✓ | `GuestsCompaniesController@guestsIndex` | List guests |
| GET | `/api/guests-module/guests/{id}` | ✓ | `GuestsCompaniesController@guestsShow` | Get guest details |

### 8.2 Customer Profiles

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/guests-module/customers` | ✓ | `GuestsCompaniesController@customersIndex` | List customers |
| POST | `/api/guests-module/customers` | ✓ | `GuestsCompaniesController@customersStore` | Create customer |
| GET | `/api/guests-module/customers/{id}` | ✓ | `GuestsCompaniesController@customersShow` | Get customer details |
| PUT | `/api/guests-module/customers/{id}` | ✓ | `GuestsCompaniesController@customersUpdate` | Update customer |
| DELETE | `/api/guests-module/customers/{id}` | ✓ | `GuestsCompaniesController@customersDestroy` | Delete customer |
| POST | `/api/guests-module/customers/{id}/restore` | ✓ | `GuestsCompaniesController@customersRestore` | Restore customer |
| POST | `/api/guests-module/customers/merge` | ✓ | `GuestsCompaniesController@mergeCustomers` | Merge duplicate customers |
| GET | `/api/guests-module/customers/{customerId}/notes` | ✓ | `GuestsCompaniesController@customerNotesIndex` | List customer notes |
| POST | `/api/guests-module/customers/{customerId}/notes` | ✓ | `GuestsCompaniesController@customerNotesStore` | Add customer note |
| DELETE | `/api/guests-module/customers/{customerId}/notes/{noteId}` | ✓ | `GuestsCompaniesController@customerNotesDestroy` | Delete customer note |

### 8.3 Companies

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/guests-module/companies` | ✓ | `GuestsCompaniesController@companiesIndex` | List companies |
| POST | `/api/guests-module/companies` | ✓ | `GuestsCompaniesController@companiesStore` | Create company |
| GET | `/api/guests-module/companies/{id}` | ✓ | `GuestsCompaniesController@companiesShow` | Get company details |
| PUT | `/api/guests-module/companies/{id}` | ✓ | `GuestsCompaniesController@companiesUpdate` | Update company |
| DELETE | `/api/guests-module/companies/{id}` | ✓ | `GuestsCompaniesController@companiesDestroy` | Delete company |
| GET | `/api/guests-module/companies/{companyId}/notes` | ✓ | `GuestsCompaniesController@companyNotesIndex` | List company notes |
| POST | `/api/guests-module/companies/{companyId}/notes` | ✓ | `GuestsCompaniesController@companyNotesStore` | Add company note |
| DELETE | `/api/guests-module/companies/{companyId}/notes/{noteId}` | ✓ | `GuestsCompaniesController@companyNotesDestroy` | Delete company note |

### 8.4 Company Groups

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/guests-module/company-groups` | ✓ | `GuestsCompaniesController@groupsIndex` | List company groups |
| POST | `/api/guests-module/company-groups` | ✓ | `GuestsCompaniesController@groupsStore` | Create company group |
| GET | `/api/guests-module/company-groups/{id}` | ✓ | `GuestsCompaniesController@groupsShow` | Get group details |
| PUT | `/api/guests-module/company-groups/{id}` | ✓ | `GuestsCompaniesController@groupsUpdate` | Update group |
| DELETE | `/api/guests-module/company-groups/{id}` | ✓ | `GuestsCompaniesController@groupsDestroy` | Delete group |
| GET | `/api/guests-module/company-groups/{id}/exposure` | ✓ | `GuestsCompaniesController@groupsExposure` | Get group exposure report |

### 8.5 Guest Management

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/guests-module/blocked-guests` | ✓ | `GuestsCompaniesController@blockedIndex` | List blocked guests |
| POST | `/api/guests-module/blocked-guests` | ✓ | `GuestsCompaniesController@blockedStore` | Block a guest |
| DELETE | `/api/guests-module/blocked-guests/{id}` | ✓ | `GuestsCompaniesController@blockedDestroy` | Unblock guest |
| GET | `/api/guests-module/turnaway-logs` | ✓ | `GuestsCompaniesController@turnawayLogsIndex` | List turnaway logs |
| POST | `/api/guests-module/turnaway-logs` | ✓ | `GuestsCompaniesController@turnawayLogsStore` | Log turnaway |
| GET | `/api/guests-module/turnaway-reasons` | ✓ | `GuestsCompaniesController@turnawayReasonsIndex` | List turnaway reasons |
| POST | `/api/guests-module/turnaway-reasons` | ✓ | `GuestsCompaniesController@turnawayReasonsStore` | Create turnaway reason |
| PUT | `/api/guests-module/turnaway-reasons/{id}` | ✓ | `GuestsCompaniesController@turnawayReasonsUpdate` | Update reason |
| DELETE | `/api/guests-module/turnaway-reasons/{id}` | ✓ | `GuestsCompaniesController@turnawayReasonsDestroy` | Delete reason |
| GET | `/api/guests-module/comments` | ✓ | `GuestsCompaniesController@commentsIndex` | List comments |
| POST | `/api/guests-module/comments` | ✓ | `GuestsCompaniesController@commentsStore` | Create comment |
| DELETE | `/api/guests-module/comments/{id}` | ✓ | `GuestsCompaniesController@commentsDestroy` | Delete comment |
| GET | `/api/guests-module/highlights` | ✓ | `GuestsCompaniesController@highlightsIndex` | List highlights |
| POST | `/api/guests-module/highlights` | ✓ | `GuestsCompaniesController@highlightsStore` | Create highlight |
| PUT | `/api/guests-module/highlights/{id}` | ✓ | `GuestsCompaniesController@highlightsUpdate` | Update highlight |
| DELETE | `/api/guests-module/highlights/{id}` | ✓ | `GuestsCompaniesController@highlightsDestroy` | Delete highlight |

---

## 9. Finance

### 9.1 Cashier Shifts

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/cashier-shifts` | ✓ | `CashierShiftController@index` | List cashier shifts |
| GET | `/api/cashier-shifts/active` | ✓ | `CashierShiftController@activeShift` | Get active shift |
| POST | `/api/cashier-shifts/open` | ✓ | `CashierShiftController@open` | Open new shift |
| POST | `/api/cashier-shifts/{shift}/close` | ✓ | `CashierShiftController@close` | Close shift |
| POST | `/api/cashier-shifts/{shift}/approve` | ✓ | `CashierShiftController@approve` | Approve shift |
| GET | `/api/cashier-shifts/{shift}` | ✓ | `CashierShiftController@show` | Get shift details |
| GET | `/api/cashier-shifts/{shift}/transactions` | ✓ | `CashierShiftController@transactions` | Get shift transactions |

### 9.2 Commissions

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/commissions` | ✓ | `CommissionController@index` | List commissions |
| GET | `/api/commissions/summary` | ✓ | `CommissionController@summary` | Commission summary |
| POST | `/api/commissions/{payment}/approve` | ✓ | `CommissionController@approve` | Approve commission |
| POST | `/api/commissions/{payment}/pay` | ✓ | `CommissionController@pay` | Pay commission |
| POST | `/api/commissions/{payment}/cancel` | ✓ | `CommissionController@cancel` | Cancel commission |

### 9.3 City Ledger & AR

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/city-ledger/dashboard` | ✓ | `CityLedgerController@dashboard` | City ledger dashboard |
| GET | `/api/city-ledger/aging` | ✓ | `CityLedgerController@agingReport` | AR aging report |
| GET | `/api/city-ledger/export` | ✓ | `CityLedgerController@export` | Export city ledger |
| GET | `/api/ar/invoice-transfers` | ✓ | `InvoiceTransferController@index` | List invoice transfers |
| GET | `/api/ar/invoice-transfers/export` | ✓ | `InvoiceTransferController@export` | Export transfers |
| POST | `/api/ar/invoice-transfer` | ✓ | `InvoiceTransferController@store` | Transfer invoice to AR |

### 9.4 Promissories

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/promissories` | ✓ | `PromissoryController@index` | List promissories |
| GET | `/api/promissories/payment-logs` | ✓ | `PromissoryController@paymentLogs` | List payment logs |
| POST | `/api/promissories/{promissory}/apply-payment` | ✓ | `PromissoryController@applyPayment` | Apply payment |
| POST | `/api/promissories/payment-logs/{log}/reverse` | ✓ | `PromissoryController@reversePayment` | Reverse payment |

### 9.5 Financial Management

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/financial/{module}` | ✓ | `FinancialManagementController@index` | Get financial module data |
| POST | `/api/financial/{type}/drafts` | ✓ | `FinancialManagementController@storeDraft` | Store financial draft |
| POST | `/api/financial/{type}/confirm` | ✓ | `FinancialManagementController@confirm` | Confirm financial transaction |
| POST | `/api/finance/payment-correction` | ✓ | `PaymentCorrectionController@correct` | Correct payment |
| GET | `/api/finance/payment-corrections` | ✓ | `PaymentCorrectionController@index` | List payment corrections |

### 9.6 Adjustments

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| POST | `/api/adjustments` | ✓ | `RoomAdjustmentController@store` | Create adjustment |
| GET | `/api/reservations/{reservation}/adjustments` | ✓ | `RoomAdjustmentController@index` | List reservation adjustments |

---

## 10. Master Data

### 10.1 Sources (Travel Agents)

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/sources` | ✓ | `SourceController@index` | List sources |
| POST | `/api/sources` | ✓ | `SourceController@store` | Create source |
| GET | `/api/sources/{source}` | ✓ | `SourceController@show` | Get source details |
| PUT | `/api/sources/{source}` | ✓ | `SourceController@update` | Update source |
| DELETE | `/api/sources/{source}` | ✓ | `SourceController@destroy` | Delete source |

### 10.2 Company Profiles

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/company-profiles` | ✓ | `CompanyProfileController@index` | List company profiles |
| POST | `/api/company-profiles` | ✓ | `CompanyProfileController@store` | Create profile |
| GET | `/api/company-profiles/{id}` | ✓ | `CompanyProfileController@show` | Get profile |
| PUT | `/api/company-profiles/{id}` | ✓ | `CompanyProfileController@update` | Update profile |
| DELETE | `/api/company-profiles/{id}` | ✓ | `CompanyProfileController@destroy` | Delete profile |
| POST | `/api/company-profiles/{id}/restore` | ✓ | `CompanyProfileController@restore` | Restore profile |
| POST | `/api/company-profiles/drafts` | ✓ | `CompanyProfileController@saveDraft` | Save draft |
| GET | `/api/company-profiles/drafts/latest` | ✓ | `CompanyProfileController@latestDraft` | Get latest draft |

### 10.3 Company Groups

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/company-groups` | ✓ | `CompanyGroupController@index` | List company groups |
| POST | `/api/company-groups` | ✓ | `CompanyGroupController@store` | Create group |
| GET | `/api/company-groups/{id}` | ✓ | `CompanyGroupController@show` | Get group |
| PUT | `/api/company-groups/{id}` | ✓ | `CompanyGroupController@update` | Update group |
| DELETE | `/api/company-groups/{id}` | ✓ | `CompanyGroupController@destroy` | Delete group |
| POST | `/api/company-groups/{companyGroup}/link` | ✓ | `CompanyGroupController@linkCompany` | Link company to group |
| POST | `/api/company-profiles/{companyProfile}/unlink` | ✓ | `CompanyGroupController@unlinkCompany` | Unlink company |

### 10.4 Room Management

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/rooms` | ✓ | `RoomController@index` | List rooms |
| POST | `/api/rooms` | ✓ | `RoomController@store` | Create room |
| PUT | `/api/rooms/{room}` | ✓ | `RoomController@update` | Update room |
| DELETE | `/api/rooms/{room}` | ✓ | `RoomController@destroy` | Delete room |
| GET | `/api/rooms/metrics` | ✓ | `RoomController@metrics` | Room metrics |
| GET | `/api/rooms/filters` | ✓ | `RoomController@filters` | Room filters |
| GET | `/api/rooms/export` | ✓ | `RoomController@export` | Export rooms |
| GET | `/api/rooms/availability` | ✓ | `RoomController@availability` | Check availability |
| GET | `/api/rooms/availability/list` | ✓ | `RoomController@availability` | List available rooms |

### 10.5 Guests

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/guests` | ✓ | `GuestController@index` | List guests |
| POST | `/api/guests` | ✓ | `GuestController@store` | Create guest |
| PUT | `/api/guests/{guest}` | ✓ | `GuestController@update` | Update guest |
| DELETE | `/api/guests/{guest}` | ✓ | `GuestController@destroy` | Delete guest |

### 10.6 Service Categories

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/service-categories` | ✓ | `ServiceCategoryController@index` | List categories |
| POST | `/api/service-categories` | ✓ | `ServiceCategoryController@store` | Create category |
| GET | `/api/service-categories/users` | ✓ | `ServiceCategoryController@users` | List users by category |
| GET | `/api/service-categories/{serviceCategory}` | ✓ | `ServiceCategoryController@show` | Get category |
| PUT | `/api/service-categories/{serviceCategory}` | ✓ | `ServiceCategoryController@update` | Update category |
| DELETE | `/api/service-categories/{serviceCategory}` | ✓ | `ServiceCategoryController@destroy` | Delete category |

### 10.7 POS Catalog

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/pos/stores` | ✓ | `PosController@stores` | List POS stores |
| GET | `/api/pos/categories` | ✓ | `PosController@categories` | List POS categories |
| GET | `/api/pos/sub-categories` | ✓ | `PosController@subCategories` | List sub-categories |
| GET | `/api/pos/brands` | ✓ | `PosController@brands` | List brands |
| GET | `/api/pos/products` | ✓ | `PosController@products` | List products |
| GET | `/api/pos/services` | ✓ | `PosController@services` | List services |
| POST | `/api/pos/services` | ✓ | `PosController@createService` | Create service |
| GET | `/api/pos/transactions` | ✓ | `PosController@transactions` | List transactions |
| GET | `/api/pos/cart` | ✓ | `PosController@cart` | Get cart |
| POST | `/api/pos/cart/items` | ✓ | `PosController@updateCart` | Update cart |
| DELETE | `/api/pos/cart/items` | ✓ | `PosController@clearCart` | Clear cart |
| GET | `/api/pos/checkout` | ✓ | `PosController@checkout` | Get checkout data |

### 10.8 Lookups

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/lookups/countries` | ✓ | `LookupController@countries` | List countries |
| GET | `/api/lookups/cities` | ✓ | `LookupController@cities` | List cities |
| GET | `/api/search` | ✓ | `SearchController@autocomplete` | Search autocomplete |

### 10.9 Dynamic Master Data

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/master-data/tables` | ✓ | `MasterDataController@tables` | List available tables |
| GET | `/api/master-data/{table}` | ✓ | `MasterDataController@index` | List table data |
| POST | `/api/master-data/{table}` | ✓ | `MasterDataController@store` | Create record |
| PUT | `/api/master-data/{table}/{id}` | ✓ | `MasterDataController@update` | Update record |
| DELETE | `/api/master-data/{table}/{id}` | ✓ | `MasterDataController@destroy` | Delete record |

### 10.10 Units Housing

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/units/filters` | ✓ | `UnitHousingController@filters` | Unit filters |
| GET | `/api/units/floors` | ✓ | `UnitHousingController@floors` | List floors |
| GET | `/api/units/daily-status` | ✓ | `UnitHousingController@dailyStatus` | Daily status board |
| POST | `/api/units/check-in` | ✓ | `UnitHousingController@checkIn` | Unit check-in |
| POST | `/api/units/check-out` | ✓ | `UnitHousingController@checkOut` | Unit check-out |
| GET | `/api/reservations/{reservation}/balance` | ✓ | `UnitHousingController@getBalance` | Get balance |
| PUT | `/api/units/{unit}/status` | ✓ | `UnitHousingController@updateStatus` | Update unit status |

---

## 11. Reports

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/reports/deposits` | ✓ | `ReportsController@deposits` | Deposits report |
| GET | `/api/reports/withdraws` | ✓ | `ReportsController@withdraws` | Withdrawals report |
| GET | `/api/reports/safe-movement` | ✓ | `ReportsController@safeMovement` | Safe movement report |
| GET | `/api/reports/customer-movement` | ✓ | `ReportsController@customerMovement` | Customer movement |
| GET | `/api/reports/services` | ✓ | `ReportsController@services` | Services report |
| GET | `/api/reports/monthly` | ✓ | `ReportsController@monthly` | Monthly report |
| GET | `/api/reports/units-movement` | ✓ | `ReportsController@unitsMovement` | Units movement |
| GET | `/api/reports/occupancy` | ✓ | `ReportsController@occupancy` | Occupancy report |
| GET | `/api/reports/cleaning` | ✓ | `ReportsController@cleaning` | Cleaning report |
| GET | `/api/reports/maintenance` | ✓ | `ReportsController@maintenance` | Maintenance report |
| GET | `/api/reports/transfers` | ✓ | `ReportsController@transfers` | Transfers report |
| GET | `/api/reports/revenues` | ✓ | `ReportsController@revenues` | Revenues report |
| GET | `/api/reports/resources` | ✓ | `ReportsController@resources` | Resources report |
| GET | `/api/reports/contracts` | ✓ | `ReportsController@contracts` | Contracts report |
| GET | `/api/reports/invoices` | ✓ | `ReportsController@invoices` | Invoices report |
| GET | `/api/reports/daily` | ✓ | `ReportsController@daily` | Daily report |
| GET | `/api/reports/metabase/{id}` | ✓ | `MetabaseController@getDashboardUrl` | Metabase dashboard URL |

---

## 12. System

### 12.1 Settings

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/settings/{category}` | ✗ | `SettingsController@index` | Get settings |
| POST | `/api/settings/global` | ✗ | `SettingsController@updateGlobal` | Update global settings |

### 12.2 Uploads

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| POST | `/api/uploads` | ✓ | `UploadController@store` | Upload file |
| DELETE | `/api/uploads/{uploadedMedia}` | ✓ | `UploadController@destroy` | Delete upload |

### 12.3 Room Status Logs

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/room-status-logs` | ✓ | `RoomStatusLogController@index` | List status logs |
| GET | `/api/room-status-logs/timeline/{unit}` | ✓ | `RoomStatusLogController@timeline` | Status timeline |

### 12.4 Night Audit

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/night-audit/preflight` | ✓ | `NightAuditController@preflight` | Preflight checks |
| POST | `/api/night-audit/run` | ✓ | `NightAuditController@run` | Run night audit |
| POST | `/api/night-audit/rerun/{id}` | ✓ | `NightAuditController@rerun` | Rerun night audit |
| GET | `/api/night-audit/status` | ✓ | `NightAuditController@status` | Get audit status |
| POST | `/api/night-audit/init-date` | ✓ | `NightAuditController@setInitialDate` | Set initial date |

### 12.5 No-Show Rules

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/no-show-rules/preview` | ✓ | `NoShowChargeRuleController@previewAffected` | Preview affected reservations |
| GET | `/api/no-show-rules` | ✓ | `NoShowChargeRuleController@index` | List no-show rules |
| POST | `/api/no-show-rules` | ✓ | `NoShowChargeRuleController@store` | Create rule |
| GET | `/api/no-show-rules/{id}` | ✓ | `NoShowChargeRuleController@show` | Get rule |
| PUT | `/api/no-show-rules/{id}` | ✓ | `NoShowChargeRuleController@update` | Update rule |
| DELETE | `/api/no-show-rules/{id}` | ✓ | `NoShowChargeRuleController@destroy` | Delete rule |

### 12.6 Stay Charge Configs

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/stay-charge-configs/calculate` | ✓ | `StayChargeConfigController@calculate` | Calculate stay charges |
| GET | `/api/stay-charge-configs` | ✓ | `StayChargeConfigController@index` | List configs |
| POST | `/api/stay-charge-configs` | ✓ | `StayChargeConfigController@store` | Create config |
| GET | `/api/stay-charge-configs/{id}` | ✓ | `StayChargeConfigController@show` | Get config |
| PUT | `/api/stay-charge-configs/{id}` | ✓ | `StayChargeConfigController@update` | Update config |
| DELETE | `/api/stay-charge-configs/{id}` | ✓ | `StayChargeConfigController@destroy` | Delete config |

### 12.7 User Grouping & Permissions

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/user-groups/roles` | ✓ | `UserGroupingController@roles` | List roles |
| POST | `/api/user-groups/roles` | ✓ | `UserGroupingController@storeRole` | Create role |
| PUT | `/api/user-groups/roles/{role}` | ✓ | `UserGroupingController@updateRole` | Update role |
| DELETE | `/api/user-groups/roles/{role}` | ✓ | `UserGroupingController@deleteRole` | Delete role |
| POST | `/api/user-groups/roles/{role}/duplicate` | ✓ | `UserGroupingController@duplicateRole` | Duplicate role |
| GET | `/api/user-groups/users` | ✓ | `UserGroupingController@users` | List users |
| POST | `/api/user-groups/roles/{role}/assign-users` | ✓ | `UserGroupingController@assignUsers` | Assign users to role |
| GET | `/api/user-groups/roles/{role}/permissions` | ✓ | `UserGroupingController@matrix` | Get permission matrix |
| PUT | `/api/user-groups/roles/{role}/permissions/{permission}` | ✓ | `UserGroupingController@updatePermission` | Update permission |
| GET | `/api/user-groups/teams` | ✓ | `UserGroupingController@teams` | List teams |

### 12.8 Bookings

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| POST | `/api/bookings` | ✓ | `BookingController@store` | Create booking |
| PUT | `/api/bookings/{booking}` | ✓ | `BookingController@update` | Update booking |

### 12.9 Leads

| Method | Endpoint | Auth | Controller | Description |
|--------|----------|------|------------|-------------|
| GET | `/api/leads` | ✓ | `LeadController@index` | List leads |
| GET | `/api/leads/stats` | ✓ | `LeadController@stats` | Lead statistics |
| PUT | `/api/leads/{lead}` | ✓ | `LeadController@update` | Update lead |
| DELETE | `/api/leads/{lead}` | ✓ | `LeadController@destroy` | Delete lead |
| POST | `/api/leads/submit` | ✗ | `LeadController@store` | Public lead submission |

---

## Authentication

### Login
```bash
curl -X POST http://usama-fandaqah.test/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
```

**Response:**
```json
{
  "token": "YOUR_SANCTUM_TOKEN",
  "user": { ... }
}
```

### Authenticated Requests
```bash
curl -X GET http://usama-fandaqah.test/api/reservations \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## Summary Statistics

| Category | Public | Auth Required | Total |
|----------|--------|---------------|-------|
| Authentication | 1 | 1 | 2 |
| Dashboard | 0 | 14 | 14 |
| Reservations | 0 | 45 | 45 |
| Rooms & Housekeeping | 0 | 35 | 35 |
| Front Desk | 0 | 26 | 26 |
| POS & Services | 0 | 27 | 27 |
| Guests & Companies | 0 | 35 | 35 |
| Finance | 0 | 26 | 26 |
| Master Data | 2 | 50 | 52 |
| Reports | 0 | 16 | 16 |
| System | 0 | 23 | 23 |
| **TOTAL** | **3** | **273** | **276** |

---

## Notes

- **Authentication**: All protected routes require `Authorization: Bearer {token}` header
- **Content-Type**: Use `application/json` for POST/PUT requests
- **Rate Limiting**: Not currently configured
- **Pagination**: Most list endpoints support `?page=` and `?per_page=` parameters
- **Filtering**: Many endpoints support query string filters (e.g., `?search=`, `?status=`)
