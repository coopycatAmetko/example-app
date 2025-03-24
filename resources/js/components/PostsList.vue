<template>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Posts</h1>

        <div v-if="loading" class="text-center py-4">
            <p>Loading posts...</p>
        </div>

        <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <p>{{ error }}</p>
        </div>

        <div v-else>
            <div v-if="posts.data && posts.data.length > 0" class="grid gap-6">
                <div v-for="post in posts.data" :key="post.id" class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-2">
                        <router-link :to="{ name: 'post', params: { id: post.id } }"
                            class="text-blue-600 hover:text-blue-800">
                            {{ post.title }}
                        </router-link>
                    </h2>
                    <p class="text-gray-700">{{ post.text.substring(0, 200) }}{{ post.text.length > 200 ? '...' : '' }}
                    </p>
                    <div class="mt-4">
                        <router-link :to="{ name: 'post', params: { id: post.id } }"
                            class="text-blue-600 hover:text-blue-800">
                            View Comments
                        </router-link>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-4">
                <p>No posts found.</p>
            </div>

            <div v-if="posts.meta && posts.meta.last_page > 1" class="flex justify-center mt-8">
                <button v-for="page in posts.meta.last_page" :key="page" @click="fetchPosts(page)"
                    class="mx-1 px-3 py-1 rounded"
                    :class="{ 'bg-blue-600 text-white': page === posts.meta.current_page, 'bg-gray-200': page !== posts.meta.current_page }">
                    {{ page }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            posts: {},
            loading: true,
            error: null
        };
    },

    created() {
        this.fetchPosts();
    },

    methods: {
        async fetchPosts(page = 1) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(`/api/posts?page=${page}`);
                this.posts = {
                    data: response.data.data,
                    meta: {
                        current_page: response.data.current_page,
                        last_page: response.data.last_page
                    }
                };
            } catch (error) {
                console.error('Error fetching posts:', error);
                this.error = 'Failed to load posts. Please try again.';
            } finally {
                this.loading = false;
            }
        }
    }
};
</script>
