import { defineComponent, h } from 'vue';

export const draggable = defineComponent({
  name: 'draggable',
  props: ['modelValue', 'itemKey'],
  emits: ['update:modelValue'],
  setup(props, { slots }) {
    return () => h('div', {}, slots.item ? props.modelValue.map((element, index) => slots.item({ element, index })) : slots.default());
  }
});

export default draggable;
