import { defineStore } from 'pinia';

const STORAGE_KEY = 'fandaqah_slider_settings';

const defaultCards = [
  {
    id: 1,
    title: 'SMS Feature Raseel',
    description: 'Send instant SMS to guests directly from Fandaqah PMS',
    colorClass: 'card-coral',
    iconType: 'mail',
  },
  {
    id: 2,
    title: 'Zatca Integration',
    description: 'Integration with Zakat, Tax and Customs Authority',
    colorClass: 'card-navy',
    iconType: 'monitor',
  },
  {
    id: 3,
    title: 'Shomoos Integration',
    description: 'Instantly integrate guest data with Shomoos',
    colorClass: 'card-beige',
    iconType: 'user',
  },
  {
    id: 4,
    title: 'Passport Scanner',
    description: 'Faster, more accurate guest data entry',
    colorClass: 'card-green',
    iconType: 'calendar',
  },
  {
    id: 5,
    title: 'Payment Gateway',
    description: 'Secure and seamless payment processing',
    colorClass: 'card-peach',
    iconType: 'dollar',
  },
];

function loadFromStorage() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

function saveToStorage(data) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
  } catch {}
}

export const useSliderStore = defineStore('slider', {
  state: () => {
    const saved = loadFromStorage();
    return {
      // Header
      logoText: saved?.logoText ?? 'fandaqah',
      logoUrl: saved?.logoUrl ?? '',
      showFeatureSlider: saved?.showFeatureSlider ?? true,

      // Slider behaviour
      animationDuration: saved?.animationDuration ?? 35, // seconds
      cardWidth: saved?.cardWidth ?? 320,
      cardGap: saved?.cardGap ?? 20,

      // Cards data
      cards: saved?.cards ?? JSON.parse(JSON.stringify(defaultCards)),
    };
  },

  getters: {
    totalSlideWidth(state) {
      return (state.cardWidth + state.cardGap) * state.cards.length;
    },
  },

  actions: {
    persist() {
      saveToStorage({
        logoText: this.logoText,
        logoUrl: this.logoUrl,
        showFeatureSlider: this.showFeatureSlider,
        animationDuration: this.animationDuration,
        cardWidth: this.cardWidth,
        cardGap: this.cardGap,
        cards: this.cards,
      });
    },

    updateLogo(payload) {
      if ('logoText' in payload) this.logoText = payload.logoText;
      if ('logoUrl' in payload) this.logoUrl = payload.logoUrl;
      this.persist();
    },

    toggleSlider(val) {
      this.showFeatureSlider = val;
      this.persist();
    },

    updateCard(id, payload) {
      const idx = this.cards.findIndex(c => c.id === id);
      if (idx !== -1) {
        this.cards[idx] = { ...this.cards[idx], ...payload };
        this.persist();
      }
    },

    addCard() {
      const newId = Date.now();
      this.cards.push({
        id: newId,
        title: 'New Feature',
        description: 'Describe this feature here',
        colorClass: 'card-coral',
        iconType: 'mail',
      });
      this.persist();
    },

    removeCard(id) {
      this.cards = this.cards.filter(c => c.id !== id);
      this.persist();
    },

    reorderCard(fromIdx, toIdx) {
      const arr = [...this.cards];
      const [moved] = arr.splice(fromIdx, 1);
      arr.splice(toIdx, 0, moved);
      this.cards = arr;
      this.persist();
    },

    updateSliderSettings(payload) {
      if ('animationDuration' in payload) this.animationDuration = payload.animationDuration;
      if ('cardWidth' in payload) this.cardWidth = payload.cardWidth;
      if ('cardGap' in payload) this.cardGap = payload.cardGap;
      this.persist();
    },

    resetToDefaults() {
      this.logoText = 'fandaqah';
      this.logoUrl = '';
      this.showFeatureSlider = true;
      this.animationDuration = 35;
      this.cardWidth = 320;
      this.cardGap = 20;
      this.cards = JSON.parse(JSON.stringify(defaultCards));
      this.persist();
    },
  },
});
