<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Audit Locks</h1>
        <p class="text-slate-500 text-sm">Manage business date locks and audit restrictions</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Current Business Date</p>
        <p class="text-2xl font-black text-slate-900">May 17, 2026</p>
        <p class="text-emerald-500 text-xs font-bold mt-1 flex items-center gap-1">
          <Clock class="w-3 h-3" />
          Updated: 04:00 AM
        </p>
      </div>
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Locked Dates</p>
        <p class="text-2xl font-black text-slate-900">12</p>
        <p class="text-slate-500 text-xs font-bold mt-1">Preventing modifications</p>
      </div>
      <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Active Restrictions</p>
        <p class="text-2xl font-black text-slate-900">3</p>
        <p class="text-amber-500 text-xs font-bold mt-1">Require admin override</p>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
      <div class="p-4 border-b border-slate-100 flex justify-between items-center">
        <h2 class="text-lg font-bold text-slate-800">Locked Business Dates</h2>
        <div class="text-sm text-slate-500">
          Showing all locked periods
        </div>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 text-slate-500 text-left text-sm">
            <tr>
              <th class="font-medium px-4 py-3">Business Date</th>
              <th class="font-medium px-4 py-3">Locked By</th>
              <th class="font-medium px-4 py-3">Reason</th>
              <th class="font-medium px-4 py-3">Locked At</th>
              <th class="font-medium px-4 py-3">Status</th>
              <th class="font-medium px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3 font-medium text-slate-800">May 16, 2026</td>
              <td class="px-4 py-3 text-slate-700">System (Night Audit)</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Auto-Lock</span>
              </td>
              <td class="px-4 py-3 text-slate-700">May 17, 04:05 AM</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">Active</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <MoreHorizontal class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3 font-medium text-slate-800">May 15, 2026</td>
              <td class="px-4 py-3 text-slate-700">Ahmad Al-Mansouri</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-800">Manual</span>
              </td>
              <td class="px-4 py-3 text-slate-700">May 16, 02:30 PM</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">Active</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <MoreHorizontal class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3 font-medium text-slate-800">May 14, 2026</td>
              <td class="px-4 py-3 text-slate-700">System (Night Audit)</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Auto-Lock</span>
              </td>
              <td class="px-4 py-3 text-slate-700">May 15, 04:12 AM</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">Active</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <MoreHorizontal class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="hover:bg-slate-50/50">
              <td class="px-4 py-3 font-medium text-slate-800">May 10, 2026</td>
              <td class="px-4 py-3 text-slate-700">Fatima Al-Zahra</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-rose-100 text-rose-800">Financial Review</span>
              </td>
              <td class="px-4 py-3 text-slate-700">May 11, 10:15 AM</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-rose-100 text-rose-800">Override Required</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <MoreHorizontal class="w-4 h-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Lock Override Request</h2>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Select Date to Unlock</label>
            <input 
              type="date" 
              class="input w-full" 
              value="2026-05-10"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Reason for Override</label>
            <textarea 
              class="input w-full" 
              rows="3"
              placeholder="Provide a reason for requesting the audit lock override..."
            ></textarea>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Required Authorization Level</label>
            <select class="input w-full">
              <option>Department Manager</option>
              <option>Financial Manager</option>
              <option>General Manager</option>
              <option>System Administrator</option>
            </select>
          </div>
          
          <button class="btn-danger w-full py-2.5 rounded-lg text-sm font-medium flex items-center justify-center gap-2">
            <Unlock class="w-4 h-4" />
            Submit Override Request
          </button>
        </div>
      </div>
      
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Recent Override Activities</h2>
        
        <div class="space-y-4">
          <div class="p-3 bg-slate-50 rounded-lg">
            <div class="flex justify-between">
              <div class="font-medium text-slate-800">May 10, 2026 unlocked</div>
              <div class="text-sm text-slate-500">2 hours ago</div>
            </div>
            <div class="text-sm text-slate-600 mt-1">By: Khalid Al-Harbi (GM)</div>
            <div class="text-xs text-slate-500 mt-1">Reason: Financial audit adjustment</div>
          </div>
          
          <div class="p-3 bg-slate-50 rounded-lg">
            <div class="flex justify-between">
              <div class="font-medium text-slate-800">May 5, 2026 unlocked</div>
              <div class="text-sm text-slate-500">May 15, 2026</div>
            </div>
            <div class="text-sm text-slate-600 mt-1">By: Layla Al-Ghamdi (FM)</div>
            <div class="text-xs text-slate-500 mt-1">Reason: Correct billing error</div>
          </div>
          
          <div class="p-3 bg-slate-50 rounded-lg">
            <div class="flex justify-between">
              <div class="font-medium text-slate-800">Apr 28, 2026 unlocked</div>
              <div class="text-sm text-slate-500">May 1, 2026</div>
            </div>
            <div class="text-sm text-slate-600 mt-1">By: Ahmad Al-Mansouri (Admin)</div>
              <div class="text-xs text-slate-500 mt-1">Reason: System migration requirement</div>
          </div>
          
          <div class="p-3 bg-slate-50 rounded-lg">
            <div class="flex justify-between">
              <div class="font-medium text-slate-800">Apr 25, 2026 unlocked</div>
              <div class="text-sm text-slate-500">Apr 26, 2026</div>
            </div>
            <div class="text-sm text-slate-600 mt-1">By: Fatima Al-Zahra (FM)</div>
            <div class="text-xs text-slate-500 mt-1">Reason: Tax report correction</div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="text-lg font-bold text-slate-800 mb-4">Lock Configuration</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 class="font-medium text-slate-800 mb-3">Automatic Lock Settings</h3>
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <div>
                <div class="text-sm font-medium">Auto-lock after night audit</div>
                <div class="text-xs text-slate-500">Lock date after successful audit</div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
              </label>
            </div>
            
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <div>
                <div class="text-sm font-medium">Lock previous 3 days</div>
                <div class="text-xs text-slate-500">Prevent modifications to recent dates</div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
              </label>
            </div>
          </div>
        </div>
        
        <div>
          <h3 class="font-medium text-slate-800 mb-3">Access Permissions</h3>
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <div>
                <div class="text-sm font-medium">Financial Manager Override</div>
                <div class="text-xs text-slate-500">Can unlock financial dates</div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
              </label>
            </div>
            
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <div>
                <div class="text-sm font-medium">General Manager Override</div>
                <div class="text-xs text-slate-500">Full unlock permissions</div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Clock, MoreHorizontal, Unlock } from 'lucide-vue-next';

// This is a placeholder component for the audit locks page
</script>