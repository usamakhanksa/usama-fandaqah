<template>
    <div class="min-h-screen bg-[#f2f0eb] p-4 md:p-8 text-[#2a273c] font-sans selection:bg-[#e95a54] selection:text-white">
        <!-- Header Section -->
        <header class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-end mb-10 pb-8 border-b border-[#8f9793]/20 space-y-6 md:space-y-0">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <span class="w-10 h-1 bg-[#e95a54] rounded-full"></span>
                    <span class="text-[#8f9793] font-bold tracking-[0.2em] uppercase text-xs">Fandaqah Financial Hub</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-black tracking-tight text-[#2a273c]">Financial Controller</h1>
                
                <!-- Navigation Tabs -->
                <nav class="flex space-x-6 mt-8 font-bold overflow-x-auto no-scrollbar">
                    <button v-for="t in ['cashiering', 'ar', 'comps', 'eod']" :key="t"
                        @click="switchTab(t)"
                        :class="tab === t ? 'text-[#e95a54] border-b-4 border-[#e95a54]' : 'text-[#8f9793] hover:text-[#2a273c] border-b-4 border-transparent'"
                        class="pb-3 transition-all duration-300 whitespace-nowrap capitalize text-sm md:text-base tracking-wide">
                        {{ t === 'ar' ? 'Accounts Receivable' : (t === 'eod' ? 'End of Day' : t) }}
                    </button>
                </nav>
            </div>
            
            <div class="flex space-x-4">
                <button v-if="tab === 'cashiering'" @click="openModal('cashiering')" class="group relative bg-[#2a273c] text-[#fbcdab] px-8 py-3 rounded-2xl shadow-2xl hover:bg-[#e95a54] hover:text-[#f2f0eb] transition-all duration-500 font-black flex items-center overflow-hidden">
                    <span class="relative z-10 text-sm tracking-widest">+ POST TRANSACTION</span>
                </button>
                <button v-if="tab === 'ar'" @click="openModal('ar')" class="bg-[#2a273c] text-[#fbcdab] px-8 py-3 rounded-2xl shadow-2xl hover:bg-[#e95a54] hover:text-[#f2f0eb] transition-all duration-500 font-black text-sm tracking-widest">
                    + NEW CORPORATE ACCOUNT
                </button>
                <button v-if="tab === 'comps'" @click="openModal('comp')" class="bg-[#2a273c] text-[#fbcdab] px-8 py-3 rounded-2xl shadow-2xl hover:bg-[#e95a54] hover:text-[#f2f0eb] transition-all duration-500 font-black text-sm tracking-widest">
                    + POST COMPLIMENTARY
                </button>
            </div>
        </header>

        <main class="max-w-7xl mx-auto animate-in fade-in slide-in-from-bottom-4 duration-700">
            <!-- Metrics Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div class="bg-white/70 backdrop-blur-md p-6 rounded-[2rem] border border-white shadow-xl hover:shadow-2xl transition-all duration-500 group">
                    <div class="flex justify-between items-start mb-3">
                        <div class="p-3 bg-[#f2f0eb] rounded-2xl group-hover:bg-[#fbcdab] transition-colors duration-500">
                            <svg class="w-6 h-6 text-[#2a273c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-green-500 bg-green-50 px-2 py-1 rounded-full">+{{ ((metrics.today_revenue / (metrics.yesterday_revenue || 1)) * 100).toFixed(0) }}%</span>
                    </div>
                    <div class="text-[#8f9793] font-bold text-[10px] uppercase tracking-widest mb-1">Today's Revenue</div>
                    <div class="text-2xl font-black text-[#2a273c]">SAR {{ Number(metrics.today_revenue).toLocaleString() }}</div>
                </div>
                
                <div class="bg-[#2a273c] p-6 rounded-[2rem] border border-[#8f9793]/20 shadow-2xl group relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-[#e95a54] rounded-full opacity-10 group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="text-[#fbcdab] font-bold text-[10px] uppercase tracking-widest mb-1 relative z-10">Accounts Receivable</div>
                    <div class="text-2xl font-black text-[#f2f0eb] relative z-10">SAR {{ Number(metrics.ar_balance).toLocaleString() }}</div>
                    <div class="mt-4 flex items-center space-x-2 relative z-10">
                        <div class="w-full bg-[#8f9793]/30 h-1 rounded-full overflow-hidden">
                            <div class="bg-[#e95a54] h-full" style="width: 65%"></div>
                        </div>
                        <span class="text-[10px] font-bold text-[#8f9793]">65%</span>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-md p-6 rounded-[2rem] border border-white shadow-xl">
                    <div class="text-[#8f9793] font-bold text-[10px] uppercase tracking-widest mb-1">Complimentary Postings</div>
                    <div class="text-2xl font-black text-[#e95a54]">SAR {{ Number(metrics.today_comps).toLocaleString() }}</div>
                    <div class="mt-3 text-[10px] font-bold text-[#8f9793] bg-[#f2f0eb] px-3 py-1 rounded-full inline-block">Managerial Approval Active</div>
                </div>

                <div class="bg-gradient-to-br from-[#fbcdab] to-[#f2f0eb] p-6 rounded-[2rem] border border-white shadow-xl border-b-4 border-b-[#e95a54]">
                    <div class="text-[#2a273c] font-bold text-[10px] uppercase tracking-widest mb-1">Last Night Audit</div>
                    <div class="text-2xl font-black text-[#2a273c]">{{ latest_eod?.audit_date ? new Date(latest_eod.audit_date).toLocaleDateString() : 'N/A' }}</div>
                    <div class="mt-3 flex items-center space-x-1">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-[10px] font-black text-[#2a273c]">System Balanced</span>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <!-- Cashiering Table -->
            <section v-if="tab === 'cashiering'" class="space-y-4">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-xl font-black text-[#2a273c] flex items-center space-x-2">
                        <span>Daily Postings Log</span>
                        <span class="text-xs bg-[#2a273c] text-white px-2 py-0.5 rounded-full font-bold">LIVE</span>
                    </h2>
                    <div class="relative group">
                        <input type="text" placeholder="Search receipts..." class="bg-white/50 border-none rounded-2xl px-10 py-2 text-sm focus:ring-2 focus:ring-[#e95a54] transition-all duration-300 w-64 shadow-sm">
                        <svg class="w-4 h-4 text-[#8f9793] absolute left-4 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
                
                <div class="bg-white rounded-[2rem] shadow-2xl overflow-hidden border border-[#8f9793]/10 backdrop-blur-xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-[#2a273c] text-[#fbcdab] text-[10px] uppercase tracking-[0.2em]">
                                    <th class="p-6 font-black">Transaction ID</th>
                                    <th class="p-6 font-black">Entity Details</th>
                                    <th class="p-6 font-black text-center">Type</th>
                                    <th class="p-6 font-black">Payment Method</th>
                                    <th class="p-6 font-black text-right">Debit/Credit (SAR)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f2f0eb]">
                                <tr v-if="!transactions?.data.length">
                                    <td colspan="5" class="p-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-16 h-16 bg-[#f2f0eb] rounded-full flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8 text-[#8f9793]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </div>
                                            <span class="text-[#8f9793] font-bold">No transactions found for this period.</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="tx in transactions?.data" :key="tx.id" class="hover:bg-[#f2f0eb]/30 transition-colors duration-300 group">
                                    <td class="p-6">
                                        <div class="font-black text-[#2a273c] group-hover:text-[#e95a54] transition-colors">{{ tx.receipt_number }}</div>
                                        <div class="text-[10px] text-[#8f9793] font-bold">{{ new Date(tx.transaction_date).toLocaleDateString() }}</div>
                                    </td>
                                    <td class="p-6">
                                        <div class="font-black text-[#2a273c] text-sm">{{ tx.booking_reference || tx.ar_account?.company_name }}</div>
                                        <div class="text-[11px] text-[#8f9793] font-medium max-w-xs truncate">{{ tx.description }}</div>
                                    </td>
                                    <td class="p-6 text-center">
                                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wider inline-block border transition-all duration-300 shadow-sm" 
                                            :class="{
                                                'bg-[#fbcdab] text-[#2a273c] border-[#fbcdab]': tx.type === 'charge',
                                                'bg-[#8f9793]/10 text-[#2a273c] border-[#8f9793]/20': tx.type === 'payment',
                                                'bg-[#e95a54] text-white border-[#e95a54]': tx.type === 'refund',
                                                'bg-indigo-50 text-indigo-700 border-indigo-100': tx.type === 'adjustment'
                                            }">
                                            {{ tx.type }}
                                        </span>
                                    </td>
                                    <td class="p-6">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-2 h-2 rounded-full bg-[#fbcdab]"></div>
                                            <span class="text-xs font-bold text-[#2a273c]">{{ tx.payment_method || 'Internal' }}</span>
                                        </div>
                                    </td>
                                    <td class="p-6 text-right">
                                        <div class="text-base font-black tracking-tight" :class="tx.type === 'payment' ? 'text-[#8f9793]' : 'text-[#2a273c]'">
                                            {{ tx.type === 'payment' ? '-' : '' }}{{ Number(tx.amount).toLocaleString(undefined, {minimumFractionDigits: 2}) }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Simplified Pagination UI -->
                    <div v-if="transactions?.links?.length > 3" class="p-6 border-t border-[#f2f0eb] flex justify-center space-x-2">
                        <template v-for="(link, k) in transactions.links" :key="k">
                            <button v-if="link.url" 
                                @click="router.get(link.url)"
                                v-html="link.label"
                                :disabled="link.active"
                                class="px-4 py-2 rounded-xl text-xs font-black transition-all"
                                :class="link.active ? 'bg-[#2a273c] text-white' : 'bg-[#f2f0eb] text-[#8f9793] hover:bg-[#e95a54] hover:text-white'">
                            </button>
                        </template>
                    </div>
                </div>
            </section>

            <!-- AR Section -->
            <section v-if="tab === 'ar'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="account in accounts?.data" :key="account.id" class="group bg-white rounded-[2.5rem] p-8 shadow-xl hover:shadow-2xl transition-all duration-500 border border-white hover:-translate-y-2">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-14 h-14 bg-[#2a273c] rounded-[1.25rem] flex items-center justify-center text-[#fbcdab] font-black text-xl group-hover:bg-[#e95a54] group-hover:text-white transition-all duration-500 shadow-lg">
                                {{ account.company_name.charAt(0) }}
                            </div>
                            <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border transition-all"
                                :class="account.status === 'active' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-red-50 text-red-600 border-red-100'">
                                {{ account.status }}
                            </span>
                        </div>
                        
                        <h3 class="text-2xl font-black text-[#2a273c] mb-1 group-hover:text-[#e95a54] transition-colors line-clamp-1 italic">{{ account.company_name }}</h3>
                        <div class="text-[11px] text-[#8f9793] font-bold uppercase tracking-wider mb-6 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            {{ account.contact_person }}
                        </div>

                        <div class="bg-[#f2f0eb]/50 rounded-3xl p-6 space-y-4 mb-6 border border-[#f2f0eb]">
                            <div class="flex justify-between items-end">
                                <span class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Current Balance</span>
                                <span class="text-xl font-black text-[#e95a54]">SAR {{ Number(account.current_balance).toLocaleString() }}</span>
                            </div>
                            <div class="w-full bg-[#f2f0eb] h-2 rounded-full overflow-hidden flex">
                                <div class="bg-[#e95a54] h-full transition-all duration-1000" :style="`width: ${(account.current_balance / account.credit_limit) * 100}%`"></div>
                            </div>
                            <div class="flex justify-between items-center text-[10px] font-bold">
                                <span class="text-[#8f9793]">Utilized Credit</span>
                                <span class="text-[#2a273c]">{{ ((account.current_balance / account.credit_limit) * 100).toFixed(0) }}% / SAR {{ Number(account.credit_limit).toLocaleString() }}</span>
                            </div>
                        </div>

                        <div class="flex space-x-3">
                            <button @click="openModal('ar', account)" class="flex-1 bg-[#2a273c] text-white py-3.5 rounded-2xl font-black text-xs tracking-widest hover:bg-[#e95a54] transition-all duration-300 shadow-lg active:scale-95">ACCOUNT RECON</button>
                            <button class="w-14 bg-white border-2 border-[#f2f0eb] rounded-2xl flex items-center justify-center hover:border-[#e95a54] hover:text-[#e95a54] transition-all text-[#8f9793]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Comps Section -->
            <section v-if="tab === 'comps'" class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden border border-[#8f9793]/10 overflow-x-auto min-w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#2a273c] text-[#fbcdab] text-[10px] uppercase tracking-[0.2em] italic">
                            <th class="p-8 font-black">Audit Marker</th>
                            <th class="p-8 font-black">Cost Center</th>
                            <th class="p-8 font-black">Justification</th>
                            <th class="p-8 font-black">Approval Authority</th>
                            <th class="p-8 font-black text-right">Value Asset (SAR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f2f0eb]">
                        <tr v-for="comp in comps?.data" :key="comp.id" class="hover:bg-[#f2f0eb]/50 transition-all group">
                            <td class="p-8">
                                <div class="font-black text-[#2a273c]">{{ comp.booking_reference }}</div>
                                <div class="text-[10px] text-[#8f9793] font-bold">{{ new Date(comp.date_posted).toLocaleDateString() }}</div>
                            </td>
                            <td class="p-8">
                                <span class="bg-[#f2f0eb] text-[#2a273c] px-4 py-1.5 rounded-full font-black text-[9px] uppercase tracking-wider border border-[#8f9793]/20 shadow-sm transition-all group-hover:bg-[#fbcdab]">
                                    {{ comp.department.replace('_', ' & ') }}
                                </span>
                            </td>
                            <td class="p-8">
                                <div class="font-bold text-[#2a273c] text-sm leading-tight italic">{{ comp.reason }}</div>
                            </td>
                            <td class="p-8">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 rounded-full bg-[#fbcdab]/30 flex items-center justify-center font-black text-[10px] text-[#2a273c]">JS</div>
                                    <span class="text-xs font-black text-[#2a273c]">{{ comp.approved_by }}</span>
                                </div>
                            </td>
                            <td class="p-8 text-right font-black text-xl text-[#e95a54] tabular-nums">
                                {{ Number(comp.value_amount).toLocaleString(undefined, {minimumFractionDigits: 2}) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- EOD Section -->
            <section v-if="tab === 'eod'" class="space-y-10">
                <div class="relative bg-[#2a273c] rounded-[3rem] p-10 md:p-14 shadow-2xl text-[#f2f0eb] border-2 border-white/5 overflow-hidden group">
                    <div class="absolute right-0 bottom-0 w-96 h-96 bg-[#e95a54] rounded-full blur-[100px] opacity-10 group-hover:opacity-20 transition-opacity duration-1000"></div>
                    
                    <div class="relative z-10 flex flex-col lg:flex-row justify-between items-center lg:items-end space-y-10 lg:space-y-0">
                        <div class="text-center lg:text-left">
                            <div class="inline-flex items-center space-x-2 bg-white/5 backdrop-blur-md px-4 py-2 rounded-full mb-6 border border-white/10 shadow-inner">
                                <span class="w-2 h-2 bg-red-500 rounded-full animate-ping"></span>
                                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-[#fbcdab]">System Audit Pending</span>
                            </div>
                            <h2 class="text-4xl md:text-6xl font-black mb-4 tracking-tighter leading-none italic">End of Day <br> Audit Protocol.</h2>
                            <p class="text-[#8f9793] max-w-lg font-bold text-sm md:text-base leading-relaxed">
                                The Night Audit finalizes all daily transactions, verifies guest balances, and advance the system business date. This action is irreversible.
                            </p>
                        </div>
                        
                        <div class="flex flex-col items-center space-y-6">
                            <div class="text-center">
                                <div class="text-4xl font-black text-[#fbcdab] mb-1 tabular-nums">SAR {{ Number(metrics.today_revenue).toLocaleString() }}</div>
                                <div class="text-[10px] font-black uppercase tracking-widest text-[#8f9793]">Total Revenue for Audit Cycle</div>
                            </div>
                            <form @submit.prevent="runEod">
                                <button type="submit" class="bg-[#e95a54] text-white px-12 py-5 rounded-[2rem] shadow-[0_20px_50px_rgba(233,90,84,0.3)] hover:bg-white hover:text-[#e95a54] transition-all duration-500 font-black text-lg tracking-widest border-2 border-transparent hover:border-[#e95a54] transform hover:scale-105 active:scale-95 italic">
                                    EXECUTE PROTOCOL
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden border border-[#8f9793]/10">
                    <div class="p-8 border-b border-[#f2f0eb] flex justify-between items-center bg-white/50 backdrop-blur-md">
                        <h3 class="font-black text-[#2a273c] text-lg italic tracking-tight uppercase">Audit Logs Hierarchy</h3>
                        <button class="text-[#8f9793] hover:text-[#e95a54] transition-colors"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg></button>
                    </div>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#f2f0eb]/30 text-[#8f9793] text-[9px] uppercase tracking-[0.3em]">
                                <th class="p-8 font-black">Audit Date</th>
                                <th class="p-8 font-black">Final Revenue</th>
                                <th class="p-8 font-black">Settlements</th>
                                <th class="p-8 font-black text-center">Status</th>
                                <th class="p-8 font-black">Execution Lead</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f2f0eb]">
                            <tr v-for="eod in eods?.data" :key="eod.id" class="hover:bg-[#f2f0eb]/20 group transition-all duration-300">
                                <td class="p-8 font-black text-[#2a273c] text-sm italic">{{ new Date(eod.audit_date).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'}) }}</td>
                                <td class="p-8 font-black text-[#2a273c] tabular-nums">SAR {{ Number(eod.total_revenue).toLocaleString() }}</td>
                                <td class="p-8 font-bold text-[#8f9793] tabular-nums">SAR {{ Number(eod.total_payments).toLocaleString() }}</td>
                                <td class="p-8 text-center text-xs">
                                    <span class="px-4 py-1 rounded-full font-black uppercase text-[9px] shadow-sm transform group-hover:scale-110 transition-transform duration-500" 
                                        :class="eod.status === 'completed' ? 'bg-[#fbcdab] text-[#2a273c]' : 'bg-red-100 text-red-600'">
                                        {{ eod.status }}
                                    </span>
                                </td>
                                <td class="p-8 text-[#2a273c] font-black text-xs group-hover:text-[#e95a54] transition-colors flex items-center">
                                    <div class="w-2 h-2 bg-[#fbcdab] rounded-full mr-2"></div>
                                    {{ eod.run_by }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <!-- Dynamic Modal Base -->
        <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to-class="opacity-100 translate-y-0 sm:scale-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100 translate-y-0 sm:scale-100" leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <div v-if="isModalOpen" class="fixed inset-0 bg-[#2a273c]/90 backdrop-blur-md flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-[3rem] p-10 max-w-xl w-full shadow-[0_50px_100px_rgba(0,0,0,0.5)] border border-[#8f9793]/10 relative overflow-hidden animate-in zoom-in-95 duration-300">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-[#fbcdab] rounded-full blur-[60px] opacity-30"></div>
                    
                    <button @click="isModalOpen = false" class="absolute right-8 top-8 text-[#8f9793] hover:text-[#2a273c] transition-colors group">
                        <svg class="w-8 h-8 group-hover:rotate-90 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <!-- Modals Contents -->
                    <form v-if="modalType === 'cashiering'" @submit.prevent="submitCashiering" class="space-y-6 relative z-10">
                        <div class="mb-8">
                            <h2 class="text-4xl font-black text-[#2a273c] tracking-tighter italic">Post Transaction.</h2>
                            <p class="text-[#8f9793] font-bold text-xs uppercase tracking-widest mt-1">Operational Revenue Portal</p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Entry Type</label>
                                <select v-model="cashForm.type" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-bold text-[#2a273c] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all cursor-pointer">
                                    <option value="charge">Debit / Charge</option>
                                    <option value="payment">Credit / Payment</option>
                                    <option value="refund">Reversal / Refund</option>
                                    <option value="adjustment">Internal Adjustment</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Asset Value (SAR)</label>
                                <input v-model="cashForm.amount" type="number" step="0.01" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-black text-[#e95a54] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Client Identifier (Booking/AR)</label>
                            <input v-model="cashForm.booking_reference" type="text" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-bold text-[#2a273c] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all placeholder:text-[#8f9793]/50" placeholder="Enter Reference Code">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Audit Description</label>
                            <input v-model="cashForm.description" type="text" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-bold text-[#2a273c] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all" placeholder="Explain the transaction context" required>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Settlement Route</label>
                                <select v-model="cashForm.payment_method" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-bold text-[#2a273c] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all">
                                    <option>Visa</option>
                                    <option>Mada</option>
                                    <option>Cash</option>
                                    <option>CityLedger (AR)</option>
                                    <option>MasterCard</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Ledger Date</label>
                                <input v-model="cashForm.transaction_date" type="date" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-bold text-[#2a273c] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all" required>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-[#f2f0eb] flex justify-end">
                            <button type="submit" :disabled="cashForm.processing" class="w-full md:w-auto bg-[#2a273c] text-[#fbcdab] px-12 py-5 rounded-2xl font-black text-xs tracking-[0.2em] hover:bg-[#e95a54] hover:text-white transition-all duration-500 shadow-2xl disabled:opacity-50 italic">
                                {{ cashForm.processing ? 'AUDITING...' : 'CONFIRM POSTING' }}
                            </button>
                        </div>
                    </form>

                    <!-- AR Modal Content -->
                    <form v-if="modalType === 'ar'" @submit.prevent="submitAr" class="space-y-6 relative z-10">
                        <div class="mb-8">
                            <h2 class="text-4xl font-black text-[#2a273c] tracking-tighter italic">Corporate Ledger.</h2>
                            <p class="text-[#8f9793] font-bold text-xs uppercase tracking-widest mt-1">{{ editMode ? 'Modify Credit Terms' : 'New Credit Facility' }}</p>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Holding Company</label>
                            <input v-model="arForm.company_name" type="text" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-black text-[#2a273c] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all" placeholder="e.g. Saudi Aramco" required>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Appointed Controller</label>
                            <input v-model="arForm.contact_person" type="text" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-bold text-[#2a273c] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all" placeholder="Full Name" required>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Secure Email</label>
                                <input v-model="arForm.email" type="email" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-bold text-[#2a273c] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all" required>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Direct Line</label>
                                <input v-model="arForm.phone" type="text" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-bold text-[#2a273c] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Credit Exposure Limit (SAR)</label>
                                <input v-model="arForm.credit_limit" type="number" step="1000" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-black text-[#e95a54] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all" required>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Account Status</label>
                                <select v-model="arForm.status" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-bold text-[#2a273c] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all">
                                    <option value="active">Operational / Active</option>
                                    <option value="suspended">Suspended / Review</option>
                                    <option value="closed">Permanently Closed</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-[#f2f0eb] flex justify-end space-x-4">
                            <button type="button" @click="isModalOpen = false" class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest px-6 py-4 hover:text-[#2a273c] transition-colors">Discard</button>
                            <button type="submit" :disabled="arForm.processing" class="flex-1 bg-[#2a273c] text-[#fbcdab] px-8 py-5 rounded-2xl font-black text-xs tracking-widest hover:bg-[#e95a54] hover:text-white transition-all duration-500 shadow-xl italic uppercase">
                                {{ arForm.processing ? 'SAVING...' : (editMode ? 'UPDATE FACILITY' : 'ESTABLISH FACILITY') }}
                            </button>
                        </div>
                    </form>

                    <!-- Comp Modal Content -->
                    <form v-if="modalType === 'comp'" @submit.prevent="submitComp" class="space-y-6 relative z-10">
                        <div class="mb-8">
                            <h2 class="text-4xl font-black text-[#2a273c] tracking-tighter italic">Gratis Allocation.</h2>
                            <p class="text-[#8f9793] font-bold text-xs uppercase tracking-widest mt-1">Non-Revenue Value Posting</p>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Booking Reference</label>
                            <input v-model="compForm.booking_reference" type="text" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-black text-[#2a273c] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all" placeholder="VIP Target Reference" required>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Department Allocation</label>
                                <select v-model="compForm.department" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-bold text-[#2a273c] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all">
                                    <option value="rooms">Rooms Division</option>
                                    <option value="f_and_b">Food & Beverage</option>
                                    <option value="spa">SPA & Wellness</option>
                                    <option value="transport">Luxury Transport</option>
                                    <option value="other">Institutional</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Asset Value (SAR)</label>
                                <input v-model="compForm.value_amount" type="number" step="0.01" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-black text-[#e95a54] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-[#8f9793] uppercase tracking-widest">Managerial Justification</label>
                            <textarea v-model="compForm.reason" rows="4" class="w-full bg-[#f2f0eb] rounded-2xl border-none p-4 text-sm font-bold text-[#2a273c] focus:ring-4 focus:ring-[#fbcdab]/50 transition-all placeholder:font-normal" placeholder="Detailed reason for complimentary allocation..." required></textarea>
                        </div>

                        <div class="pt-8 border-t border-[#f2f0eb]">
                            <button type="submit" :disabled="compForm.processing" class="w-full bg-[#2a273c] text-[#fbcdab] py-6 rounded-3xl font-black text-sm tracking-[0.2em] hover:bg-[#e95a54] hover:text-white transition-all duration-700 shadow-2xl active:scale-95 italic">
                                {{ compForm.processing ? 'AUTHENTICATING...' : 'AUTHORIZE GRATIS POSTING' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- Notification Toast -->
        <Transition enter-active-class="transform transition ease-out duration-300" enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="$page.props.flash.success" class="fixed right-6 bottom-6 flex items-center bg-[#2a273c] text-white px-8 py-5 rounded-3xl shadow-[0_30px_60px_rgba(0,0,0,0.4)] border border-white/10 z-[100] group">
                <div class="w-10 h-10 bg-[#fbcdab] rounded-2xl mr-4 flex items-center justify-center text-[#2a273c] group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-[#8f9793]">Ledger Notification</div>
                    <div class="font-black italic text-sm tracking-tight">{{ $page.props.flash.success }}</div>
                </div>
                <button @click="$page.props.flash.success = null" class="ml-8 text-[#8f9793] hover:text-[#fbcdab]"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg></button>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    tab: String,
    metrics: Object,
    transactions: Object,
    accounts: Object,
    comps: Object,
    eods: Object,
    latest_eod: Object,
});

const switchTab = (t) => {
    router.get('/financials', { tab: t }, { preserveState: true, preserveScroll: true });
};

// State
const isModalOpen = ref(false);
const modalType = ref('');
const editMode = ref(false);
let editingId = null;

// Forms
const cashForm = useForm({
    type: 'charge',
    amount: '',
    booking_reference: '',
    description: '',
    payment_method: 'Visa',
    transaction_date: new Date().toISOString().split('T')[0]
});

const arForm = useForm({
    company_name: '',
    contact_person: '',
    email: '',
    phone: '',
    credit_limit: 0,
    status: 'active'
});

const compForm = useForm({
    booking_reference: '',
    department: 'rooms',
    value_amount: '',
    reason: ''
});

const openModal = (type, data = null) => {
    modalType.value = type;
    editMode.value = !!data;
    
    if(type === 'cashiering') cashForm.reset();
    
    if(type === 'ar') {
        if(data) {
            editingId = data.id;
            arForm.company_name = data.company_name;
            arForm.contact_person = data.contact_person;
            arForm.email = data.email;
            arForm.phone = data.phone;
            arForm.credit_limit = data.credit_limit;
            arForm.status = data.status;
        } else {
            arForm.reset();
        }
    }
    
    if(type === 'comp') compForm.reset();
    
    isModalOpen.value = true;
};

const submitCashiering = () => {
    cashForm.post('/financials/transactions', {
        onSuccess: () => {
            isModalOpen.value = false;
        }
    });
};

const submitAr = () => {
    if(editMode.value) {
        arForm.put(`/financials/ar-accounts/${editingId}`, {
            onSuccess: () => isModalOpen.value = false
        });
    } else {
        arForm.post('/financials/ar-accounts', {
            onSuccess: () => isModalOpen.value = false
        });
    }
};

const submitComp = () => {
    compForm.post('/financials/comps', {
        onSuccess: () => isModalOpen.value = false
    });
};

const runEod = () => {
    if(confirm('SYSTEM PROTOCOL REQUIRED: Initiating End of Day audit will finalize all previous ledger entries. Continue?')) {
        router.post('/financials/eod');
    }
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
