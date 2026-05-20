<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Audit Logs</h1>
        <p class="text-slate-500 text-sm">View historical records of night audit processes</p>
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
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Audits</p>
        <p class="text-2xl font-black text-slate-900">142</p>
        <p class="text-emerald-500 text-xs font-bold mt-1 flex items-center gap-1">
          <TrendingUp class="w-3 h-3" />
          12% from last month
        </p>
      </div>
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Successful</p>
        <p class="text-2xl font-black text-slate-900">138</p>
        <p class="text-emerald-500 text-xs font-bold mt-1">97.2% success rate</p>
      </div>
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Errors</p>
        <p class="text-2xl font-black text-slate-900">4</p>
        <p class="text-rose-500 text-xs font-bold mt-1">2.8% failure rate</p>
      </div>
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Avg. Duration</p>
        <p class="text-2xl font-black text-slate-900">3.2 min</p>
        <p class="text-emerald-500 text-xs font-bold mt-1">Improved 15%</p>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
      <div class="p-4 border-b border-slate-100 flex justify-between items-center">
        <h2 class="text-lg font-bold text-slate-800">Recent Audit Logs</h2>
        <div class="text-sm text-slate-500">
          Showing latest 20 records
        </div>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 text-slate-500 text-left text-sm">
            <tr>
              <th class="font-medium px-4 py-3">Date & Time</th>
              <th class="font-medium px-4 py-3">User</th>
              <th class="font-medium px-4 py-3">Status</th>
              <th class="font-medium px-4 py-3">Duration</th>
              <th class="font-medium px-4 py-3">Records Processed</th>
              <th class="font-medium px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3">
                <div class="font-medium">May 15, 2026</div>
                <div class="text-slate-500 text-sm">04:05:22 AM</div>
              </td>
              <td class="px-4 py-3 text-slate-700">Ahmad Al-Mansouri</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">Success</span>
              </td>
              <td class="px-4 py-3 text-slate-700">2 min 45 sec</td>
              <td class="px-4 py-3 text-slate-700">1,248</td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <Eye class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3">
                <div class="font-medium">May 14, 2026</div>
                <div class="text-slate-500 text-sm">04:02:18 AM</div>
              </td>
              <td class="px-4 py-3 text-slate-700">Fatima Al-Zahra</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">Success</span>
              </td>
              <td class="px-4 py-3 text-slate-700">3 min 12 sec</td>
              <td class="px-4 py-3 text-slate-700">1,235</td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <Eye class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3">
                <div class="font-medium">May 13, 2026</div>
                <div class="text-slate-500 text-sm">04:10:05 AM</div>
              </td>
              <td class="px-4 py-3 text-slate-700">Khalid Al-Harbi</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-rose-100 text-rose-800">Error</span>
              </td>
              <td class="px-4 py-3 text-slate-700">1 min 08 sec</td>
              <td class="px-4 py-3 text-slate-700">842</td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <Eye class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3">
                <div class="font-medium">May 12, 2026</div>
                <div class="text-slate-500 text-sm">04:03:42 AM</div>
              </td>
              <td class="px-4 py-3 text-slate-700">Layla Al-Ghamdi</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">Success</span>
              </td>
              <td class="px-4 py-3 text-slate-700">2 min 58 sec</td>
              <td class="px-4 py-3 text-slate-700">1,267</td>
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
  </div>
</template>

<script setup>
import { Filter, Download, Eye, TrendingUp } from 'lucide-vue-next';

// This is a placeholder component for the night audit logs page
</script>