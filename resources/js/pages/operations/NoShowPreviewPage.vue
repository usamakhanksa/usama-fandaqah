<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">No-Show Preview</h1>
        <p class="text-slate-500 text-sm">Preview reservations that will be marked as no-show based on current rules</p>
      </div>
      <button class="btn-primary px-4 py-2 rounded-lg text-sm font-medium">
        Run Preview
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Expected No-Shows</p>
        <p class="text-2xl font-black text-slate-900">8</p>
        <p class="text-rose-500 text-xs font-bold mt-1 flex items-center gap-1">
          <AlertTriangle class="w-3 h-3" />
          3 more than yesterday
        </p>
      </div>
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Potential Revenue Loss</p>
        <p class="text-2xl font-black text-slate-900">2,450 SAR</p>
        <p class="text-rose-500 text-xs font-bold mt-1">Based on first night charges</p>
      </div>
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">VIP Reservations Affected</p>
        <p class="text-2xl font-black text-slate-900">1</p>
        <p class="text-amber-500 text-xs font-bold mt-1">Requires manual review</p>
      </div>
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Corporate Bookings</p>
        <p class="text-2xl font-black text-slate-900">2</p>
        <p class="text-slate-500 text-xs font-bold mt-1">May impact relations</p>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
      <div class="p-4 border-b border-slate-100 flex justify-between items-center">
        <h2 class="text-lg font-bold text-slate-800">Upcoming Check-Ins (Today)</h2>
        <div class="text-sm text-slate-500">
          24 reservations scheduled for today
        </div>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 text-slate-500 text-left text-sm">
            <tr>
              <th class="font-medium px-4 py-3">Reservation ID</th>
              <th class="font-medium px-4 py-3">Guest Name</th>
              <th class="font-medium px-4 py-3">Scheduled Time</th>
              <th class="font-medium px-4 py-3">Status</th>
              <th class="font-medium px-4 py-3">Risk Level</th>
              <th class="font-medium px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3 font-medium text-slate-800">#RES-2026-05-001</td>
              <td class="px-4 py-3 text-slate-700">Mohammed Al-Saud</td>
              <td class="px-4 py-3 text-slate-700">03:00 PM</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Confirmed</span>
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">Low</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <Phone class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3 font-medium text-slate-800">#RES-2026-05-008</td>
              <td class="px-4 py-3 text-slate-700">Fatima Al-Zahra</td>
              <td class="px-4 py-3 text-slate-700">04:00 PM</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Confirmed</span>
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-800">Medium</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <Phone class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3 font-medium text-slate-800">#RES-2026-05-015</td>
              <td class="px-4 py-3 text-slate-700">Khalid Al-Harbi</td>
              <td class="px-4 py-3 text-slate-700">05:00 PM</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Confirmed</span>
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-rose-100 text-rose-800">High</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <Phone class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="hover:bg-slate-50/50 bg-amber-50">
              <td class="px-4 py-3 font-medium text-amber-800">#RES-2026-05-022</td>
              <td class="px-4 py-3 text-amber-700">Layla Al-Ghamdi (VIP)</td>
              <td class="px-4 py-3 text-amber-700">06:00 PM</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Confirmed</span>
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-rose-100 text-rose-800">Critical</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-amber-600 hover:text-amber-800">
                  <Phone class="w-4 h-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">No-Show Risk Factors</h2>
        
        <div class="space-y-4">
          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-slate-700">Booked more than 7 days ago</span>
              <span class="text-sm font-bold text-slate-700">34%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-amber-500 h-2 rounded-full" style="width: 34%"></div>
            </div>
          </div>
          
          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-slate-700">No confirmation call made</span>
              <span class="text-sm font-bold text-slate-700">67%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-rose-500 h-2 rounded-full" style="width: 67%"></div>
            </div>
          </div>
          
          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-slate-700">Free cancellation</span>
              <span class="text-sm font-bold text-slate-700">22%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-blue-500 h-2 rounded-full" style="width: 22%"></div>
            </div>
          </div>
          
          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-slate-700">Corporate booking</span>
              <span class="text-sm font-bold text-slate-700">8%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-emerald-500 h-2 rounded-full" style="width: 8%"></div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Recommended Actions</h2>
        
        <div class="space-y-3">
          <div class="flex items-start p-3 bg-blue-50 rounded-lg border border-blue-100">
            <Phone class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" />
            <div class="ml-3">
              <h3 class="font-medium text-blue-800">Confirmation Calls</h3>
              <p class="text-sm text-blue-600 mt-1">Contact 16 reservations that haven't been confirmed</p>
            </div>
          </div>
          
          <div class="flex items-start p-3 bg-amber-50 rounded-lg border border-amber-100">
            <AlertTriangle class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" />
            <div class="ml-3">
              <h3 class="font-medium text-amber-800">High-Risk Reservations</h3>
              <p class="text-sm text-amber-600 mt-1">4 reservations with high no-show probability</p>
            </div>
          </div>
          
          <div class="flex items-start p-3 bg-rose-50 rounded-lg border border-rose-100">
            <UserX class="w-5 h-5 text-rose-500 mt-0.5 flex-shrink-0" />
            <div class="ml-3">
              <h3 class="font-medium text-rose-800">VIP Reservation</h3>
              <p class="text-sm text-rose-600 mt-1">One VIP reservation at critical risk level</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Phone, AlertTriangle, UserX } from 'lucide-vue-next';

// This is a placeholder component for the no-show preview page
</script>