<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Guest Ledger</h1>
        <p class="text-slate-500 text-sm">Track all financial transactions for individual guests</p>
      </div>
      <div class="flex gap-3">
        <button class="btn-outline px-4 py-2 rounded-lg text-sm font-medium">
          <Filter class="w-4 h-4 mr-2 inline" />
          Filter
        </button>
        <button class="btn-primary px-4 py-2 rounded-lg text-sm font-medium">
          <Download class="w-4 h-4 mr-2 inline" />
          Export
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Outstanding</p>
        <p class="text-2xl font-black text-slate-900">12,450 SAR</p>
        <p class="text-emerald-500 text-xs font-bold mt-1 flex items-center gap-1">
          <TrendingDown class="w-3 h-3" />
          8% from last week
        </p>
      </div>
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Active Guests</p>
        <p class="text-2xl font-black text-slate-900">42</p>
        <p class="text-slate-500 text-xs font-bold mt-1">Currently staying</p>
      </div>
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Avg. Balance</p>
        <p class="text-2xl font-black text-slate-900">296 SAR</p>
        <p class="text-slate-500 text-xs font-bold mt-1">Per active guest</p>
      </div>
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">High Balances</p>
        <p class="text-2xl font-black text-slate-900">3</p>
        <p class="text-rose-500 text-xs font-bold mt-1">Over 1,000 SAR</p>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
      <div class="p-4 border-b border-slate-100 flex justify-between items-center">
        <h2 class="text-lg font-bold text-slate-800">Guest Ledger Summary</h2>
        <div class="text-sm text-slate-500">
          Showing 20 of 42 active guests
        </div>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 text-slate-500 text-left text-sm">
            <tr>
              <th class="font-medium px-4 py-3">Guest</th>
              <th class="font-medium px-4 py-3">Reservation ID</th>
              <th class="font-medium px-4 py-3">Check-In</th>
              <th class="font-medium px-4 py-3">Nights</th>
              <th class="font-medium px-4 py-3">Charges</th>
              <th class="font-medium px-4 py-3">Payments</th>
              <th class="font-medium px-4 py-3">Balance</th>
              <th class="font-medium px-4 py-3">Status</th>
              <th class="font-medium px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3 font-medium text-slate-800">Mohammed Al-Saud</td>
              <td class="px-4 py-3 text-slate-700">#RES-2026-05-012</td>
              <td class="px-4 py-3 text-slate-700">May 10</td>
              <td class="px-4 py-3 text-slate-700">7</td>
              <td class="px-4 py-3 text-slate-700">3,250 SAR</td>
              <td class="px-4 py-3 text-slate-700">3,250 SAR</td>
              <td class="px-4 py-3 font-bold text-emerald-600">0 SAR</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">Settled</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <Eye class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3 font-medium text-slate-800">Fatima Al-Zahra</td>
              <td class="px-4 py-3 text-slate-700">#RES-2026-05-018</td>
              <td class="px-4 py-3 text-slate-700">May 15</td>
              <td class="px-4 py-3 text-slate-700">2</td>
              <td class="px-4 py-3 text-slate-700">1,850 SAR</td>
              <td class="px-4 py-3 text-slate-700">1,500 SAR</td>
              <td class="px-4 py-3 font-bold text-amber-600">350 SAR</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-800">Due</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <Eye class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3 font-medium text-slate-800">Khalid Al-Harbi</td>
              <td class="px-4 py-3 text-slate-700">#RES-2026-05-022</td>
              <td class="px-4 py-3 text-slate-700">May 16</td>
              <td class="px-4 py-3 text-slate-700">1</td>
              <td class="px-4 py-3 text-slate-700">1,200 SAR</td>
              <td class="px-4 py-3 text-slate-700">0 SAR</td>
              <td class="px-4 py-3 font-bold text-rose-600">1,200 SAR</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-rose-100 text-rose-800">Outstanding</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <Eye class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="hover:bg-slate-50/50 bg-amber-50">
              <td class="px-4 py-3 font-medium text-amber-800">Layla Al-Ghamdi</td>
              <td class="px-4 py-3 text-amber-700">#RES-2026-04-120</td>
              <td class="px-4 py-3 text-amber-700">Apr 28</td>
              <td class="px-4 py-3 text-amber-700">19</td>
              <td class="px-4 py-3 text-amber-700">12,450 SAR</td>
              <td class="px-4 py-3 text-amber-700">10,000 SAR</td>
              <td class="px-4 py-3 font-bold text-amber-600">2,450 SAR</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-800">Extended Stay</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-amber-600 hover:text-amber-800">
                  <Eye class="w-4 h-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Balance Distribution</h2>
        
        <div class="space-y-4">
          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-slate-700">Settled Accounts (0 SAR)</span>
              <span class="text-sm font-bold text-slate-700">12</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-emerald-500 h-2 rounded-full" style="width: 28%"></div>
            </div>
          </div>
          
          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-slate-700">Under 500 SAR</span>
              <span class="text-sm font-bold text-slate-700">22</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-blue-500 h-2 rounded-full" style="width: 52%"></div>
            </div>
          </div>
          
          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-slate-700">500 - 1,000 SAR</span>
              <span class="text-sm font-bold text-slate-700">5</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-amber-500 h-2 rounded-full" style="width: 12%"></div>
            </div>
          </div>
          
          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-slate-700">Over 1,000 SAR</span>
              <span class="text-sm font-bold text-slate-700">3</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-rose-500 h-2 rounded-full" style="width: 8%"></div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Recent Activities</h2>
        
        <div class="space-y-4">
          <div class="flex items-start pb-4 border-b border-slate-100 last:border-0 last:pb-0">
            <div class="mr-3 mt-1">
              <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                <Check class="w-4 h-4 text-emerald-600" />
              </div>
            </div>
            <div>
              <h3 class="font-medium text-slate-800">Payment received</h3>
              <p class="text-sm text-slate-600">From Mohammed Al-Saud - 350 SAR</p>
              <p class="text-xs text-slate-400 mt-1">2 minutes ago</p>
            </div>
          </div>
          
          <div class="flex items-start pb-4 border-b border-slate-100 last:border-0 last:pb-0">
            <div class="mr-3 mt-1">
              <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                <DollarSign class="w-4 h-4 text-blue-600" />
              </div>
            </div>
            <div>
              <h3 class="font-medium text-slate-800">Additional charge</h3>
              <p class="text-sm text-slate-600">Room service for Layla Al-Ghamdi - 120 SAR</p>
              <p class="text-xs text-slate-400 mt-1">1 hour ago</p>
            </div>
          </div>
          
          <div class="flex items-start">
            <div class="mr-3 mt-1">
              <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                <AlertCircle class="w-4 h-4 text-amber-600" />
              </div>
            </div>
            <div>
              <h3 class="font-medium text-slate-800">High balance alert</h3>
              <p class="text-sm text-slate-600">Khalid Al-Harbi balance exceeds 1,000 SAR</p>
              <p class="text-xs text-slate-400 mt-1">3 hours ago</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Filter, Download, Eye, TrendingDown, Check, DollarSign, AlertCircle } from 'lucide-vue-next';

// This is a placeholder component for the guest ledger page
</script>