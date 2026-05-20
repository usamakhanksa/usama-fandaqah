<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Roles & Permissions</h1>
        <p class="text-slate-500 text-sm">Manage user roles and their associated permissions</p>
      </div>
      <button class="btn-primary px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
        <Plus class="w-4 h-4" />
        Add New Role
      </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-100 text-left text-sm text-slate-500">
              <th class="pb-3 font-medium">Role</th>
              <th class="pb-3 font-medium">Description</th>
              <th class="pb-3 font-medium">Users</th>
              <th class="pb-3 font-medium">Permissions</th>
              <th class="pb-3 font-medium text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr class="py-2">
              <td class="py-4 font-medium text-slate-800">Super Admin</td>
              <td class="py-4 text-sm text-slate-600">Full system access</td>
              <td class="py-4 text-sm text-slate-600">5</td>
              <td class="py-4 text-sm text-slate-600">All permissions</td>
              <td class="py-4 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <MoreHorizontal class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="py-2">
              <td class="py-4 font-medium text-slate-800">Hotel Manager</td>
              <td class="py-4 text-sm text-slate-600">Operational management</td>
              <td class="py-4 text-sm text-slate-600">3</td>
              <td class="py-4 text-sm text-slate-600">Most permissions</td>
              <td class="py-4 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <MoreHorizontal class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr class="py-2">
              <td class="py-4 font-medium text-slate-800">Front Desk Agent</td>
              <td class="py-4 text-sm text-slate-600">Check-in/out operations</td>
              <td class="py-4 text-sm text-slate-600">12</td>
              <td class="py-4 text-sm text-slate-600">Limited permissions</td>
              <td class="py-4 text-right">
                <button class="text-slate-400 hover:text-slate-600">
                  <MoreHorizontal class="w-4 h-4" />
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
import { Plus, MoreHorizontal } from 'lucide-vue-next';

// This is a placeholder component for the roles and permissions page
</script>