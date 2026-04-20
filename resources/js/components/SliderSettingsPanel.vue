<template>
  <div class="slider-settings space-y-8">

    <!-- ── Logo & Brand ─────────────────────────────────────── -->
    <section class="settings-section">
      <h3 class="section-heading">
        <span class="section-icon">🏷️</span> Logo & Brand
      </h3>
      <div class="settings-grid">
        <!-- Logo Text -->
        <div class="field-group">
          <label class="field-label">Brand Name</label>
          <input
            v-model="draft.logoText"
            type="text"
            class="field-input"
            placeholder="e.g. fandaqah"
          />
          <p class="field-hint">Displayed in the header when no logo image is set.</p>
        </div>

        <!-- Logo URL -->
        <div class="field-group">
          <label class="field-label">Logo Image URL</label>
          <div class="flex gap-2">
            <input
              v-model="draft.logoUrl"
              type="url"
              class="field-input flex-1"
              placeholder="https://…/logo.png"
            />
            <button v-if="draft.logoUrl" @click="draft.logoUrl = ''" class="btn-ghost text-xs px-3">✕</button>
          </div>
          <p v-if="draft.logoUrl" class="field-hint">
            <img :src="draft.logoUrl" alt="Preview" class="h-8 mt-1 rounded object-contain border border-slate-200 p-1 bg-white" />
          </p>
        </div>

        <!-- Show / Hide Slider toggle -->
        <div class="field-group col-span-2">
          <div class="toggle-row">
            <div>
              <span class="field-label">Show Feature Slider</span>
              <p class="field-hint">Toggle the auto-scrolling feature cards bar in the header.</p>
            </div>
            <button
              :class="['toggle-btn', draft.showFeatureSlider ? 'active' : '']"
              @click="draft.showFeatureSlider = !draft.showFeatureSlider"
              :aria-pressed="draft.showFeatureSlider"
            >
              <span class="toggle-knob" />
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Slider Behaviour ──────────────────────────────────── -->
    <section class="settings-section" v-show="draft.showFeatureSlider">
      <h3 class="section-heading">
        <span class="section-icon">⚡</span> Slider Behaviour
      </h3>
      <div class="settings-grid">
        <!-- Speed -->
        <div class="field-group">
          <label class="field-label">Scroll Speed (seconds / loop)</label>
          <div class="range-row">
            <input v-model.number="draft.animationDuration" type="range" min="5" max="120" step="5" class="range-input flex-1" />
            <span class="range-value">{{ draft.animationDuration }}s</span>
          </div>
          <p class="field-hint">Lower = faster. Recommended: 25–45 s.</p>
        </div>

        <!-- Card Width -->
        <div class="field-group">
          <label class="field-label">Card Width (px)</label>
          <div class="range-row">
            <input v-model.number="draft.cardWidth" type="range" min="200" max="480" step="10" class="range-input flex-1" />
            <span class="range-value">{{ draft.cardWidth }}px</span>
          </div>
        </div>

        <!-- Gap -->
        <div class="field-group">
          <label class="field-label">Card Gap (px)</label>
          <div class="range-row">
            <input v-model.number="draft.cardGap" type="range" min="8" max="60" step="4" class="range-input flex-1" />
            <span class="range-value">{{ draft.cardGap }}px</span>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Feature Cards ─────────────────────────────────────── -->
    <section class="settings-section" v-show="draft.showFeatureSlider">
      <div class="flex items-center justify-between mb-4">
        <h3 class="section-heading mb-0">
          <span class="section-icon">🃏</span> Feature Cards
        </h3>
        <button @click="addCard" class="btn-add">+ Add Card</button>
      </div>

      <div class="cards-list space-y-3">
        <div
          v-for="(card, idx) in draft.cards"
          :key="card.id"
          class="card-editor"
        >
          <!-- Drag handle  + colour dot -->
          <div class="card-editor-handle">
            <span class="card-number">{{ idx + 1 }}</span>
            <span :class="['color-dot', card.colorClass]" />
          </div>

          <!-- Fields -->
          <div class="card-editor-fields">
            <div class="settings-grid-3">
              <!-- Title -->
              <div class="field-group col-span-2 sm:col-span-1">
                <label class="field-label">Title</label>
                <input v-model="card.title" type="text" class="field-input" placeholder="Feature name" />
              </div>

              <!-- Color -->
              <div class="field-group">
                <label class="field-label">Color Theme</label>
                <select v-model="card.colorClass" class="field-input">
                  <option value="card-coral">🔴 Coral</option>
                  <option value="card-navy">🔵 Navy</option>
                  <option value="card-beige">🟤 Beige</option>
                  <option value="card-green">🟢 Green</option>
                  <option value="card-peach">🟠 Peach</option>
                  <option value="card-purple">🟣 Purple</option>
                  <option value="card-blue">💙 Blue</option>
                </select>
              </div>

              <!-- Icon -->
              <div class="field-group">
                <label class="field-label">Icon</label>
                <select v-model="card.iconType" class="field-input">
                  <option value="mail">✉️ Mail</option>
                  <option value="monitor">🖥️ Monitor</option>
                  <option value="user">👤 User</option>
                  <option value="calendar">📅 Calendar</option>
                  <option value="dollar">💵 Dollar</option>
                  <option value="zap">⚡ Zap</option>
                  <option value="shield">🛡️ Shield</option>
                  <option value="star">⭐ Star</option>
                </select>
              </div>

              <!-- Description -->
              <div class="field-group col-span-3">
                <label class="field-label">Description</label>
                <input v-model="card.description" type="text" class="field-input" placeholder="Short description…" />
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="card-editor-actions">
            <button :disabled="idx === 0" @click="move(idx, idx - 1)" class="icon-btn" title="Move up">↑</button>
            <button :disabled="idx === draft.cards.length - 1" @click="move(idx, idx + 1)" class="icon-btn" title="Move down">↓</button>
            <button @click="removeCard(idx)" class="icon-btn danger" title="Remove">✕</button>
          </div>
        </div>

        <div v-if="draft.cards.length === 0" class="empty-state">
          No cards yet. Click "Add Card" to start.
        </div>
      </div>
    </section>

    <!-- ── Actions ───────────────────────────────────────────── -->
    <div class="actions-bar">
      <button @click="reset" class="btn-outline">Reset Defaults</button>
      <button @click="save" class="btn-primary">Save Settings</button>
    </div>

    <!-- Toast -->
    <Transition name="toast">
      <div v-if="saved" class="toast">✅ Settings saved!</div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, watch, reactive } from 'vue';
