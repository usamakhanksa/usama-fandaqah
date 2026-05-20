<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Historical Backfill</h1>
        <p class="text-slate-500 text-sm">Process historical data for dates before system implementation</p>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="text-lg font-bold text-slate-800 mb-4">Backfill Configuration</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Start Date</label>
            <input 
              type="date" 
              class="input w-full" 
              value="2025-01-01"
            />
            <p class="text-xs text-slate-500 mt-1">Beginning date for historical data processing</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">End Date</label>
            <input 
              type="date" 
              class="input w-full" 
              value="2025-12-31"
            />
            <p class="text-xs text-slate-500 mt-1">End date for historical data processing</p>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Data Sources</label>
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <div>
                <div class="text-sm font-medium">Reservation Records</div>
                <div class="text-xs text-slate-500">Historical booking data</div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
              </label>
            </div>
            
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <div>
                <div class="text-sm font-medium">Financial Records</div>
                <div class="text-xs text-slate-500">Payment and transaction history</div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
              </label>
            </div>
            
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <div>
                <div class="text-sm font-medium">Occupancy Data</div>
                <div class="text-xs text-slate-500">Room status and availability</div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold text-slate-800">Data Validation</h2>
        <button class="btn-outline px-3 py-1.5 rounded-lg text-sm font-medium">
          <RefreshCw class="w-4 h-4 inline mr-1" />
          Validate
        </button>
      </div>
      
      <div class="space-y-4">
        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
          <div>
            <div class="text-sm font-medium">Reservation Data Completeness</div>
            <div class="text-xs text-slate-500">Check for missing required fields</div>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-24 bg-slate-200 rounded-full h-2">
              <div class="bg-emerald-500 h-2 rounded-full" style="width: 98%"></div>
            </div>
            <span class="text-sm font-bold text-emerald-600">98%</span>
          </div>
        </div>
        
        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
          <div>
            <div class="text-sm font-medium">Financial Data Accuracy</div>
            <div class="text-xs text-slate-500">Validate amounts and currencies</div>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-24 bg-slate-200 rounded-full h-2">
              <div class="bg-emerald-500 h-2 rounded-full" style="width: 95%"></div>
            </div>
            <span class="text-sm font-bold text-emerald-600">95%</span>
          </div>
        </div>
        
        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
          <div>
            <div class="text-sm font-medium">Guest Information</div>
            <div class="text-xs text-slate-500">Validate contact details completeness</div>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-24 bg-slate-200 rounded-full h-2">
              <div class="bg-amber-500 h-2 rounded-full" style="width: 87%"></div>
            </div>
            <span class="text-sm font-bold text-amber-600">87%</span>
          </div>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="text-lg font-bold text-slate-800 mb-4">Processing Controls</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <h3 class="font-medium text-slate-800 mb-3">Batch Settings</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-xs text-slate-500 mb-1">Records Per Batch</label>
              <input 
                type="number" 
                class="input w-full" 
                value="500"
                min="10"
                max="10000"
              />
            </div>
            
            <div>
              <label class="block text-xs text-slate-500 mb-1">Pause Between Batches (ms)</label>
              <input 
                type="number" 
                class="input w-full" 
                value="1000"
                min="100"
                max="10000"
              />
            </div>
          </div>
        </div>
        
        <div>
          <h3 class="font-medium text-slate-800 mb-3">Processing Options</h3>
          <div class="space-y-3">
            <div class="flex items-center">
              <label class="inline-flex items-center">
                <input type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                <span class="ml-2 text-sm text-slate-700">Update existing records</span>
              </label>
            </div>
            
            <div class="flex items-center">
              <label class="inline-flex items-center">
                <input type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                <span class="ml-2 text-sm text-slate-700">Generate audit trail</span>
              </label>
            </div>
            
            <div class="flex items-center">
              <label class="inline-flex items-center">
                <input type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-slate-700">Send completion notification</span>
              </label>
            </div>
          </div>
        </div>
        
        <div>
          <h3 class="font-medium text-slate-800 mb-3">Safety Measures</h3>
          <div class="space-y-3">
            <div class="flex items-center">
              <label class="inline-flex items-center">
                <input type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                <span class="ml-2 text-sm text-slate-700">Create backup before processing</span>
              </label>
            </div>
            
            <div class="flex items-center">
              <label class="inline-flex items-center">
                <input type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                <span class="ml-2 text-sm text-slate-700">Stop on critical errors</span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="flex justify-end gap-4">
      <button class="btn-outline px-4 py-2 rounded-lg text-sm font-medium">
        Cancel
      </button>
      <button class="btn-primary px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
        <Database class="w-4 h-4" />
        Start Backfill Process
      </button>
    </div>
  </div>
</template>

<script setup>
import { RefreshCw, Database } from 'lucide-vue-next';

// This is a placeholder component for the night audit backfill page
</script>