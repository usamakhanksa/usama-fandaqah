<!-- partial/TransactionBalance.vue -->
<template>
  <div v-if="visible" class="modal-overlay" @click.self="close">
    <div class="modal">
      <h1>{{ __('Transactions Balance') }}</h1>
      <p class="note">{{ __('This tab is used to view and manage balance related to a reservation.') }}</p>
      <p v-if="loading">{{ __('Loading transactions...') }}</p>
      <p v-if="!loading && !transactions.length">{{ __('No transactions found.') }}</p>
      <div class="table-scroll-container">
        <table v-if="!loading && transactions.length" class="transaction-table" :class="{ 'force-left': isArabic }">
          <thead>
            <tr>
              <th>{{ __('Type') }}</th>
              <th>{{ __('Amount') }}</th>
              <th>{{ __('Created By') }}</th>
              <th>{{ __('Public') }}</th>
              <th>{{ __('Insurance') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(transaction, index) in transactions" :key="index">
              <td>{{ transaction.type }}</td>
              <td>{{ transaction.amount / 100 }} <span class="icon-saudi_riyal"></span></td>
              <td>{{ transaction.created_by }}</td>
              <td>
                <span :class="{ 'text-green': transaction.is_public, 'text-red': !transaction.is_public }">
                  {{ transaction.is_public ? __('Yes') : __('No') }}
                </span>
              </td>
              <td>
                <span :class="{ 'text-green': transaction.is_insurance, 'text-red': !transaction.is_insurance }">
                  {{ transaction.is_insurance ? __('Yes') : __('No') }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p class = 'note'>{{ __('Note that public transactions are transactions made by user, while private transactions are made by the system to manage the balance of the reservation.') }}</p>
    <div class="balance-toolbar">
      <div v-if="!loading && transactions.length" class="total-balance">
        <strong>{{ __('Total Balance') }}:</strong> 
        <span :class="{'negative': totalBalance < 0, 'positive': totalBalance >= 0}">
          {{ totalBalance.toFixed(2) }}
        </span> 
        <span class="icon-saudi_riyal"></span>
      </div>

      <div class="modify-balance">
        <input
          type="number"
          v-model="newTransactionAmount"
          class="amount-input"
        />
        <button @click="addTransaction" class="add-btn">{{ __('Update Balance') }}</button>
      </div>
    </div>
    <p class = 'note'>{{ __('Please refresh the page to see the changes in the balance.') }}</p>



      <button class="close-btn" @click="close">{{ __('Close') }}</button>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      visible: false,
      reservationId: null,
      transactions: [],
      loading: false,
      newTransactionAmount: '',
      locale: Nova.config.local
    };
  },
  computed: {
    isArabic() {
      return this.locale === 'ar';
    },
    
    totalBalance() {
      const totalCents = this.transactions
        .filter(t => !t.is_insurance) // exclude insurance transactions
        .reduce((sum, t) => sum + Number(t.amount), 0);

      return totalCents / 100;
    }
  },
  methods: {
    open(reservationId) {
      this.visible = true;
      this.reservationId = reservationId;
      this.fetchTransactions();
    },
    close() {
      this.visible = false;
      this.reservationId = null;
      this.transactions = [];
      this.loading = false;
    },
    async fetchTransactions() {
      this.loading = true;
      axios.get(`/nova-vendor/calender/reservations/${this.reservationId}/transactions`)
        .then((response) => {
            this.loading = false;
            this.transactions = response.data;
        })
        .catch((error) => {
            this.loading = false;
            console.error("Error fetching transactions:", error);
            this.transactions = [];
        });

    },
    async addTransaction() {
      if (!this.newTransactionAmount) {
        alert('Please enter an amount.');
        return;
      }

      try {
        const response = await axios.post(`/nova-vendor/calender/reservations/${this.reservationId}/transactions`, {
          amount: this.newTransactionAmount,
          reservation_id: this.reservationId,
        });

        this.transactions.push(response.data);
        this.newTransactionAmount = ''; // Clear input after adding
        this.fetchTransactions(); // Refresh transactions list

      } catch (error) {
        console.error("Error adding transaction:", error);
        alert('Failed to add transaction. Please try again.');
      }
    }
    
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal {
  background: white;
  padding: 20px;
  border-radius: 3px;
  width: 900px;         /* Increased from 600px */
  max-width: 95%;       /* Optional: more responsive on small screens */
  max-height: 90vh;     /* Optional: to avoid overflowing vertically */
  overflow-y: auto;     /* Enables scroll if content is tall */
}

.transaction-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

.transaction-table th,
.transaction-table td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: left;
}

.text-green {
  color: green;
}
.text-red {
  color: red;
}
.total-balance {
  margin-top: 15px;
  font-size: 1.2em;
  text-align: right;
}
.close-btn {
  margin-top: 20px;
  background-color: #e53e3e;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.close-btn:hover {
  background-color: #c53030;
}
.negative {
  color: red;
}

.positive {
  color: green;
}
.balance-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1rem;
  flex-wrap: wrap; /* Optional for mobile responsiveness */
}

.total-balance {
  font-size: 16px;
}

.modify-balance {
  display: flex;
  gap: 8px;
  align-items: center;
}

.amount-input {
  padding: 6px 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  width: 120px;
}

.add-btn {
  background-color: #38a169; /* green */
  color: white;
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.add-btn:hover {
  background-color: #2f855a;
}
.note {
  font-size: 0.9em;
  font-style: italic;
  color: #666;
  margin-top: 10px;
}
.transaction-table {
  width: 100%;
  border-collapse: collapse;
}

.transaction-table th, 
.transaction-table td {
  padding: 8px 12px;
  border: 1px solid #ddd;
  text-align: left;
}

.transaction-table tbody tr:nth-child(odd) {
  background-color: #f9f9f9;
}

.transaction-table tbody tr:nth-child(even) {
  background-color: #fff;
}

.transaction-table tbody tr:hover {
  background-color: #e6f7ff;
}
.table-scroll-container {
  max-height: 500px; /* or any height you prefer */
  overflow-y: auto;
  /* border: 1px solid #ccc; */
}

/* Optional: keep header fixed (more advanced) */
.transaction-table thead th {
  position: sticky;
  top: 0;
  background: #fff;
  z-index: 1;
}
.force-left th,
.force-left td {
  text-align: right !important;
  direction: ltr;
}
</style>
