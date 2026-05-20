<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Deposit Ledger</h1>
        <p class="text-slate-500 text-sm">Track all deposits and refund transactions</p>
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
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Deposits Held</p>
        <p class="text-2xl font-black text-slate-900">84,250 SAR</p>
        <p class="text-emerald-500 text-xs font-bold mt-1 flex items-center gap-1">
          <TrendingDown class="w-3 h-3" />
          2% from last month
        </p>
      </div>
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Active Deposits</p>
        <p class="text-2xl font-black text-slate-900">32</p>
        <p class="text-slate-500 text-xs font-bold mt-1">From 28 guests</p>
      </div>
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Avg. Deposit</p>
        <p class="text-2xl font-black text-slate-900">2,633 SAR</p>
        <p class="text-slate-500 text-xs font-bold mt-1">Per active deposit</p>
      </div>
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Refunds Processed</p>
        <p class="text-2xl font-black text-slate-900">12</p>
        <p class="text-emerald-500 text-xs font-bold mt-1">This month</p>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
      <div class="p-4 border-b border-slate-100 flex justify-between items-center">
        <h2 class="text-lg font-bold text-slate-800">Deposit Transactions</h2>
        <div class="text-sm text-slate-500">
          Showing latest 20 transactions
        </div>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 text-slate-500 text-left text-sm">
            <tr>
              <th class="font-medium px-4 py-3">Guest</th>
              <th class="font-medium px-4 py-3">Reservation ID</th>
              <th class="font-medium px-4 py-3">Type</th>
              <th class="font-medium px-4 py-3">Amount</th>
              <th class="font-medium px-4 py-3">Date</th>
              <th class="font-medium px-4 py-3">Method</th>
              <th class="font-medium px-4 py-3">Status</th>
              <th class="font-medium px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3 font-medium text-slate-800">Mohammed Al-Saud</td>
              <td class="px-4 py-3 text-slate-700">#RES-2026-05-012</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Deposit</span>
              </td>
              <td class="px-4 py-3 text-slate-700 font-medium">1,500 SAR</td>
              <td class="px-4 py-3 text-slate-700">May 10, 2026</td>
              <td class="px-4 py-3 text-slate-700">Credit Card</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">Active</span>
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
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Deposit</span>
              </td>
              <td class="px-4 py-3 text-slate-700 font-medium">2,000 SAR</td>
              <td class="px-4 py-3 text-slate-700">May 15, 2026</td>
              <td class="px-4 py-3 text-slate-700">Cash</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">Active</span>
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
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Deposit</span>
              </td>
              <td class="px-4 py-3 text-slate-700 font-medium">1,800 SAR</td>
              <td class="px-4 py-3 text-slate-700">May 16, 2026</td>
              <td class="px-4 py-3 text-slate-700">Bank Transfer</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-rose-100 text-rose-800">Refunded</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <Eye class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3 font-medium text-slate-800">Layla Al-Ghamdi</td>
              <td class="px-4 py-3 text-slate-700">#RES-2026-04-120</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-rose-100 text-rose-800">Refund</span>
              </td>
              <td class="px-4 py-3 text-slate-700 font-medium">-500 SAR</td>
              <td class="px-4 py-3 text-slate-700">May 14, 2026</td>
              <td class="px-4 py-3 text-slate-700">Bank Transfer</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">Processed</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
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
        <h2 class="text-lg font-bold text-slate-800 mb-4">Deposit Distribution</h2>
        
        <div class="space-y-4">
          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-slate-700">Cash Deposits</span>
              <span class="text-sm font-bold text-slate-700">18,500 SAR</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-blue-500 h-2 rounded-full" style="width: 22%"></div>
            </div>
          </div>
          
          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-slate-700">Credit Card</span>
              <span class="text-sm font-bold text-slate-700">32,400 SAR</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-emerald-500 h-2 rounded-full" style="width: 38%"></div>
            </div>
          </div>
          
          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-slate-700">Bank Transfer</span>
              <span class="text-sm font-bold text-slate-700">21,350 SAR</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-amber-500 h-2 rounded-full" style="width: 25%"></div>
            </div>
          </div>
          
          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-slate-700">Other Methods</span>
              <span class="text-sm font-bold text-slate-700">12,000 SAR</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="bg-rose-500 h-2 rounded-full" style="width: 15%"></div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Deposit Status</h2>
        
        <div class="grid grid-cols-2 gap-6">
          <div>
            <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-100">
              <p class="text-2xl font-black text-blue-700">32</p>
              <p class="text-sm text-blue-600 font-medium">Active</p>
            </div>
          </div>
          <div>
            <div class="text-center p-4 bg-emerald-50 rounded-xl border border-emerald-100">
              <p class="text-2xl font-black text-emerald-700">12</p>
              <p class="text-sm text-emerald-600 font-medium">Refunded</p>
            </div>
          </div>
          <div>
            <div class="text-center p-4 bg-amber-50 rounded-xl border border-amber-100">
              <p class="text-2xl font-black text-amber-700">5</p>
              <p class="text-sm text-amber-600 font-medium">Pending Refund</p>
            </div>
          </div>
          <div>
            <div class="text-center p-4 bg-rose-50 rounded-xl border border-rose-100">
              <p class="text-2xl font-black text-rose-700">3</p>
              <p class="text-sm text-rose-600 font-medium">On Hold</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold text-slate-800">Deposit Refund Requests</h2>
        <button class="btn-outline px-3 py-1.5 rounded-lg text-sm font-medium">
          <Plus class="w-4 h-4 inline mr-1" />
          New Request
        </button>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 text-slate-500 text-left text-sm">
            <tr>
              <th class="font-medium px-4 py-3">Guest</th>
              <th class="font-medium px-4 py-3">Reservation ID</th>
              <th class="font-medium px-4 py-3">Requested Date</th>
              <th class="font-medium px-4 py-3">Amount</th>
              <th class="font-medium px-4 py-3">Reason</th>
              <th class="font-medium px-4 py-3">Status</th>
              <th class="font-medium px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3 font-medium text-slate-800">Ahmad Al-Mansouri</td>
              <td class="px-4 py-3 text-slate-700">#RES-2026-05-025</td>
              <td class="px-4 py-3 text-slate-700">May 16, 2026</td>
              <td class="px-4 py-3 text-slate-700 font-medium">1,500 SAR</td>
              <td class="px-4 py-3 text-slate-700">Early departure</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-800">Pending</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <Check class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3 font-medium text-slate-800">Sarah Al-Dosari</td>
              <td class="px-4 py-3 text-slate-700">#RES-2026-05-019</td>
              <td class="px-4 py-3 text-slate-700">May 15, 2026</td>
              <td class="px-4 py-3 text-slate-700 font-medium">2,200 SAR</td>
              <td class="px-4 py-3 text-slate-700">Reservation cancelled</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-800">Pending</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <Check class="w-4 h-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Filter, Download, Eye, TrendingDown, Check, Plus } from 'lucide-vue-next';

// This is a placeholder component for the deposit ledger page
</script>