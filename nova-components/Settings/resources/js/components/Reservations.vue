<template>
    <div>
        <!-- Return Button -->
        <div class="mb-6">
            <button
                @click="$router.push({ name: 'channel-manager' })"
                class="group inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 mr-2 text-gray-400 group-hover:text-blue-500 transition-colors duration-200"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"
                    />
                </svg>

            </button>
        </div>

        <div class="flex items-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="49" height="49" viewBox="0 0 24 24" fill="none" stroke="#b5b5b5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3">
                <path d="M17 3a2.85 2.85 0 1 1 0 5.7"></path>
                <path d="M7 3a2.85 2.85 0 1 0 0 5.7"></path>
                <path d="M21 20V10c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2Z"></path>
                <path d="M8 16h8"></path>
                <path d="M8 12h8"></path>
            </svg>
            <heading>Channel Reservations</heading>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <card class="bg-gradient-to-r from-blue-500 to-blue-600">
                <div class="p-4">
                    <h3 class="text-white text-lg font-semibold mb-2">{{ __('Total Reservations') }}</h3>
                    <p class="text-white text-2xl font-bold">{{ totalReservations }}</p>
                </div>
            </card>
            <card class="bg-gradient-to-r from-green-500 to-green-600">
                <div class="p-4">
                    <h3 class="text-white text-lg font-semibold mb-2">{{ __('Total Revenue') }}</h3>
                    <p class="text-white text-2xl font-bold">{{ totalRevenue }} {{ __('SAR') }}</p>
                </div>
            </card>


        </div>

        <!-- Filters -->
        <card class="mb-6">
            <div class="p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Filters') }}</h3>
                </div>
                <div class="flex flex-wrap items-end gap-4">
                    <!-- Search Filter -->
                    <div class="flex-1 min-w-[200px]">
                        <!-- <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Search') }}</label> -->
                        <div class="flex">
                            <input
                                type="text"
                                v-model="filters.search"
                                @keyup.enter="performSearch"
                                :placeholder="`${__('Search by ID, Guest name...')}`"
                                class="flex-1 form-control form-input border-gray-300 rounded-l-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <!-- <button
                                @click="performSearch"
                                class="px-4 bg-blue-600 text-white rounded-r-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                {{ __('Search') }}
                            </button> -->
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="w-[400px]">
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Date Range') }}</label>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <input
                                    type="date"
                                    v-model="filters.dateFrom"
                                    class="w-full form-control form-input border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    @change="performSearch"
                                    placeholder="From"
                                >
                            </div>
                            <div class="flex-1">
                                <input
                                    type="date"
                                    v-model="filters.dateTo"
                                    class="w-full form-control form-input border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    @change="performSearch"
                                    placeholder="To"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="w-[200px]" style="width: 199px;">
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Status') }}</label>
                        <select
                            v-model="filters.status"
                            @change="performSearch"
                            class="w-full form-control form-select border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="all">{{ __('All Status') }}</option>
                            <option value="book">{{ __('Book') }}</option>
                            <option value="cancel">{{ __('Cancel') }}</option>
                            <option value="modify">{{ __('Modify') }}</option>
                        </select>
                    </div>

                    <!-- Is Posted Filter -->
                    <div class="w-[200px]" style="width: 199px;">
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Is Posted') }}</label>
                        <select
                            v-model="filters.isPosted"
                            @change="performSearch"
                            class="w-full form-control form-select border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="all">{{ __('All') }}</option>
                            <option value="true">{{ __('Yes') }}</option>
                            <option value="false">{{ __('No') }}</option>
                        </select>
                    </div>

                    <!-- Is Open Filter -->
                    <div class="w-[200px]" style="width: 199px;">
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Reservation Status') }}</label>
                        <select
                            v-model="filters.isOpen"
                            @change="performSearch"
                            class="w-full form-control form-select border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="all">{{ __('All Reservations') }}</option>
                            <option value="true">{{ __('Opened') }}</option>
                            <option value="false">{{ __('Closed') }}</option>
                        </select>
                    </div>

                    <!-- Sort By Filter -->
                    <div class="w-[200px]" style="width: 199px;">
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Sort By') }}</label>
                        <select
                            v-model="filters.sortBy"
                            @change="performSearch"
                            class="w-full form-control form-select border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="created_at">{{ __('Created Date') }}</option>
                            <option value="checkin">{{ __('Check-in Date') }}</option>
                        </select>
                    </div>

                    <!-- Channel Filter -->
                    <div class="w-[200px]" style="width: 199px;">
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('Channel') }}</label>
                        <select
                            v-model="filters.channel"
                            @change="performSearch"
                            class="w-full form-control form-select border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="all">{{ __('All Channels') }}</option>
                            <option v-for="channel in availableChannels" :key="channel" :value="channel">
                                {{ channel }}
                            </option>
                        </select>
                    </div>

                    <!-- Clear Filters Button -->
                    <div class="flex items-end">
                        <button
                            @click="clearFilters"
                            class="inline-flex items-center p-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            :class="{'opacity-50 cursor-not-allowed': !hasActiveFilters}"
                            :disabled="!hasActiveFilters"
                            :title="__('Reset Filters')"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </card>

        <!-- Reservations Table -->
        <card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Booking ID') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Guest') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Channel') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Check In') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Check Out') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Amount') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Is Posted') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Unit Number') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Created At') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Updated At') }}</th>
                            <th v-if="showUnitSelection" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Select Unit') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="reservation in paginatedReservations" :key="reservation.id" :class="[
                            'hover:bg-gray-50 transition-colors duration-150',
                            {
                                'bg-green-50': reservation.is_open === true || reservation.is_open === 1,
                                'bg-red-50': reservation.is_open === false || reservation.is_open === 0
                            }
                        ]">
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <button
                                    @click="openReservationDetails(reservation)"
                                    class="text-blue-600 hover:text-blue-800 hover:underline"
                                >
                                    {{ reservation.booking_id }}
                                </button>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ getGuestName(reservation) }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span
                                    :class="[
                                        'px-2 py-1 inline-flex text-xs font-medium rounded-full',
                                        getChannelClass(getReservationChannel(reservation))
                                    ]"
                                >
                                    {{ getReservationChannel(reservation) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(reservation.checkin) }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(reservation.checkout) }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatAmount(reservation.amount) }} SAR
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span
                                    :class="{
                                        'px-2 py-1 inline-flex text-xs font-medium rounded-full': true,
                                        'bg-green-100 text-green-800': reservation.is_posted,
                                        'bg-red-100 text-red-800': !reservation.is_posted
                                    }"
                                >
                                    {{ reservation.is_posted ? __('Yes') : __('No') }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ reservation.unit || '-' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span
                                    :class="{
                                        'px-3 py-1 inline-flex text-sm font-medium rounded-md cursor-pointer': true,
                                        'text-white bg-green-600 hover:bg-green-700': reservation.action === 'book',
                                        'text-white bg-blue-600 hover:bg-blue-700': reservation.action === 'modify',
                                        'text-white bg-red-600 hover:bg-red-700': reservation.action === 'cancel'
                                    }"
                                    @click="
                                        reservation.action === 'book' ? bookReservation(reservation) :
                                        reservation.action === 'modify' ? modifyReservation(reservation) :
                                        cancelReservation(reservation)
                                    "
                                >
                                    {{ __(reservation.action) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDateTime(reservation.created_at) }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDateTime(reservation.updated_at) }}
                            </td>
                            <td v-if="showUnitSelection && !reservation.is_posted" class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex items-center space-x-2">
                                    <select
                                        v-model="reservation.selected_unit"
                                        @change="handleUnitSelection(reservation)"
                                        class="form-control form-select border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">{{ __('Select Unit') }}</option>
                                        <option v-for="unit in reservation.available_units" :key="unit.id" :value="unit.id">
                                            {{ unit.name }} ({{ unit.unit_number }})
                                        </option>
                                    </select>
                                    <button
                                    style="   margin-right: 13px;"
                                        v-if="reservation.selected_unit"
                                        @click="bookReservationWithUnit(reservation)"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                    >
                                        {{ __('Book') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button @click="previousPage" :disabled="currentPage === 1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        {{ __('Previous') }}
                    </button>
                    <button @click="nextPage" :disabled="currentPage >= totalPages" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        {{ __('Next') }}
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            {{ __('Showing') }}
                            <span class="font-medium">{{ paginationStart }}</span>
                            {{ __('to') }}
                            <span class="font-medium">{{ paginationEnd }}</span>
                            {{ __('of') }}
                            <span class="font-medium">{{ filteredReservations.length }}</span>
                            {{ __('results') }}
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <button @click="previousPage" :disabled="currentPage === 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                {{ __('Previous') }}
                            </button>
                            <button v-for="page in displayedPages" :key="page" @click="goToPage(page)" :class="[currentPage === page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50', 'relative inline-flex items-center px-4 py-2 border text-sm font-medium']">
                                {{ page }}
                            </button>
                            <button @click="nextPage" :disabled="currentPage >= totalPages" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                {{ __('Next') }}
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </card>

        <!-- Reservation Details Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        {{ __('Reservation Details') }}
                                    </h3>
                                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-500">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div v-if="selectedReservation" class="bg-gray-50 rounded-lg p-6">
                                    <!-- Booking Information -->
                                    <div class="mb-6">
                                        <h4 class="text-sm font-semibold text-gray-600 uppercase mb-3">{{ __('Booking Information') }}</h4>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-sm text-gray-600">{{ __('Booking ID') }}</p>
                                                <p class="text-base font-medium">{{ selectedReservation.booking_id }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">{{ __('Status') }}</p>
                                                <span
                                                    :class="{
                                                        'px-3 py-1 inline-flex text-sm font-medium rounded-md': true,
                                                        'text-white bg-green-600': selectedReservation.action === 'book',
                                                        'text-white bg-blue-600': selectedReservation.action === 'modify',
                                                        'text-white bg-red-600': selectedReservation.action === 'cancel'
                                                    }"
                                                >
                                                    {{ selectedReservation.action.charAt(0).toUpperCase() + selectedReservation.action.slice(1) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Guest Information -->
                                    <div class="mb-6">
                                        <h4 class="text-sm font-semibold text-gray-600 uppercase mb-3">{{ __('Guest Information') }}</h4>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-sm text-gray-600">{{ __('Guest Name') }}</p>
                                                <p class="text-base font-medium">{{ getGuestName(selectedReservation) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">{{ __('Email') }}</p>
                                                <p class="text-base font-medium">{{ JSON.parse(selectedReservation.guest).email }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stay Information -->
                                    <div class="mb-6">
                                        <h4 class="text-sm font-semibold text-gray-600 uppercase mb-3">{{ __('Stay Information') }}</h4>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-sm text-gray-600">{{ __('Check In') }}</p>
                                                <p class="text-base font-medium">{{ formatDate(selectedReservation.checkin) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">{{ __('Check Out') }}</p>
                                                <p class="text-base font-medium">{{ formatDate(selectedReservation.checkout) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">{{ __('Amount') }}</p>
                                                <p class="text-base font-medium">{{ formatAmount(selectedReservation.amount) }} SAR</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">{{ __('Is Posted') }}</p>
                                                <span
                                                    :class="{
                                                        'px-2 py-1 inline-flex text-xs font-medium rounded-full': true,
                                                        'bg-green-100 text-green-800': selectedReservation.is_posted,
                                                        'bg-red-100 text-red-800': !selectedReservation.is_posted
                                                    }"
                                                >
                                                    {{ selectedReservation.is_posted ? __('Yes') : __('No') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Room Information -->
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-600 uppercase mb-3">{{ __('Room Information') }}</h4>
                                        <div v-for="(room, roomIndex) in JSON.parse(selectedReservation.rooms)" :key="roomIndex" class="bg-white rounded-lg border border-gray-200 mb-4 last:mb-0">
                                            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                                                <div class="grid grid-cols-3 gap-4">
                                                    <div>
                                                        <p class="text-xs text-gray-500">{{ __('Room Code') }}</p>
                                                        <p class="font-medium">{{ room.roomCode }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-gray-500">{{ __('Rate Plan') }}</p>
                                                        <p class="font-medium">{{ room.rateplanCode }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-gray-500">{{ __('Guest Name') }}</p>
                                                        <p class="font-medium">{{ room.guestName }}</p>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <p class="text-xs text-gray-500">{{ __('Occupancy') }}</p>
                                                    <div class="flex gap-4 mt-1">
                                                        <p class="text-sm">
                                                            <span class="font-medium">{{ room.occupancy.adults }}</span> {{ __('Adults') }}
                                                        </p>
                                                        <p class="text-sm">
                                                            <span class="font-medium">{{ room.occupancy.children }}</span> {{ __('Children') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-4">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead>
                                                        <tr>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Sell Rate') }}</th>
                                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Created At') }}</th>
                                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Updated At') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-200">
                                                        <tr v-for="(price, index) in room.prices" :key="index" class="hover:bg-gray-50">
                                                            <td class="px-4 py-2 text-sm text-gray-900">{{ formatDate(price.date) }}</td>
                                                            <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ price.sellRate }} {{ __('SAR') }}</td>
                                                            <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ formatDateTime(price.created_at) }}</td>
                                                            <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ formatDateTime(price.updated_at) }}</td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot class="bg-gray-50">
                                                        <tr>
                                                            <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ __('Total') }}</td>
                                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 text-right">
                                                                {{ room.prices.reduce((sum, price) => sum + price.sellRate, 0) }} {{ __('SAR') }}
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Popup Modal -->
        <div v-if="showSuccessModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showSuccessModal = false"></div>

                <!-- Modal panel -->
                <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transition-all sm:max-w-sm sm:w-full sm:p-6">
                    <div>
                        <!-- Success icon -->
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Success!
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ successMessage }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6">
                        <button
                            type="button"
                            class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm"
                            @click="showSuccessModal = false"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'Reservations',
    data() {
        return {
            team_id: Nova.app.currentTeam.id,
            lang: Nova.config.local,
            aiosell: Nova.app.currentTeam.enable_aiosell,
            reservations: [],
            currentPage: 1,
            perPage: 10,
            filters: {
                search: '',
                dateFrom: '',
                dateTo: '',
                status: 'all',
                isPosted: 'all',
                isOpen: 'true',
                sortBy: 'created_at',
                channel: 'all'
            },
            loading: false,
            showModal: false,
            selectedReservation: null,
            showUnitSelection: true,
            showSuccessModal: false,
            successMessage: ''
        }
    },
    computed: {
        availableChannels() {
            // Get unique channels from all reservations
            const channels = new Set();
            this.reservations.forEach(reservation => {
                const channel = this.getReservationChannel(reservation);
                if (channel) {
                    channels.add(channel);
                }
            });
            return Array.from(channels).sort();
        },
        filteredReservations() {
            return this.reservations.filter(reservation => {
                // Apply filters here
                if (this.filters.search) {
                    const searchTerm = this.filters.search.toLowerCase();
                    const guest = JSON.parse(reservation.guest);
                    if (!(reservation.booking_id.toLowerCase().includes(searchTerm) ||
                        guest.firstName.toLowerCase().includes(searchTerm) ||
                        guest.lastName.toLowerCase().includes(searchTerm))) {
                        return false;
                    }
                }

                // Apply channel filter
                if (this.filters.channel !== 'all') {
                    const channel = this.getReservationChannel(reservation);
                    if (channel !== this.filters.channel) {
                        return false;
                    }
                }

                return true;
            });
        },
        paginatedReservations() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.filteredReservations.slice(start, end);
        },
        totalPages() {
            return Math.ceil(this.filteredReservations.length / this.perPage);
        },
        displayedPages() {
            const pages = [];
            for (let i = 1; i <= this.totalPages; i++) {
                pages.push(i);
            }
            return pages;
        },
        paginationStart() {
            return ((this.currentPage - 1) * this.perPage) + 1;
        },
        paginationEnd() {
            return Math.min(this.currentPage * this.perPage, this.filteredReservations.length);
        },
        totalReservations() {
            return this.filteredReservations.length;
        },
        totalRevenue() {
            return this.filteredReservations.reduce((sum, reservation) => {
                const amount = typeof reservation.amount === 'string' ?
                    parseFloat(reservation.amount) : reservation.amount;
                return sum + amount;
            }, 0).toFixed(2);
        },
        averageStay() {
            return (this.filteredReservations.reduce((sum, reservation) => {
                const checkIn = new Date(reservation.checkin);
                const checkOut = new Date(reservation.checkout);
                return sum + (checkOut - checkIn) / (1000 * 60 * 60 * 24);
            }, 0) / this.filteredReservations.length || 0).toFixed(1);
        },
        upcomingCheckins() {
            const today = new Date();
            return this.filteredReservations.filter(reservation => {
                const checkIn = new Date(reservation.checkin);
                return checkIn >= today;
            }).length;
        },
        hasActiveFilters() {
            return this.filters.search !== '' ||
                   this.filters.dateFrom !== '' ||
                   this.filters.dateTo !== '' ||
                   this.filters.status !== 'all' ||
                   this.filters.isPosted !== 'all' ||
                   this.filters.isOpen !== 'true' ||
                   this.filters.sortBy !== 'created_at' ||
                   this.filters.channel !== 'all';
        }
    },
    methods: {
        async fetchAvailableUnits(reservation) {
            try {
                const response = await axios.post('/nova-vendor/calender/units/get-available-units', {
                    start: reservation.checkin,
                    end: reservation.checkout,
                    rent_type: '1' // Always 1 as specified
                });

                // Filter out units that have reservations
                const availableUnits = response.data.units.filter(unit => !unit.has_reservation);

                // Add available units to the reservation object
                this.$set(reservation, 'available_units', availableUnits);
                this.$set(reservation, 'selected_unit', '');

            } catch (error) {
                console.error('Error fetching available units:', error);
            }
        },

        async handleUnitSelection(reservation) {
            if (reservation.selected_unit) {
                // Store the selected unit but don't make the API call yet
                console.log(`Unit ${reservation.selected_unit} selected for reservation ${reservation.booking_id}`);
            }
        },

        async fetchReservations() {
            try {
                const response = await axios.get('/nova-vendor/settings/get-reservations', {
                    params: {
                        team_id: this.team_id
                    }
                });
                this.reservations = response.data;

                // For each unposted reservation, fetch available units
                for (const reservation of this.reservations) {
                    if (!reservation.is_posted) {
                        await this.fetchAvailableUnits(reservation);
                    }
                }
            } catch (error) {
                console.error('Error fetching reservations:', error);
            }
        },
        formatDate(date) {
            const d = new Date(date);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
        },
        formatDateTime(datetime) {
            if (!datetime) return '-';
            const d = new Date(datetime);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const hours = d.getHours().toString().padStart(2, '0');
            const minutes = d.getMinutes().toString().padStart(2, '0');
            return `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()} ${hours}:${minutes}`;
        },
        formatAmount(amount) {
            if (typeof amount === 'string') {
                try {
                    amount = JSON.parse(amount);
                    return amount.amountAfterTax.toFixed(2);
                } catch (e) {
                    return parseFloat(amount).toFixed(2);
                }
            }
            return parseFloat(amount).toFixed(2);
        },
        getGuestName(reservation) {
            const guest = JSON.parse(reservation.guest);
            return `${guest.firstName} ${guest.lastName}`;
        },
        getReservationChannel(reservation) {
            try {
                const request = typeof reservation.request === 'string'
                    ? JSON.parse(reservation.request)
                    : reservation.request;
                return (request && request.channel) ? request.channel : 'Unknown';
            } catch (e) {
                return 'Unknown';
            }
        },
        getChannelClass(channel) {
            const classes = {
                'booking.com': 'bg-blue-100 text-blue-800',
                'expedia': 'bg-yellow-100 text-yellow-800',
                'airbnb': 'bg-red-100 text-red-800',
                'Unknown': 'bg-gray-100 text-gray-800'
            };
            return classes[channel.toLowerCase()] || 'bg-gray-100 text-gray-800';
        },
        getStatusClass(reservation) {
            const today = new Date();
            const checkIn = new Date(reservation.checkin);
            const checkOut = new Date(reservation.checkout);

            if (today < checkIn) {
                return 'bg-yellow-100 text-yellow-800';
            } else if (today >= checkIn && today <= checkOut) {
                return 'bg-green-100 text-green-800';
            } else {
                return 'bg-gray-100 text-gray-800';
            }
        },
        getReservationStatus(reservation) {
            const today = new Date();
            const checkIn = new Date(reservation.checkin);
            const checkOut = new Date(reservation.checkout);

            if (today < checkIn) {
                return 'Upcoming';
            } else if (today >= checkIn && today <= checkOut) {
                return 'Checked In';
            } else {
                return 'Checked Out';
            }
        },
        viewDetails(reservation) {
            // Implement view details functionality
            console.log('View details for reservation:', reservation);
        },
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },
        goToPage(page) {
            this.currentPage = page;
        },
        async performSearch() {
            this.loading = true;
            try {
                const response = await axios.get('/nova-vendor/settings/get-reservations', {
                    params: {
                        team_id: this.team_id,
                        search: this.filters.search,
                        date_from: this.filters.dateFrom,
                        date_to: this.filters.dateTo,
                        status: this.filters.status,
                        is_posted: this.filters.isPosted,
                        is_open: this.filters.isOpen,
                        sort_by: this.filters.sortBy,
                        channel: this.filters.channel
                    }
                });

                this.reservations = response.data;

                // Fetch available units for all unposted reservations after filtering
                const unpostedReservations = this.reservations.filter(reservation => !reservation.is_posted);
                for (const reservation of unpostedReservations) {
                    await this.fetchAvailableUnits(reservation);
                }
            } catch (error) {
                console.error('Error fetching reservations:', error);
                Nova.$emit('error', 'Failed to fetch reservations');
            } finally {
                this.loading = false;
            }
        },
        bookReservation(reservation) {
            console.log('Booking reservation:', reservation)
            // Implement booking logic here
        },
        modifyReservation(reservation) {
            console.log('Modifying reservation:', reservation)
            // Implement modification logic here
        },
        cancelReservation(reservation) {
            console.log('Canceling reservation:', reservation)
            // Implement cancellation logic here
        },
        openReservationDetails(reservation) {
            this.selectedReservation = reservation;
            this.showModal = true;
        },
        async bookReservationWithUnit(reservation) {
            try {
                // Get the original request or create new object if it doesn't exist
                let request = reservation.request || {};

                // If request is a string, try to parse it, otherwise use as is
                if (typeof request === 'string') {
                    try {
                        request = JSON.parse(request);
                    } catch (e) {
                        request = {};
                    }
                }

                // Add only the selected unit ID
                request.selected_unit = reservation.selected_unit;

                const response = await axios.post('/api/Aiossel/create', {
                    ...reservation,
                    request: JSON.stringify(request)
                });
                console.log(response);

                if (response.data.success) {
                    // Update the reservation status in the UI
                    reservation.is_posted = true;

                    // Refresh the reservations data
                    await this.fetchReservations();
                    // Re-apply current filters
                    await this.performSearch();

                    // Show success message without translation function
                    // this.successMessage = 'Reservation has been successfully booked!';
                    // this.showSuccessModal = true;
                }
            } catch (error) {
                console.error('Error booking reservation:', error);
                // Use Nova's toast notification correctly
                Nova.$emit('error', 'Failed to book reservation');
            }
        },
        clearFilters() {
            this.filters = {
                search: '',
                dateFrom: '',
                dateTo: '',
                status: 'all',
                isPosted: 'all',
                isOpen: 'true',
                sortBy: 'created_at',
                channel: 'all'
            };
            // performSearch will be automatically triggered by the watcher
        }
    },
    mounted() {
        this.fetchReservations();
    },
    watch: {
        'filters': {
            handler: function() {
                this.performSearch();
            },
            deep: true
        }
    }
}
</script>

<style scoped>
/* Add any additional component styles here */
</style>
