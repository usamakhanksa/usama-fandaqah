<template>
  <div class="unit-table-container">
   <heading class="my-1">
            <router-link :to="{ name: 'channel-manager' }" class="back-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </router-link>

        </heading>
    <table class="unit-table">
      <thead>
        <tr>
          <th>Input</th>
          <th>Unit Count</th>
          <th>Name</th>
          <th>ID</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="unit in units" :key="unit.id">
          <td>
            <input
            style="width: -webkit-fill-available;
                    text-align: center;"
              type="number"
              v-model.number="unitInputs[unit.id]"
              :max="unit.units_count"
              :min="0"
              @input="validateInput(unit.id, unit.units_count)"
            />
          </td>
          <td>{{ unit.units_count }}</td>
          <td>{{ unit.name }}</td>
          <td>{{ unit.id }}</td>
        </tr>
      </tbody>
    </table>
    <button @click="sendSelectedUnits">Push Selected Units</button>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      team_id: Nova.app.currentTeam.id,
      units: [],
      unitInputs: {},
      local: Nova.config.local,
    };
  },
  mounted() {
    this.fetchUnits();
  },
  methods: {
    fetchUnits() {
      axios.post(`/nova-vendor/settings/get-units`, {
        team_id: this.team_id,
        lang: this.local
      })
        .then(response => {
          this.units = response.data;
          this.initializeUnitInputs();
        })
        .catch(error => {
          console.error('Error fetching units:', error);
        });
    },
    initializeUnitInputs() {
      this.units.forEach(unit => {
        this.$set(this.unitInputs, unit.id, unit.synced_units || 0);
      });
    },
    validateInput(id, max) {
      if (this.unitInputs[id] > max) {
        this.unitInputs[id] = max;
      } else if (this.unitInputs[id] < 0) {
        this.unitInputs[id] = 0;
      }
    },
    sendSelectedUnits() {
      const selectedUnits = this.units.map(unit => ({
        id: unit.id,
        synced_units: this.unitInputs[unit.id]
      }));

      axios.post(`/nova-vendor/settings/update-units`, {
        units: selectedUnits,
        team_id: this.team_id
      })
        .then(response => {
          console.log('Response:', response.data);
          const message = response.data.message;
          if (message === 'Units updated successfully') {
            this.$toasted.show('Units updated successfully', { type: 'success' });
          } else {
            this.$toasted.show('Error updating units', { type: 'error' });
          }
        })
        .catch(error => {
          console.error('Error sending selected units:', error);
          this.$toasted.show('Error updating units', { type: 'error' });
        });
    }
  }
};
</script>

<style scoped>
.unit-table-container {
  padding: 20px;
  font-family: Arial, sans-serif;
}

.unit-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}

.unit-table th,
.unit-table td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

.unit-table th {
  background-color: #f2f2f2;
  color: #333;
}

.unit-table tr:nth-child(even) {
  background-color: #f9f9f9;
}

.unit-table tr:hover {
  background-color: #ddd;
}

.selected-units h3 {
  margin-top: 0;
}

.selected-units ul {
  list-style-type: none;
  padding-left: 0;
}

.selected-units li {
  padding: 5px 0;
}
.back-icon {
    display: inline-block;
    margin-right: 10px;
    vertical-align: middle;
}
.back-icon svg {
    width: 24px;
    height: 24px;
    stroke: #000;
}

button {
  background-color: #4CAF50; /* Green */
  border: none;
  color: white;
  padding: 10px 20px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  margin: 10px 2px;
  cursor: pointer;
  border-radius: 4px;
}
</style>
