import { createRouter, createWebHistory } from 'vue-router';
import Home from '../pages/Home.vue';
import LibraryPage from '../pages/Library.vue';

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
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;