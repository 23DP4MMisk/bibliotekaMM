import { createApp } from 'vue'
import './bootstrap';
import App from './components/App.vue';
import router from './router';
import { vuetify } from './plugins/vuetify'
import '../css/library-pages.css';
import VueApexCharts from 'vue3-apexcharts'


const fontLink = document.createElement('link')
fontLink.href = 'https://fonts.googleapis.com/css2?family=Tenor+Sans&display=swap'
fontLink.rel = 'stylesheet'
document.head.appendChild(fontLink)
createApp(App).use(router).use(vuetify).use(VueApexCharts).mount('#app');
