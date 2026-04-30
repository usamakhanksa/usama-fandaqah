<template>
    <Head title="System Administration" />
    <Layout>
        <div class="min-h-screen bg-[#f2f0eb] p-8 text-[#2a273c] font-sans">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-[#8f9793]/30 pb-6 gap-4">
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight text-[#2a273c]">System Administration</h1>
                    <div class="flex space-x-6 mt-4 font-bold overflow-x-auto pb-2 scrollbar-hide">
                        <button @click="switchTab('interfaces')" :class="tab === 'interfaces' ? 'text-[#e95a54] border-b-2 border-[#e95a54]' : 'text-[#8f9793] hover:text-[#2a273c]'" class="pb-1 transition whitespace-nowrap">Interfaces</button>
                        <button @click="switchTab('exports')" :class="tab === 'exports' ? 'text-[#e95a54] border-b-2 border-[#e95a54]' : 'text-[#8f9793] hover:text-[#2a273c]'" class="pb-1 transition whitespace-nowrap">Data Exports</button>
                        <button @click="switchTab('requests')" :class="tab === 'requests' ? 'text-[#e95a54] border-b-2 border-[#e95a54]' : 'text-[#8f9793] hover:text-[#2a273c]'" class="pb-1 transition whitespace-nowrap">Service Requests</button>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4 w-full md:w-auto">
                    <div class="relative flex-1 md:flex-none">
                        <input v-model="form.search" @keyup.enter="applySearch" type="text" placeholder="Search systems..." class="w-full pl-10 pr-4 py-2 border-[#8f9793]/30 rounded-xl text-[#2a273c] focus:ring-[#e95a54] focus:border-[#e95a54] bg-white shadow-sm">
                        <svg class="w-5 h-5 absolute left-3 top-2.5 text-[#8f9793]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <button v-if="tab === 'interfaces'" @click="openModal('interface')" class="bg-[#2a273c] text-[#fbcdab] px-6 py-2.5 rounded-xl shadow-lg hover:bg-[#e95a54] hover:text-[#f2f0eb] transition font-bold whitespace-nowrap">+ Add Interface</button>
                    <button v-if="tab === 'exports'" @click="openModal('export')" class="bg-[#2a273c] text-[#fbcdab] px-6 py-2.5 rounded-xl shadow-lg hover:bg-[#e95a54] hover:text-[#f2f0eb] transition font-bold whitespace-nowrap">+ New Export</button>
                    <button v-if="tab === 'requests'" @click="openModal('request')" class="bg-[#2a273c] text-[#fbcdab] px-6 py-2.5 rounded-xl shadow-lg hover:bg-[#e95a54] hover:text-[#f2f0eb] transition font-bold whitespace-nowrap">+ New Ticket</button>
                </div>
            </div>

            <!-- Interfaces Tab -->
            <div v-if="tab === 'interfaces'" class="space-y-6 animate-in fade-in duration-500">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-[#2a273c] p-6 rounded-2xl shadow-xl border border-[#8f9793]/20 relative overflow-hidden group">
                        <div class="relative z-10">
                            <div class="text-[#8f9793] font-bold text-sm uppercase tracking-widest">Total Interfaces</div>
                            <div class="text-4xl font-black text-[#f2f0eb] mt-1">{{ metrics.total }}</div>
                        </div>
                        <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                            <svg class="w-24 h-24 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-xl border border-[#fbcdab]/30 relative overflow-hidden group">
                        <div class="relative z-10">
                            <div class="text-[#8f9793] font-bold text-sm uppercase tracking-widest">Active Connections</div>
                            <div class="text-4xl font-black text-[#e95a54] mt-1">{{ metrics.active }}</div>
                        </div>
                        <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                            <svg class="w-24 h-24 text-[#e95a54]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <div v-if="interfaces?.data.length === 0" class="col-span-full py-20 text-center bg-white rounded-3xl border-2 border-dashed border-[#fbcdab]/50">
                        <div class="w-16 h-16 bg-[#f2f0eb] rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-[#8f9793]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-[#2a273c]">No Interfaces Configured</h3>
                        <p class="text-[#8f9793] max-w-xs mx-auto mt-2">Connect your PMS to government portals, payment gateways, and door locks.</p>
                    </div>
                    <div v-for="intf in interfaces?.data" :key="intf.id" class="bg-white rounded-2xl p-6 shadow-sm border border-[#fbcdab]/20 hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 rounded-xl bg-[#f2f0eb] group-hover:bg-[#fbcdab]/20 transition-colors">
                                    <svg class="w-6 h-6 text-[#2a273c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                                </div>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border" 
                                      :class="{'bg-green-50 text-green-600 border-green-200': intf.status === 'connected', 'bg-red-50 text-red-600 border-red-200': intf.status === 'disconnected', 'bg-yellow-50 text-yellow-600 border-yellow-200': intf.status === 'degraded', 'bg-gray-50 text-gray-600 border-gray-200': intf.status === 'maintenance'}">
                                    {{ intf.status }}
                                </span>
                            </div>
                            <h3 class="text-xl font-black text-[#2a273c] mb-1">{{ intf.name }}</h3>
                            <p class="text-[#8f9793] font-bold text-xs uppercase tracking-tighter mb-4">{{ intf.provider }} • {{ intf.type.replace('_', ' ') }}</p>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-xs border-b border-[#f2f0eb] pb-2">
                                    <span class="text-[#8f9793] font-medium">Endpoint</span>
                                    <span class="text-[#2a273c] truncate max-w-[150px] font-mono">{{ intf.api_endpoint || 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between text-xs border-b border-[#f2f0eb] pb-2">
                                    <span class="text-[#8f9793] font-medium">Last Handshake</span>
                                    <span class="text-[#2a273c]">{{ intf.last_sync_at ? new Date(intf.last_sync_at).toLocaleTimeString() : 'Pending' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 flex space-x-2">
                            <button @click="openModal('interface', intf)" class="flex-1 bg-[#2a273c] text-white py-2 rounded-xl text-sm font-bold shadow-md hover:bg-[#e95a54] transition-all">Configure</button>
                            <button @click="deleteRecord('interfaces', intf.id)" class="px-3 bg-red-50 text-[#e95a54] rounded-xl hover:bg-[#e95a54] hover:text-white transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-center py-4 border-t border-[#8f9793]/10">
                    <nav class="flex gap-2">
                        <Component v-for="(link, k) in interfaces?.links" :is="link.url ? 'a' : 'span'" :key="k" @click.prevent="link.url ? router.visit(link.url) : null" :href="link.url" v-html="link.label" class="px-4 py-1.5 rounded-lg font-bold text-sm transition-all" :class="link.active ? 'bg-[#2a273c] text-[#fbcdab]' : 'text-[#8f9793] hover:bg-[#fbcdab]/20'"/>
                    </nav>
                </div>
            </div>

            <!-- Exports Tab -->
            <div v-if="tab === 'exports'" class="animate-in slide-in-from-right duration-500">
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-[#fbcdab]/30">
                    <div class="p-6 border-b border-[#f2f0eb] flex justify-between items-center">
                        <h2 class="text-xl font-black text-[#2a273c]">Archive & Extracts</h2>
                        <div class="flex gap-4">
                            <div class="text-center px-4 border-r border-[#f2f0eb]">
                                <div class="text-[10px] uppercase font-black text-[#8f9793]">Lifetime</div>
                                <div class="font-black text-[#2a273c]">{{ metrics.total }}</div>
                            </div>
                            <div class="text-center px-4">
                                <div class="text-[10px] uppercase font-black text-[#8f9793]">Past 24h</div>
                                <div class="font-black text-[#e95a54]">{{ metrics.recent }}</div>
                            </div>
                        </div>
                    </div>
                    <table class="w-full text-left border-collapse">
                        <thead><tr class="bg-[#f2f0eb]/50 text-[#8f9793] uppercase text-[10px] font-black tracking-widest leading-none"><th class="p-5">Report Manifest</th><th class="p-5">Format</th><th class="p-5">Status</th><th class="p-5">Authority</th><th class="p-5 text-right">Vault</th></tr></thead>
                        <tbody class="divide-y divide-[#f2f0eb]">
                            <tr v-if="exports?.data.length === 0"><td colspan="5" class="p-20 text-center text-[#8f9793] italic">The digital archive is currently empty.</td></tr>
                            <tr v-for="exp in exports?.data" :key="exp.id" class="hover:bg-[#f2f0eb]/20 transition-colors group">
                                <td class="p-5">
                                    <div class="font-black text-[#2a273c] text-lg">{{ exp.name }}</div>
                                    <div class="text-[10px] font-bold text-[#8f9793] opacity-60">Expires: {{ exp.expires_at ? new Date(exp.expires_at).toLocaleDateString() : 'Never' }}</div>
                                </td>
                                <td class="p-5">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-[#2a273c] text-[#fbcdab] px-2 py-1 rounded text-[10px] font-black uppercase tracking-tighter">{{ exp.format }}</span>
                                        <span class="text-[#8f9793] text-xs font-bold leading-none">{{ (exp.file_size_kb / 1024).toFixed(1) }} MB</span>
                                    </div>
                                </td>
                                <td class="p-5">
                                    <span class="flex items-center gap-2 text-xs font-black uppercase tracking-tighter" :class="exp.status === 'completed' ? 'text-green-600' : 'text-[#fbcdab]'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="exp.status === 'completed' ? 'bg-green-600' : 'bg-[#fbcdab] animate-pulse'"></span>
                                        {{ exp.status }}
                                    </span>
                                </td>
                                <td class="p-5">
                                    <div class="text-xs font-black text-[#2a273c]">{{ exp.requested_by }}</div>
                                    <div class="text-[10px] font-bold text-[#8f9793]">{{ new Date(exp.created_at).toLocaleString() }}</div>
                                </td>
                                <td class="p-5 text-right">
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity flex justify-end gap-2">
                                        <a v-if="exp.status === 'completed'" href="#" class="p-2 bg-[#2a273c] text-[#fbcdab] rounded-xl hover:bg-[#e95a54] transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        </a>
                                        <button @click="deleteRecord('exports', exp.id)" class="p-2 bg-red-50 text-[#e95a54] rounded-xl hover:bg-red-500 hover:text-white transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Service Requests Tab -->
            <div v-if="tab === 'requests'" class="animate-in fade-in duration-700 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-[#fbcdab]/30 flex flex-col items-center justify-center">
                        <div class="text-3xl font-black text-[#2a273c]">{{ metrics.open }}</div>
                        <div class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest mt-1">Pending Resolution</div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-[#e95a54] flex flex-col items-center justify-center">
                        <div class="text-3xl font-black text-[#e95a54]">{{ metrics.critical }}</div>
                        <div class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest mt-1">High Intensity</div>
                    </div>
                    <div class="md:col-span-2 bg-[#2a273c] p-6 rounded-2xl shadow-xl flex items-center gap-6">
                        <div class="p-4 bg-white/10 rounded-2xl">
                            <svg class="w-10 h-10 text-[#fbcdab]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h4 class="text-white font-black text-lg leading-tight">Need Urgent Assistance?</h4>
                            <p class="text-white/60 text-sm">Our premium support desk is available 24/7 for system blockers.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div v-if="requests?.data.length === 0" class="py-20 text-center bg-white rounded-3xl border border-[#fbcdab]/20">
                        <p class="text-[#8f9793] font-bold">Your support queue is clear.</p>
                    </div>
                    <div v-for="req in requests?.data" :key="req.id" 
                         class="bg-white rounded-2xl p-6 shadow-sm border hover:shadow-md transition-all duration-300 flex flex-col md:flex-row md:items-center justify-between gap-6"
                         :class="req.priority === 'critical' ? 'border-[#e95a54]/30' : 'border-[#fbcdab]/20'">
                        <div class="flex items-start gap-4 flex-1">
                            <div class="px-3 py-4 rounded-xl text-center min-w-[80px]" :class="req.priority === 'critical' ? 'bg-[#e95a54] text-white' : 'bg-[#f2f0eb] text-[#2a273c]'">
                                <div class="text-[10px] font-black uppercase opacity-60 leading-none mb-1">Priority</div>
                                <div class="text-xs font-black uppercase tracking-tight">{{ req.priority }}</div>
                            </div>
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="text-[10px] font-black text-[#e95a54] tracking-widest">{{ req.ticket_number }}</span>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase bg-[#2a273c] text-[#fbcdab]">{{ req.category }}</span>
                                </div>
                                <h4 class="text-xl font-extrabold text-[#2a273c] leading-tight mb-1">{{ req.title }}</h4>
                                <p class="text-sm text-[#8f9793] font-medium line-clamp-2 md:max-w-2xl">{{ req.description }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between md:justify-end gap-6 border-t md:border-t-0 pt-4 md:pt-0">
                            <div class="text-right">
                                <div class="text-[10px] font-black text-[#8f9793] uppercase mb-1">Status</div>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border transition-colors"
                                      :class="req.status === 'open' ? 'bg-[#fbcdab] text-[#2a273c] border-[#2a273c]/10' : 'bg-[#f2f0eb] text-[#8f9793] border-transparent'">
                                    {{ req.status.replace('_', ' ') }}
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <button @click="openModal('request', req)" class="p-2.5 bg-[#2a273c] text-white rounded-xl hover:bg-[#e95a54] transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                                <button @click="deleteRecord('requests', req.id)" class="p-2.5 bg-red-50 text-[#e95a54] rounded-xl hover:bg-red-500 hover:text-white transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modals -->
            <transition name="modal">
                <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-[#2a273c]/90 backdrop-blur-md" @click="isModalOpen = false"></div>
                    <div class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-xl overflow-hidden animate-in zoom-in-95 duration-300">
                        <div class="p-10 bg-[#2a273c] text-[#f2f0eb] relative overflow-hidden">
                            <div class="relative z-10">
                                <h2 class="text-3xl font-black tracking-tight" v-if="modalType === 'interface'">{{ editMode ? 'Reconfigure Link' : 'Secure Integration' }}</h2>
                                <h2 class="text-3xl font-black tracking-tight" v-if="modalType === 'export'">Data Harvesting</h2>
                                <h2 class="text-3xl font-black tracking-tight" v-if="modalType === 'request'">{{ editMode ? 'Resolve Inquiry' : 'Support Engagement' }}</h2>
                                <p class="text-[#8f9793] font-bold text-sm mt-1 uppercase tracking-widest">Fandaqah System Management</p>
                            </div>
                            <div class="absolute -right-10 -top-10 opacity-10">
                                <svg class="w-64 h-64 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" /></svg>
                            </div>
                        </div>

                        <div class="p-10">
                            <!-- Interface Form -->
                            <form v-if="modalType === 'interface'" @submit.prevent="submitForm('interfaces', intfForm)" class="space-y-5">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest ml-1">System Name</label>
                                        <input v-model="intfForm.name" type="text" class="w-full bg-[#f2f0eb] border-0 rounded-2xl focus:ring-2 focus:ring-[#e95a54] p-4 text-[#2a273c] font-bold" placeholder="e.g. ZATCA v2" required>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest ml-1">Provider</label>
                                        <input v-model="intfForm.provider" type="text" class="w-full bg-[#f2f0eb] border-0 rounded-2xl focus:ring-2 focus:ring-[#e95a54] p-4 text-[#2a273c] font-bold" placeholder="e.g. Gov Portals" required>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest ml-1">Service API URL</label>
                                    <input v-model="intfForm.api_endpoint" type="url" class="w-full bg-[#f2f0eb] border-0 rounded-2xl focus:ring-2 focus:ring-[#e95a54] p-4 text-[#2a273c] font-mono text-sm" placeholder="https://api.example.com">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest ml-1">Logic Type</label>
                                        <select v-model="intfForm.type" class="w-full bg-[#f2f0eb] border-0 rounded-2xl focus:ring-2 focus:ring-[#e95a54] p-4 text-[#2a273c] font-bold">
                                            <option value="government">Governmental</option>
                                            <option value="payment_gateway">Payment Logic</option>
                                            <option value="door_lock">Access Priority</option>
                                            <option value="erp">Enterprise Core</option>
                                            <option value="other">Ancillary</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest ml-1">Current State</label>
                                        <select v-model="intfForm.status" class="w-full bg-[#f2f0eb] border-0 rounded-2xl focus:ring-2 focus:ring-[#e95a54] p-4 text-[#2a273c] font-bold">
                                            <option value="connected">Connected</option>
                                            <option value="disconnected">Offline</option>
                                            <option value="degraded">Performance Lag</option>
                                            <option value="maintenance">Sanitized Dev</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex gap-4 pt-6">
                                    <button type="button" @click="isModalOpen = false" class="flex-1 py-4 bg-[#f2f0eb] text-[#2a273c] rounded-2xl font-black text-sm hover:bg-[#fbcdab]/50 transition-colors uppercase tracking-widest">Abandon</button>
                                    <button type="submit" class="flex-1 py-4 bg-[#2a273c] text-[#fbcdab] rounded-2xl font-black text-sm shadow-xl shadow-[#2a273c]/20 hover:bg-[#e95a54] hover:text-white transition-all uppercase tracking-widest" :disabled="intfForm.processing">Synchronize</button>
                                </div>
                            </form>

                            <!-- Export Form -->
                            <form v-if="modalType === 'export'" @submit.prevent="submitForm('exports', expForm)" class="space-y-6">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest ml-1">Dataset Reference</label>
                                    <input v-model="expForm.name" type="text" class="w-full bg-[#f2f0eb] border-0 rounded-2xl focus:ring-2 focus:ring-[#e95a54] p-4 text-[#2a273c] font-bold" placeholder="e.g. Fiscal Q3 Archive" required>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest ml-1">Extraction Format</label>
                                    <select v-model="expForm.format" class="w-full bg-[#f2f0eb] border-0 rounded-2xl focus:ring-2 focus:ring-[#e95a54] p-4 text-[#2a273c] font-bold">
                                        <option value="csv">CSV (Universal Ledger)</option>
                                        <option value="pdf">PDF (Printable Proof)</option>
                                        <option value="xml">XML (Structural Hash)</option>
                                        <option value="xlsx">Excel (Complex Formulas)</option>
                                    </select>
                                </div>
                                <div class="p-6 bg-yellow-50 rounded-2xl border border-yellow-100">
                                    <div class="flex items-center gap-3 text-yellow-800 font-bold text-sm mb-1 uppercase tracking-widest">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        Security Protocol
                                    </div>
                                    <p class="text-yellow-700/80 text-xs leading-relaxed">Large datasets may consume significant cycles. Generated extracts are retained for 7 solar days before automated erasure.</p>
                                </div>
                                <div class="flex gap-4 pt-2">
                                    <button type="button" @click="isModalOpen = false" class="flex-1 py-4 bg-[#f2f0eb] text-[#2a273c] rounded-2xl font-black text-sm uppercase tracking-widest">Discard</button>
                                    <button type="submit" class="flex-1 py-4 bg-[#e95a54] text-white rounded-2xl font-black text-sm shadow-xl shadow-[#e95a54]/20 hover:scale-[1.02] transition-all uppercase tracking-widest" :disabled="expForm.processing">Initiate Harvest</button>
                                </div>
                            </form>

                            <!-- Ticket Form -->
                            <form v-if="modalType === 'request'" @submit.prevent="submitForm('requests', reqForm)" class="space-y-5">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest ml-1">Subject Matter</label>
                                    <input v-model="reqForm.title" type="text" class="w-full bg-[#f2f0eb] border-0 rounded-2xl focus:ring-2 focus:ring-[#e95a54] p-4 text-[#2a273c] font-bold" placeholder="Summarize the anomaly..." required>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest ml-1">Classification</label>
                                        <select v-model="reqForm.category" class="w-full bg-[#f2f0eb] border-0 rounded-2xl focus:ring-2 focus:ring-[#e95a54] p-4 text-[#2a273c] font-bold">
                                            <option value="hardware">Endpoint Hardware</option>
                                            <option value="software">Core Software / Logic</option>
                                            <option value="network">Backbone Network</option>
                                            <option value="account">Privilege Escalation</option>
                                            <option value="other">Miscellaneous Inquiry</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest ml-1">Response Urgency</label>
                                        <select v-model="reqForm.priority" class="w-full bg-[#f2f0eb] border-0 rounded-2xl focus:ring-2 focus:ring-[#e95a54] p-4 text-[#2a273c] font-bold">
                                            <option value="low">Standard</option>
                                            <option value="medium">Elevated</option>
                                            <option value="high">Urgent</option>
                                            <option value="critical">System Blocker (L1)</option>
                                        </select>
                                    </div>
                                </div>
                                <div v-if="editMode" class="space-y-1">
                                    <label class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest ml-1">Ticket Lifecycle</label>
                                    <select v-model="reqForm.status" class="w-full bg-[#f2f0eb] border-0 rounded-2xl focus:ring-2 focus:ring-[#e95a54] p-4 text-[#2a273c] font-bold">
                                        <option value="open">Opened</option>
                                        <option value="in_progress">Under Investigation</option>
                                        <option value="waiting_on_vendor">External Dependent</option>
                                        <option value="resolved">Sanitized / Fixed</option>
                                        <option value="closed">Archived / Closed</option>
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest ml-1">Evidence / Description</label>
                                    <textarea v-model="reqForm.description" rows="3" class="w-full bg-[#f2f0eb] border-0 rounded-2xl focus:ring-2 focus:ring-[#e95a54] p-4 text-[#2a273c] font-medium leading-relaxed" placeholder="Detailed evidence of the issue..." required></textarea>
                                </div>
                                <div class="flex gap-4 pt-4">
                                    <button type="button" @click="isModalOpen = false" class="flex-1 py-4 bg-[#f2f0eb] text-[#2a273c] rounded-2xl font-black text-sm uppercase tracking-widest">Abandon</button>
                                    <button type="submit" class="flex-1 py-4 bg-[#2a273c] text-[#fbcdab] rounded-2xl font-black text-sm shadow-xl hover:bg-[#e95a54] hover:text-white transition-all uppercase tracking-widest" :disabled="reqForm.processing">Transmit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </Layout>
</template>

<script setup>
import Layout from '@/Layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';

const props = defineProps({ tab: String, filters: Object, interfaces: Object, exports: Object, requests: Object, metrics: Object });

const form = reactive({ search: props.filters.search || '' });
const switchTab = (t) => { 
    form.search = ''; 
    router.get(route('misc.index'), { tab: t }, { preserveState: true, replace: true }); 
};
const applySearch = () => router.get(route('misc.index'), { tab: props.tab, search: form.search }, { preserveState: true });

// Modals Setup
const isModalOpen = ref(false);
const modalType = ref('');
const editMode = ref(false);
let editingId = null;

const intfForm = useForm({ name: '', provider: '', type: 'government', status: 'connected', api_endpoint: '' });
const expForm = useForm({ name: '', format: 'csv' });
const reqForm = useForm({ title: '', description: '', category: 'software', priority: 'medium', status: 'open' });

const openModal = (type, data = null) => {
    modalType.value = type;
    editMode.value = !!data;
    if(type === 'interface') { 
        if(data){ 
            editingId = data.id; 
            intfForm.name = data.name;
            intfForm.provider = data.provider;
            intfForm.type = data.type;
            intfForm.status = data.status;
            intfForm.api_endpoint = data.api_endpoint;
        } else { intfForm.reset(); } 
    }
    if(type === 'export') expForm.reset();
    if(type === 'request') { 
        if(data){ 
            editingId = data.id; 
            reqForm.title = data.title;
            reqForm.description = data.description;
            reqForm.category = data.category;
            reqForm.priority = data.priority;
            reqForm.status = data.status;
        } else { reqForm.reset(); } 
    }
    isModalOpen.value = true;
};

const submitForm = (endpoint, formObj) => {
    if(editMode.value) {
        formObj.put(`/misc/${endpoint}/${editingId}`, { 
            onSuccess: () => isModalOpen.value = false,
            preserveScroll: true
        });
    } else {
        formObj.post(`/misc/${endpoint}`, { 
            onSuccess: () => isModalOpen.value = false,
            preserveScroll: true
        });
    }
};

const deleteRecord = (endpoint, id) => {
    if(confirm('Are you sure you want to terminate this record permanently?')) {
        router.delete(`/misc/${endpoint}/${id}`, { preserveScroll: true });
    }
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }

.animate-in { animation: enter 0.5s ease-out; }
@keyframes enter {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
