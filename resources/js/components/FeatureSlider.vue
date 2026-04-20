<template>
  <div v-if="store.showFeatureSlider" class="feature-slider-wrapper">
    <div class="slider-container" @mouseenter="paused = true" @mouseleave="paused = false">
      <!-- Prev Button -->
      <button class="nav-btn prev" @click="scrollManual('left')" aria-label="Previous">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="15 18 9 12 15 6" />
        </svg>
      </button>

      <!-- Track -->
      <div class="slider-track" ref="trackRef" :style="trackStyle">
        <template v-for="card in displayCards" :key="card._uid">
          <div :class="['feature-card', card.colorClass]" :style="{ width: store.cardWidth + 'px' }">
            <!-- Icon -->
            <div class="card-icon">
              <component :is="iconMap[card.iconType] || iconMap.mail" class="card-svg" />
            </div>
            <!-- Content -->
            <div class="card-content">
              <h3 class="card-title">{{ card.title }}</h3>
              <p class="card-description">{{ card.description }}</p>
            </div>
            <!-- Arrow -->
            <svg class="share-icon" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="22" y1="2" x2="11" y2="13" />
              <polygon points="22 2 15 22 11 13 2 9 22 2" />
            </svg>
          </div>
        </template>
      </div>

      <!-- Next Button -->
      <button class="nav-btn next" @click="scrollManual('right')" aria-label="Next">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="9 18 15 12 9 6" />
        </svg>
      </button>

      <!-- Progress Bar -->
      <div class="slider-progress" :style="progressStyle" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, h } from 'vue';
import { useSliderStore } from '../stores/slider';

const store = useSliderStore();
const paused = ref(false);
const trackRef = ref(null);
const offset = ref(0);
let rafId = null;
let lastTime = null;

// Duplicate cards for seamless infinite scroll
const displayCards = computed(() => {
  const base = store.cards;
  return [...base, ...base].map((c, i) => ({ ...c, _uid: `${c.id}-${i}` }));
});

const cardStep = computed(() => store.cardWidth + store.cardGap);
const loopWidth = computed(() => cardStep.value * store.cards.length);
const pxPerSecond = computed(() => loopWidth.value / store.animationDuration);

const trackStyle = computed(() => ({
  display: 'flex',
  gap: store.cardGap + 'px',
  willChange: 'transform',
  transform: `translateX(${-offset.value}px)`,
}));

const progressStyle = computed(() => ({
  width: loopWidth.value > 0
    ? `${((offset.value % loopWidth.value) / loopWidth.value) * 100}%`
    : '0%',
}));

function animate(ts) {
  if (!paused.value) {
    if (lastTime !== null) {
      const delta = (ts - lastTime) / 1000;
      offset.value += pxPerSecond.value * delta;
      // Seamless reset
      if (offset.value >= loopWidth.value) {
        offset.value -= loopWidth.value;
      }
    }
    lastTime = ts;
  } else {
    lastTime = null;
  }
  rafId = requestAnimationFrame(animate);
}

function scrollManual(dir) {
  const step = cardStep.value;
  if (dir === 'left') {
    offset.value = Math.max(0, offset.value - step);
  } else {
    offset.value += step;
    if (offset.value >= loopWidth.value) offset.value -= loopWidth.value;
  }
}

onMounted(() => { rafId = requestAnimationFrame(animate); });
onBeforeUnmount(() => { if (rafId) cancelAnimationFrame(rafId); });

// Reset animation when store settings change
watch(() => [store.cardWidth, store.cardGap, store.animationDuration], () => {
  offset.value = 0;
});

// ── Icon Components ──────────────────────────────────────────────────────────
const MailIcon = { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' }, [
  h('path', { d: 'M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z' }),
  h('polyline', { points: '22,6 12,13 2,6' }),
]) };

const MonitorIcon = { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' }, [
  h('rect', { x: 2, y: 3, width: 20, height: 14, rx: 2, ry: 2 }),
  h('line', { x1: 8, y1: 21, x2: 16, y2: 21 }),
  h('line', { x1: 12, y1: 17, x2: 12, y2: 21 }),
]) };

const UserIcon = { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' }, [
  h('path', { d: 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2' }),
  h('circle', { cx: 12, cy: 7, r: 4 }),
]) };

const CalendarIcon = { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' }, [
  h('rect', { x: 3, y: 4, width: 18, height: 18, rx: 2, ry: 2 }),
  h('line', { x1: 16, y1: 2, x2: 16, y2: 6 }),
  h('line', { x1: 8, y1: 2, x2: 8, y2: 6 }),
  h('line', { x1: 3, y1: 10, x2: 21, y2: 10 }),
]) };

const DollarIcon = { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' }, [
  h('line', { x1: 12, y1: 1, x2: 12, y2: 23 }),
  h('path', { d: 'M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6' }),
]) };

const ZapIcon = { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' }, [
  h('polygon', { points: '13 2 3 14 12 14 11 22 21 10 12 10 13 2' }),
]) };

const ShieldIcon = { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' }, [
  h('path', { d: 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z' }),
]) };

