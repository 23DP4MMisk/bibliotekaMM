import { createApp } from 'vue'
import './bootstrap';
import App from './components/App.vue';
import router from './router';
import { vuetify } from './plugins/vuetify'

const fontLink = document.createElement('link')
fontLink.href = 'https://fonts.googleapis.com/css2?family=Tenor+Sans&display=swap'
fontLink.rel = 'stylesheet'
document.head.appendChild(fontLink)
createApp(App).use(router).use(vuetify).mount('#app');
