<template>
  <details class="relative">
    <summary class="list-none cursor-pointer flex items-center gap-2">
      <img :src="'/assets/avatars/admin.svg'" class="w-8 h-8 rounded-full">
      <div class="text-left leading-tight">
        <p class="text-xs font-semibold">Aya Ahmed Abdullah</p>
        <p class="text-[11px] text-slate-400">Super Admin</p>
      </div>
    </summary>
    <div class="absolute right-0 bg-white border rounded-xl shadow p-2 w-36 z-20">
      <RouterLink to="/profile" class="block px-2 py-1 hover:bg-slate-100 rounded">Profile</RouterLink>
      <RouterLink to="/settings" class="block px-2 py-1 hover:bg-slate-100 rounded">Settings</RouterLink>
      <button class="w-full text-left px-2 py-1 hover:bg-slate-100 rounded">Logout</button>
    </div>
  </details>
</template>
