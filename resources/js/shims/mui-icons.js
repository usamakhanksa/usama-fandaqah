import { defineComponent, h } from 'vue';

const DummyIcon = defineComponent({
  setup() {
    return () => h('span', { class: 'dummy-icon' }, '★');
  }
});

const icons = new Proxy({}, {
  get: (target, prop) => {
    return DummyIcon;
  }
});

export const LocalOffer = DummyIcon;
export const Add = DummyIcon;
export const Edit = DummyIcon;
export const Delete = DummyIcon;
export const Visibility = DummyIcon;
export const ToggleOn = DummyIcon;
export const ToggleOff = DummyIcon;
export const Calculate = DummyIcon;
export const EventNote = DummyIcon;
export const CompareArrows = DummyIcon;
export const Receipt = DummyIcon;
export const Percent = DummyIcon;
export const LocalTax = DummyIcon;
export const ConfirmationNumber = DummyIcon;
export const ContentCopy = DummyIcon;
export const CheckCircle = DummyIcon;
export const Cancel = DummyIcon;

export default icons;
