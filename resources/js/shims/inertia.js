import { h, watchEffect, defineComponent } from 'vue';
import { RouterLink } from 'vue-router';
import routerInstance from '../router/index';

export const Head = defineComponent({
  props: ['title'],
  setup(props) {
    watchEffect(() => {
      if (props.title) {
        document.title = props.title;
      }
    });
    return () => null;
  }
});

export const Link = defineComponent({
  props: ['href', 'method', 'as'],
  setup(props, { slots }) {
    // If it's a router link, map href to to
    return () => h(RouterLink, { to: props.href || '#' }, slots);
  }
});

export const useForm = (data) => {
    // Basic shim for useForm
    return {
        ...data,
        processing: false,
        errors: {},
        post: (url) => console.log('Mock POST to', url),
        put: (url) => console.log('Mock PUT to', url),
        delete: (url) => console.log('Mock DELETE to', url),
        reset: () => {},
    };
};

export const router = {
    visit: (url) => { window.location.href = url; },
    post: (url) => console.log('Mock router.post to', url),
    put: (url) => console.log('Mock router.put to', url),
    delete: (url) => console.log('Mock router.delete to', url),
    reload: () => window.location.reload(),
};

export const route = (name, params) => {
    try {
        const resolved = routerInstance.resolve({ name, params });
        return resolved.href;
    } catch (e) {
        return '#';
    }
};

export default { Head, Link, useForm, router, route };
