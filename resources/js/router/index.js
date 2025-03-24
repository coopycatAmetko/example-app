import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('../components/PostsList.vue')
    },
    {
        path: '/posts/:id',
        name: 'post',
        component: () => import('../components/PostDetail.vue'),
        props: true
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;