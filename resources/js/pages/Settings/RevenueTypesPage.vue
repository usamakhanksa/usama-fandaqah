<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Service Revenue Types</h1>
        <p class="text-slate-500 text-sm">Manage different types of service revenue categories for reporting</p>
      </div>
      <button class="btn-primary px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
        <Plus class="w-4 h-4" />
        Add Revenue Type
      </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-100 text-left text-sm text-slate-500">
              <th class="pb-3 font-medium">Type Name</th>
              <th class="pb-3 font-medium">Category</th>
              <th class="pb-3 font-medium">Tax Rate</th>
              <th class="pb-3 font-medium">Active</th>
              <th class="pb-3 font-medium text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr>
              <td class="py-3 font-medium text-slate-800">Room Revenue</td>
              <td class="py-3 text-sm text-slate-600">Accommodation</td>
              <td class="py-3 text-sm text-slate-600">15% VAT</td>
              <td class="py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">Active</span>
              </td>
              <td class="py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <MoreHorizontal class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr>
              <td class="py-3 font-medium text-slate-800">Food & Beverage</td>
              <td class="py-3 text-sm text-slate-600">F&B</td>
              <td class="py-3 text-sm text-slate-600">15% VAT</td>
              <td class="py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">Active</span>
              </td>
              <td class="py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <MoreHorizontal class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr>
              <td class="py-3 font-medium text-slate-800">Spa Services</td>
              <td class="py-3 text-sm text-slate-600">Wellness</td>
              <td class="py-3 text-sm text-slate-600">15% VAT + 5% Tourism</td>
              <td class="py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-800">Active</span>
              </td>
              <td class="py-3 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <MoreHorizontal class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr>
              <td class="py-3 font-medium text-slate-800">Laundry Service</td>
              <td class="py-3 text-sm text-slate-600">Ancillary</td>
              <td class="py-3 text-sm text-slate-600">15% VAT</td>
              <td class="py-3">
                <span class="px-2 py-1 rounded-full text-xs bg-rose-100 text-rose-800">Inactive</span>
              </td>
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
      <h2 class="text-lg font-bold text-slate-800 mb-4">Revenue Classification</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 class="font-medium text-slate-800 mb-3">Standard Categories</h3>
          <div class="space-y-2">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <span class="text-sm text-slate-700">Accommodation</span>
              <span class="text-xs px-2 py-1 rounded bg-blue-100 text-blue-800">Primary</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <span class="text-sm text-slate-700">Food & Beverage</span>
              <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-800">Secondary</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <span class="text-sm text-slate-700">Wellness</span>
              <span class="text-xs px-2 py-1 rounded bg-purple-100 text-purple-800">Ancillary</span>
            </div>
          </div>
        </div>
        
        <div>
          <h3 class="font-medium text-slate-800 mb-3">Tax Configuration</h3>
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <span class="text-sm text-slate-700">Standard VAT Rate</span>
              <span class="text-sm font-medium">15%</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <span class="text-sm text-slate-700">Tourism Tax</span>
              <span class="text-sm font-medium">5%</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <span class="text-sm text-slate-700">Municipal Fees</span>
              <span class="text-sm font-medium">5%</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Plus, MoreHorizontal } from 'lucide-vue-next';

// This is a placeholder component for the revenue types settings page
</script>