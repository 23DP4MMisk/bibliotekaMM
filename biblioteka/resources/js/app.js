import { createApp } from 'vue'
import './bootstrap';
import App from './components/App.vue';
import router from './router';
import { vuetify } from './plugins/vuetify'


createApp(App).use(router).use(vuetify).mount('#app');
