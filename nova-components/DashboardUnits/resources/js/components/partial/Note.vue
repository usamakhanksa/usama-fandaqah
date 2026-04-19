<template>
    <div v-if="maintenance" class="unit_moved_msg" role="alert">
      {{__( message) }}
    </div>
  </template>

  <script>
  export default {
    name: "note",
    data() {
      return {
        maintenance: false,
        message: "",
      };
    },
    mounted() {
      this.checkMaintenanceStatus();
    },
    methods: {
      async checkMaintenanceStatus() {
        try {
          const response = await fetch('/nova-vendor/DashboardUnits/get-maintance-msg');
          const data = await response.json();
          if (data.maintenance) {
            this.maintenance = true;
            this.message = data.message;
          }
        } catch (error) {
          console.error('Error fetching maintenance status:', error);
        }
      },
    },
  };
  </script>

  <style lang="scss" scoped>
  .unit_moved_msg {
    background: #ff8383;
    border: 1px solid #ff8383;
    padding: 10px;
    border-radius: 4px;
    font-size: 15px;
    color: black;
    margin: 0 auto 15px;
    a {
      color: #002752;
      font-weight: bold;
      outline: none;
      &:hover {
        text-decoration: underline;
      }
    }
  }
  </style>
