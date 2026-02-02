import { createRouter, createWebHistory } from 'vue-router';
import Home from '../pages/Home.vue';
import LibraryPage from '../pages/Library.vue';
import LoginPage from '../pages/PieslLibrary.vue'

const routes = [
    { 
        path: '/',
        name: 'Home', 
        component: Home 
    },
    {
       path: '/library',
       name: 'Library',
       component: LibraryPage 
    },
     {
       path: '/login',
       name: 'PieslLibrary',
       component: LoginPage 
    }

];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;