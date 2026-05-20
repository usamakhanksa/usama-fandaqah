<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">No-Show Rules</h1>
        <p class="text-slate-500 text-sm">Configure rules for automatically marking reservations as no-show</p>
      </div>
      <button class="btn-primary px-4 py-2 rounded-lg text-sm font-medium">
        Save Rules
      </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="text-lg font-bold text-slate-800 mb-4">No-Show Configuration</h2>
      
      <div class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Grace Period After Due Time</label>
          <div class="flex gap-4">
            <div class="flex-1">
              <input 
                type="number" 
                class="input w-full" 
                value="2"
                min="0"
              />
              <p class="text-xs text-slate-500 mt-1">Hours after scheduled check-in time before marking as no-show</p>
            </div>
            <select class="input w-32">
              <option>Hours</option>
              <option>Minutes</option>
            </select>
          </div>
        </div>
        
        <div class="flex items-center pt-2">
          <label class="inline-flex items-center">
            <input type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
            <span class="ml-2 text-sm text-slate-700">Automatically mark reservations as no-show</span>
          </label>
        </div>
        
        <div class="flex items-center">
          <label class="inline-flex items-center">
            <input type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            <span class="ml-2 text-sm text-slate-700">Send notification when reservation marked as no-show</span>
          </label>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold text-slate-800">No-Show Charge Rules</h2>
        <button class="btn-secondary px-3 py-1.5 rounded-lg text-sm font-medium flex items-center gap-1">
          <Plus class="w-4 h-4" />
          Add Rule
        </button>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-100 text-left text-sm text-slate-500">
              <th class="pb-3 font-medium">Rule Name</th>
              <th class="pb-3 font-medium">Applies To</th>
              <th class="pb-3 font-medium">Charge Type</th>
              <th class="pb-3 font-medium">Amount</th>
              <th class="pb-3 font-medium text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr>
              <td class="py-3 font-medium text-slate-800">Standard No-Show Fee</td>
              <td class="py-3 text-sm text-slate-600">All Reservations</td>
              <td class="py-3 text-sm text-slate-600">Percentage of First Night</td>
              <td class="py-3 text-sm text-slate-600">100%</td>
              <td class="py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <MoreHorizontal class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr>
              <td class="py-3 font-medium text-slate-800">VIP No-Show Fee</td>
              <td class="py-3 text-sm text-slate-600">VIP Customers</td>
              <td class="py-3 text-sm text-slate-600">Fixed Amount</td>
              <td class="py-3 text-sm text-slate-600">200.00 SAR</td>
              <td class="py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <MoreHorizontal class="w-4 h-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <h2 class="text-lg font-bold text-slate-800 mb-4">Cancellation After No-Show</h2>
      
      <div class="space-y-4">
        <div class="flex items-center">
          <label class="inline-flex items-center">
            <input type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
            <span class="ml-2 text-sm text-slate-700">Automatically cancel confirmed reservations after no-show</span>
          </label>
        </div>
        
        <div class="flex items-center">
          <label class="inline-flex items-center">
            <input type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            <span class="ml-2 text-sm text-slate-700">Block customer after multiple no-shows</span>
          </label>
        </div>
        
        <div v-if="false" class="pt-4">
          <label class="block text-sm font-medium text-slate-700 mb-2">Threshold for blocking</label>
          <div class="flex gap-2">
            <input 
              type="number" 
              class="input w-24" 
              value="3"
              min="1"
            />
            <span class="self-center text-sm text-slate-600">no-shows within</span>
            <input 
              type="number" 
              class="input w-24" 
              value="30"
              min="1"
            />
            <span class="self-center text-sm text-slate-600">days</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Plus, MoreHorizontal } from 'lucide-vue-next';

// This is a placeholder component for the no-show settings page
</script>