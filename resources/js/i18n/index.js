import { createI18n } from 'vue-i18n';
import en from './en.json';
import ar from './ar.json';

const i18n = createI18n({
  legacy: false,
  locale: (window.__locale && typeof window.__locale === 'string') ? window.__locale : (document.documentElement.lang || localStorage.getItem('lang') || 'en'),
  fallbackLocale: 'en',
  messages: {
    en,
    ar
  }
});

export default i18n;