import { useSliderStore } from '../stores/slider';

const store = useSliderStore();

// Local draft — we only commit to store on Save
function makeDraft() {
  return reactive({
    logoText: store.logoText,
    logoUrl: store.logoUrl,
    showFeatureSlider: store.showFeatureSlider,
    animationDuration: store.animationDuration,
    cardWidth: store.cardWidth,
    cardGap: store.cardGap,
    cards: store.cards.map(c => ({ ...c })),
  });
}

const draft = ref(makeDraft());
const saved = ref(false);

function save() {
  store.updateLogo({ logoText: draft.value.logoText, logoUrl: draft.value.logoUrl });
  store.toggleSlider(draft.value.showFeatureSlider);
  store.updateSliderSettings({
    animationDuration: draft.value.animationDuration,
    cardWidth: draft.value.cardWidth,
    cardGap: draft.value.cardGap,
  });
  // Sync cards
  store.cards = draft.value.cards.map(c => ({ ...c }));
  store.persist();

  saved.value = true;
  setTimeout(() => (saved.value = false), 2500);
}

function reset() {
  store.resetToDefaults();
  draft.value = makeDraft();
}

function addCard() {
  draft.value.cards.push({
    id: Date.now(),
    title: 'New Feature',
    description: 'Describe this feature here',
    colorClass: 'card-coral',
    iconType: 'zap',
  });
}

function removeCard(idx) {
  draft.value.cards.splice(idx, 1);
}

function move(from, to) {
  const arr = draft.value.cards;
  const [item] = arr.splice(from, 1);
  arr.splice(to, 0, item);
}
</script>

<style scoped>
/* ── Layout ── */
.settings-section {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 24px;
}

