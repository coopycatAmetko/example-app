<template>
    <div class="container mx-auto px-4 py-8">
        <div v-if="loading" class="text-center py-4">
            <p>Loading post...</p>
        </div>

        <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <p>{{ error }}</p>
        </div>

        <div v-else>
            <!-- Post Details -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h1 class="text-3xl font-bold mb-4">{{ post.title }}</h1>
                <div class="text-gray-700 mb-6">{{ post.text }}</div>
                <div class="text-sm text-gray-500">Posted on: {{ new Date(post.created_at).toLocaleDateString() }}</div>
            </div>

            <!-- Comments Section -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                <h2 class="text-2xl font-bold mb-4">Comments</h2>

                <div class="flex flex-wrap items-center mb-6">
                    <span class="mr-2">Sort by:</span>
                    <div class="flex space-x-4">
                        <button @click="sortComments('user_name')" class="text-blue-600 hover:text-blue-800">
                            User Name
                            <span v-if="sortField === 'user_name'">
                                {{ sortOrder === 'asc' ? '↑' : '↓' }}
                            </span>
                        </button>
                        <button @click="sortComments('email')" class="text-blue-600 hover:text-blue-800">
                            Email
                            <span v-if="sortField === 'email'">
                                {{ sortOrder === 'asc' ? '↑' : '↓' }}
                            </span>
                        </button>
                        <button @click="sortComments('created_at')" class="text-blue-600 hover:text-blue-800">
                            Date
                            <span v-if="sortField === 'created_at'">
                                {{ sortOrder === 'asc' ? '↑' : '↓' }}
                            </span>
                        </button>
                    </div>
                </div>

                <div v-if="comments.data && comments.data.length > 0">
                    <div v-for="comment in comments.data" :key="comment.id" class="mb-6">
                        <comment-item :comment="comment" :post-id="postId" @comment-added="fetchComments" />
                    </div>

                    <div v-if="comments.meta && comments.meta.last_page > 1" class="flex justify-center mt-8">
                        <button v-for="page in comments.meta.last_page" :key="page" @click="fetchComments(page)"
                            class="mx-1 px-3 py-1 rounded"
                            :class="{ 'bg-blue-600 text-white': page === comments.meta.current_page, 'bg-gray-200': page !== comments.meta.current_page }">
                            {{ page }}
                        </button>
                    </div>
                </div>

                <div v-else class="text-center py-4">
                    <p>No comments yet. Be the first to comment!</p>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Add a Comment</h2>
                <comment-form :post-id="postId" @comment-added="fetchComments" />
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import CommentItem from './CommentItem.vue';
import CommentForm from './CommentForm.vue';

export default {
    components: {
        CommentItem,
        CommentForm
    },

    props: {
        id: {
            type: [Number, String],
            required: true
        }
    },

    data() {
        return {
            postId: parseInt(this.id),
            post: {},
            comments: {},
            loading: true,
            error: null,
            sortField: 'created_at',
            sortOrder: 'desc'
        };
    },

    created() {
        this.fetchPost();
        this.fetchComments();
        console.log(`posts.${this.postId}`);
        window.Echo.channel(`posts.${this.postId}`)
            .listen('.new-comment', (e) => {
                console.log(1);
                const currentPage = (this.comments.meta && this.comments.meta.current_page) ? this.comments.meta.current_page : 1;
                this.fetchComments(currentPage);
            });
    },

    methods: {
        async fetchPost() {
            try {
                const response = await axios.get(`/api/posts/${this.postId}`);
                this.post = response.data;
            } catch (error) {
                console.error('Error fetching post:', error);
                this.error = 'Failed to load post. Please try again.';
            } finally {
                this.loading = false;
            }
        },

        async fetchComments(page = 1) {
            try {
                const response = await axios.get(`/api/posts/${this.postId}/comments`, {
                    params: {
                        page,
                        sort_field: this.sortField,
                        sort_order: this.sortOrder
                    }
                });
                this.comments = {
                    data: response.data.data,
                    meta: {
                        current_page: response.data.current_page,
                        last_page: response.data.last_page
                    }
                };
            } catch (error) {
                console.error('Error fetching comments:', error);
                this.error = 'Failed to load comments. Please try again.';
            }
        },

        sortComments(field) {
            if (this.sortField === field) {
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortOrder = 'desc';
            }
            this.fetchComments();
        }
    }
};
</script>
