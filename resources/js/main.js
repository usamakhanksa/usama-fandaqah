import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import VueApexCharts from 'vue3-apexcharts';

const app = createApp(App);

app.config.errorHandler = (err, instance, info) => {
  console.error('[Vue Error]', err, info);
  const el = document.getElementById('app');
  if (el) {
    el.innerHTML = `<div style="color:red;padding:20px;font-family:monospace">
      <h2>Vue Error</h2><pre>${err.message}\n\n${err.stack}</pre>
    </div>`;
  }
};

try {
  app.use(createPinia()).use(router).use(VueApexCharts).mount('#app');
} catch (e) {
  console.error('[Mount Error]', e);
  const el = document.getElementById('app');
  if (el) {
    el.innerHTML = `<div style="color:red;padding:20px;font-family:monospace">
      <h2>Mount Failed</h2><pre>${e.message}\n\n${e.stack}</pre>
    </div>`;
  }
}
