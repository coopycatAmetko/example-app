import { createApp } from 'vue/dist/vue.esm-bundler.js';
import './bootstrap';
import VueComponent from './components/VueComponent.vue';

const app = createApp({
});

app.component('example-component', VueComponent);

app.mount('#app');