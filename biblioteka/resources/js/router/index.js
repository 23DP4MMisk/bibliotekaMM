import { createRouter, createWebHistory } from 'vue-router';
import Home from '../pages/Home.vue';
import LibraryPage from '../pages/Library.vue';
import LoginPage from '../pages/PieslLibrary.vue';
import RegisterPage from '../pages/RegisterPage.vue';
import BookView from '../pages/BookView.vue';

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
    },
    {
       path: '/register',
       name: 'Register',
       component: RegisterPage
    },
    {
        path: '/book/:isbn',
        name: 'BookView',
        component: BookView
    }

];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;