.section-heading {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.section-icon { font-size: 18px; }

.settings-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.settings-grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

@media (max-width: 640px) {
  .settings-grid { grid-template-columns: 1fr; }
  .settings-grid-3 { grid-template-columns: 1fr; }
}

/* ── Fields ── */
.field-group { display: flex; flex-direction: column; gap: 6px; }
.col-span-2 { grid-column: span 2; }
.col-span-3 { grid-column: span 3; }

.field-label { font-size: 12px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.04em; }
.field-hint { font-size: 11px; color: #94a3b8; margin-top: 2px; }

.field-input {
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  padding: 8px 12px;
  font-size: 13px;
  color: #1e293b;
  background: #f8fafc;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  width: 100%;
}
.field-input:focus {
  border-color: #e95a54;
  box-shadow: 0 0 0 3px rgba(233,90,84,0.12);
  background: #fff;
}

/* ── Toggle ── */
.toggle-row { display: flex; align-items: center; justify-content: space-between; gap: 16px; }

.toggle-btn {
  position: relative;
  width: 46px;
  height: 26px;
  border-radius: 999px;
  background: #cbd5e1;
  border: none;
  cursor: pointer;
  transition: background 0.25s;
  flex-shrink: 0;
}
.toggle-btn.active { background: #e95a54; }
.toggle-knob {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 1px 4px rgba(0,0,0,0.18);
  transition: transform 0.25s;
}
.toggle-btn.active .toggle-knob { transform: translateX(20px); }

/* ── Range ── */
.range-row { display: flex; align-items: center; gap: 12px; }
.range-input { accent-color: #e95a54; height: 4px; cursor: pointer; }
.range-value { font-size: 13px; font-weight: 700; color: #e95a54; min-width: 40px; text-align: right; }

/* ── Cards list ── */
.card-editor {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  padding: 14px;
  transition: border-color 0.2s;
}
.card-editor:hover { border-color: #e95a54; }

.card-editor-handle {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding-top: 4px;
}
.card-number { font-size: 11px; font-weight: 700; color: #94a3b8; }
.color-dot {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  flex-shrink: 0;
}
.color-dot.card-coral  { background: #e95a54; }
.color-dot.card-navy   { background: #2a273c; }
.color-dot.card-beige  { background: #e8e4d9; border: 1px solid #ccc; }
.color-dot.card-green  { background: #8f9793; }
.color-dot.card-peach  { background: #fbcdab; border: 1px solid #e0b090; }
.color-dot.card-purple { background: #7c3aed; }
.color-dot.card-blue   { background: #3b82f6; }

.card-editor-fields { flex: 1; min-width: 0; }

.card-editor-actions {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding-top: 4px;
}

.icon-btn {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  border: 1.5px solid #e2e8f0;
  background: #fff;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  color: #64748b;
}
.icon-btn:hover:not(:disabled) { border-color: #e95a54; color: #e95a54; }
.icon-btn.danger:hover:not(:disabled) { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
.icon-btn:disabled { opacity: 0.3; cursor: not-allowed; }

.empty-state {
  text-align: center;
  padding: 32px;
  color: #94a3b8;
  font-size: 13px;
  border: 2px dashed #e2e8f0;
  border-radius: 14px;
}

/* ── Actions bar ── */
.actions-bar {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

/* ── Buttons ── (re-use app globals via Tailwind, add fallbacks) ── */
.btn-add {
  font-size: 12px;
  font-weight: 700;
  padding: 7px 14px;
  border-radius: 10px;
  background: linear-gradient(135deg, #e95a54, #d6454a);
  color: #fff;
  border: none;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}
.btn-add:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(233,90,84,0.35); }

.btn-ghost {
  background: transparent;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}
.btn-ghost:hover { border-color: #e95a54; color: #e95a54; }

/* ── Toast ── */
.toast {
  position: fixed;
  bottom: 32px;
  right: 32px;
  background: #1e293b;
  color: #fff;
  padding: 12px 20px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
  box-shadow: 0 8px 24px rgba(0,0,0,0.25);
  z-index: 9999;
}
.toast-enter-active, .toast-leave-active { transition: all 0.35s cubic-bezier(0.4,0,0.2,1); }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(16px); }
</style>
