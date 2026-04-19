<template>
  <main class="p-4 bg-slate-100 min-h-[calc(100vh-56px)]">
    <div class="flex items-center gap-2 mb-4"><span>🧩</span><h1 class="font-bold text-xl">User Grouping</h1><span class="text-slate-400 text-sm">Home > User Grouping</span></div>
    <div class="grid lg:grid-cols-[320px_1fr] gap-4">
      <UserRolesList :roles="roles" :selected-role-id="selectedRole?.id" @select="selectRole" @create="openCreate" @menu="openMenu"/>
      <PermissionMatrixTable :permissions="permissions" @update="updatePermission"/>
    </div>

    <RoleFormModal :show="showRoleModal" :model-value="editingRole" @save="saveRole" @close="showRoleModal=false"/>
    <AssignUsersToRoleModal :show="showAssignModal" :users="users" :initial="selectedUserIds" @save="assignUsers" @close="showAssignModal=false"/>

    <div v-if="contextRole" class="fixed right-6 top-36 bg-white border rounded-xl shadow p-2 z-20">
      <button class="block w-full text-left px-3 py-2 hover:bg-slate-100 rounded" @click="editRole(contextRole)">Edit Role</button>
      <button class="block w-full text-left px-3 py-2 hover:bg-slate-100 rounded" @click="duplicateRole(contextRole)">Duplicate Role</button>
      <button class="block w-full text-left px-3 py-2 hover:bg-slate-100 rounded" @click="showAssign(contextRole)">Assign Users</button>
      <button class="block w-full text-left px-3 py-2 hover:bg-slate-100 rounded text-red-600" @click="deleteRole(contextRole)">Delete Role</button>
    </div>

    <FooterBar class="mt-6"/>
  </main>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import api from '../services/api';
import UserRolesList from '../components/UserRolesList.vue';
import PermissionMatrixTable from '../components/PermissionMatrixTable.vue';
import RoleFormModal from '../components/RoleFormModal.vue';
import AssignUsersToRoleModal from '../components/AssignUsersToRoleModal.vue';
import FooterBar from '../components/FooterBar.vue';

const roles = ref([]);
const permissions = ref([]);
const users = ref([]);
const selectedRole = ref(null);
const showRoleModal = ref(false);
const editingRole = ref(null);
const contextRole = ref(null);
const showAssignModal = ref(false);
const selectedUserIds = ref([]);

async function loadRoles() {
  const { data } = await api.get('/user-groups/roles');
  roles.value = data.data;
  if (!selectedRole.value && roles.value.length) selectRole(roles.value[0]);
}
async function selectRole(role) {
  selectedRole.value = role;
  contextRole.value = null;
  const { data } = await api.get(`/user-groups/roles/${role.id}/permissions`);
  permissions.value = data.data;
}
function openCreate() { editingRole.value = null; showRoleModal.value = true; }
function editRole(role) { editingRole.value = role; showRoleModal.value = true; contextRole.value = null; }
function openMenu(role) { contextRole.value = role; }
async function saveRole(payload) {
  if (payload?.id) await api.put(`/user-groups/roles/${payload.id}`, payload);
  else await api.post('/user-groups/roles', payload);
  showRoleModal.value = false;
  await loadRoles();
}
async function duplicateRole(role) { await api.post(`/user-groups/roles/${role.id}/duplicate`); contextRole.value = null; await loadRoles(); }
async function deleteRole(role) { if (!confirm('Delete role?')) return; await api.delete(`/user-groups/roles/${role.id}`); contextRole.value = null; selectedRole.value = null; await loadRoles(); }
async function updatePermission(item) {
  if (!selectedRole.value) return;
  await api.put(`/user-groups/roles/${selectedRole.value.id}/permissions/${item.id}`, item);
}
async function showAssign(role) {
  selectedRole.value = role;
  const [usersRes, rolesRes] = await Promise.all([api.get('/user-groups/users'), api.get('/user-groups/roles')]);
  users.value = usersRes.data.data;
  const fresh = rolesRes.data.data.find((r) => r.id === role.id);
  const ids = (fresh?.avatars || []).length ? users.value.filter((u) => fresh.avatars.includes(u.avatar)).map((u) => u.id) : [];
  selectedUserIds.value = ids;
  showAssignModal.value = true;
  contextRole.value = null;
}
async function assignUsers(userIds) {
  await api.post(`/user-groups/roles/${selectedRole.value.id}/assign-users`, { user_ids: userIds });
  showAssignModal.value = false;
  await loadRoles();
}

onMounted(loadRoles);
</script>