const StarIcon = { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' }, [
  h('polygon', { points: '12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2' }),
]) };

const iconMap = {
  mail: MailIcon,
  monitor: MonitorIcon,
  user: UserIcon,
  calendar: CalendarIcon,
  dollar: DollarIcon,
  zap: ZapIcon,
  shield: ShieldIcon,
  star: StarIcon,
};
</script>

<style scoped>
.feature-slider-wrapper {
  width: 100%;
  padding: 8px 0 4px;
}

.slider-container {
  position: relative;
  overflow: hidden;
  padding: 12px 0 6px;
}

.slider-track {
  display: flex;
  width: fit-content;
}

/* Cards */
.feature-card {
  flex-shrink: 0;
  border-radius: 16px;
  padding: 16px;
  position: relative;
  display: flex;
  align-items: center;
  gap: 14px;
  cursor: pointer;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
  overflow: hidden;
  border: 2px solid rgba(255, 255, 255, 0.12);
  min-height: 88px;
}

.feature-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 60%);
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
}

.feature-card:hover::before { opacity: 1; }
.feature-card:hover {
  transform: translateY(-4px) scale(1.02);
  box-shadow: 0 16px 40px rgba(0,0,0,0.2) !important;
}

/* Color variants */
.card-coral {
  background: linear-gradient(135deg, #e95a54 0%, #d6454a 100%);
  box-shadow: 0 4px 15px rgba(233, 90, 84, 0.3);
}
.card-navy {
  background: linear-gradient(135deg, #2a273c 0%, #1f1d2e 100%);
  box-shadow: 0 4px 15px rgba(42, 39, 60, 0.4);
}
.card-beige {
  background: linear-gradient(135deg, #f2f0eb 0%, #e8e4d9 100%);
  box-shadow: 0 4px 15px rgba(242, 240, 235, 0.3);
}
.card-green {
  background: linear-gradient(135deg, #8f9793 0%, #7a827e 100%);
  box-shadow: 0 4px 15px rgba(143, 151, 147, 0.3);
}
.card-peach {
  background: linear-gradient(135deg, #fbcdab 0%, #f5b890 100%);
  box-shadow: 0 4px 15px rgba(251, 205, 171, 0.3);
}
.card-purple {
  background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
  box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
}
.card-blue {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

/* Icon */
.card-icon {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.22);
  backdrop-filter: blur(8px);
  flex-shrink: 0;
  position: relative;
  z-index: 1;
  transition: transform 0.3s ease;
}
.feature-card:hover .card-icon { transform: scale(1.1) rotate(5deg); }

.card-svg {
  width: 26px;
  height: 26px;
}

/* Icon colours per card */
.card-coral .card-svg,
.card-beige .card-svg,
.card-peach .card-svg { color: #2a273c; }
.card-navy .card-svg,
.card-green .card-svg,
.card-purple .card-svg,
.card-blue .card-svg { color: #f2f0eb; }

/* Content */
.card-content { flex: 1; min-width: 0; position: relative; z-index: 1; }

.card-title {
  font-size: 13px;
  font-weight: 700;
  margin-bottom: 4px;
  line-height: 1.3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.card-coral .card-title,
.card-beige .card-title,
.card-peach .card-title { color: #2a273c; }
.card-navy .card-title,
.card-green .card-title,
.card-purple .card-title,
.card-blue .card-title { color: #f2f0eb; }

.card-description {
  font-size: 11px;
  line-height: 1.45;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  font-weight: 500;
}
.card-coral .card-description,
.card-beige .card-description,
.card-peach .card-description { color: #2a273c; opacity: 0.8; }
.card-navy .card-description,
.card-green .card-description,
.card-purple .card-description,
.card-blue .card-description { color: #f2f0eb; opacity: 0.8; }

/* Share arrow */
.share-icon {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 20px;
  height: 20px;
  opacity: 0.55;
  transition: all 0.3s ease;
  z-index: 1;
}
.card-coral .share-icon,
.card-beige .share-icon,
.card-peach .share-icon { stroke: #2a273c; }
.card-navy .share-icon,
.card-green .share-icon,
.card-purple .share-icon,
.card-blue .share-icon { stroke: #f2f0eb; }
.feature-card:hover .share-icon { opacity: 1; transform: scale(1.15); }

/* Nav buttons */
.nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: rgba(42, 39, 60, 0.88);
  border: 1.5px solid rgba(255,255,255,0.18);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.25s ease;
  z-index: 10;
  opacity: 0;
  color: #f2f0eb;
}
.slider-container:hover .nav-btn { opacity: 1; }
.nav-btn:hover { background: #e95a54; border-color: #e95a54; transform: translateY(-50%) scale(1.1); }
.nav-btn svg { width: 18px; height: 18px; }
.nav-btn.prev { left: 6px; }
.nav-btn.next { right: 6px; }

/* Progress */
.slider-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 2px;
  background: linear-gradient(90deg, #e95a54, #fbcdab, #8f9793);
  border-radius: 0 2px 2px 0;
  transition: width 0.1s linear;
  pointer-events: none;
}
</style>
