<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Rerun Audit</h1>
        <p class="text-slate-500 text-sm">Re-execute night audit for specific dates or resolve failed audits</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Rerun Options</h2>
        
        <div class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Select Date Range</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs text-slate-500 mb-1">Start Date</label>
                <input 
                  type="date" 
                  class="input w-full" 
                  value="2026-05-10"
                />
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">End Date</label>
                <input 
                  type="date" 
                  class="input w-full" 
                  value="2026-05-15"
                />
              </div>
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Audit Scope</label>
            <div class="space-y-3">
              <div class="flex items-center">
                <label class="inline-flex items-center">
                  <input type="radio" class="rounded text-blue-600 border-slate-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" name="scope" checked>
                  <span class="ml-2 text-sm text-slate-700">Only Failed Audits</span>
                </label>
              </div>
              <div class="flex items-center">
                <label class="inline-flex items-center">
                  <input type="radio" class="rounded text-blue-600 border-slate-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" name="scope">
                  <span class="ml-2 text-sm text-slate-700">All Audits in Date Range</span>
                </label>
              </div>
              <div class="flex items-center">
                <label class="inline-flex items-center">
                  <input type="radio" class="rounded text-blue-600 border-slate-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" name="scope">
                  <span class="ml-2 text-sm text-slate-700">Specific Business Date</span>
                </label>
              </div>
            </div>
          </div>
          
          <div class="pt-4">
            <div class="flex items-start">
              <div class="flex items-start">
                <input 
                  id="override-lock" 
                  type="checkbox" 
                  class="mt-1 rounded text-blue-600 border-slate-300 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                >
              </div>
              <div class="ml-3">
                <label for="override-lock" class="text-sm font-medium text-slate-700">Override existing audit locks</label>
                <p class="text-sm text-slate-500">Allow rerunning audits even if locked by previous runs</p>
              </div>
            </div>
          </div>
          
          <div class="pt-4">
            <button class="btn-danger px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
              <RefreshCw class="w-4 h-4" />
              Rerun Selected Audits
            </button>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Recent Failed Audits</h2>
        
        <div class="space-y-4">
          <div class="p-3 bg-rose-50 border border-rose-100 rounded-lg">
            <div class="font-medium text-rose-800">May 13, 2026</div>
            <div class="text-sm text-rose-600 mt-1">Processing Error</div>
            <div class="text-xs text-rose-500 mt-1">Connection timeout during financial sync</div>
            <button class="mt-2 text-xs btn-outline-danger py-1 px-2 rounded">
              Rerun Now
            </button>
          </div>
          
          <div class="p-3 bg-amber-50 border border-amber-100 rounded-lg">
            <div class="font-medium text-amber-800">May 10, 2026</div>
            <div class="text-sm text-amber-600 mt-1">Partial Completion</div>
            <div class="text-xs text-amber-500 mt-1">Some records failed to sync</div>
            <button class="mt-2 text-xs btn-outline-warning py-1 px-2 rounded">
              Rerun Now
            </button>
          </div>
          
          <div class="p-3 bg-slate-50 border border-slate-100 rounded-lg">
            <div class="font-medium text-slate-800">May 8, 2026</div>
            <div class="text-sm text-slate-600 mt-1">Completed Successfully</div>
            <div class="text-xs text-slate-500 mt-1">All records processed normally</div>
          </div>
          
          <div class="p-3 bg-slate-50 border border-slate-100 rounded-lg">
            <div class="font-medium text-slate-800">May 7, 2026</div>
            <div class="text-sm text-slate-600 mt-1">Completed Successfully</div>
            <div class="text-xs text-slate-500 mt-1">All records processed normally</div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="text-lg font-bold text-slate-800 mb-4">Audit Parameters</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <h3 class="font-medium text-slate-800 mb-2">Data Validation</h3>
          <ul class="text-sm text-slate-600 space-y-1">
            <li class="flex items-center gap-2">
              <CheckCircle class="w-4 h-4 text-emerald-500" />
              Reservation completeness
            </li>
            <li class="flex items-center gap-2">
              <CheckCircle class="w-4 h-4 text-emerald-500" />
              Financial reconciliation
            </li>
            <li class="flex items-center gap-2">
              <CheckCircle class="w-4 h-4 text-emerald-500" />
              Tax compliance check
            </li>
          </ul>
        </div>
        
        <div>
          <h3 class="font-medium text-slate-800 mb-2">Processing Options</h3>
          <ul class="text-sm text-slate-600 space-y-1">
            <li class="flex items-center gap-2">
              <Circle class="w-4 h-4 text-slate-300" />
              Generate reports
            </li>
            <li class="flex items-center gap-2">
              <Circle class="w-4 h-4 text-slate-300" />
              Send notifications
            </li>
            <li class="flex items-center gap-2">
              <Circle class="w-4 h-4 text-slate-300" />
              Update analytics
            </li>
          </ul>
        </div>
        
        <div>
          <h3 class="font-medium text-slate-800 mb-2">Notifications</h3>
          <ul class="text-sm text-slate-600 space-y-1">
            <li class="flex items-center gap-2">
              <Circle class="w-4 h-4 text-slate-300" />
              Email to managers
            </li>
            <li class="flex items-center gap-2">
              <Circle class="w-4 h-4 text-slate-300" />
              SMS alerts
            </li>
            <li class="flex items-center gap-2">
              <Circle class="w-4 h-4 text-slate-300" />
              Dashboard notifications
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RefreshCw, CheckCircle, Circle } from 'lucide-vue-next';

// This is a placeholder component for the night audit rerun page
</script>