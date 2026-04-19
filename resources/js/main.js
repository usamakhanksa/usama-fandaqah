import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import VueApexCharts from 'vue3-apexcharts';
createApp(App).use(createPinia()).use(router).use(VueApexCharts).mount('#app');
