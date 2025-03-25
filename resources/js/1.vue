<template>
    <div class="bg-gray-50 rounded-lg p-4 mb-4" :class="{ 'ml-2': isReply }">
        <div class="flex justify-between mb-2">
            <div>
                <span class="font-semibold">{{ comment.user_name }}</span>
                <span class="text-gray-500 ml-2">{{ formatDate(comment.created_at) }}</span>
            </div>
            <button @click="showReplyForm = !showReplyForm" class="text-blue-600 hover:text-blue-800">
                {{ showReplyForm ? 'Cancel Reply' : 'Reply' }}
            </button>
        </div>

        <div v-if="comment.email" class="text-sm text-gray-600 mb-2">
            Email: {{ comment.email }}
        </div>

        <div v-if="comment.homepage" class="text-sm text-gray-600 mb-2">
            Homepage: <a :href="comment.homepage" target="_blank" class="text-blue-600 hover:underline">{{ comment.homepage }}</a>
        </div>

        <div class="prose prose-sm max-w-none my-2" v-html="comment.text"></div>

        <!-- Files attached to the comment -->
        <div v-if="comment.files && comment.files.length > 0" class="mt-3">
            <div v-for="file in comment.files" :key="file.id" class="mt-2">
                <div v-if="isImage(file.type)">
                    <a :href="`/${file.path}`" data-lightbox="comment-gallery" class="block w-auto h-auto max-w-xs">
                        <img :src="`/${file.thumb_path || file.path}`" alt="Attached image" class="max-w-full h-auto rounded">
                    </a>
                </div>
                <div v-else-if="isTextFile(file.type)">
                    <a :href="`/${file.path}`" target="_blank" class="text-blue-600 hover:underline">
                        Download text file
                    </a>
                </div>
            </div>
        </div>

        <div v-if="loading" class="mt-4 text-gray-500">
            Loading replies...
        </div>

        <div v-if="error" class="mt-4 text-red-500">
            {{ error }}
        </div>

        <!-- Reply form -->
        <div v-if="showReplyForm" class="mt-4 p-4 bg-white rounded-lg border border-gray-200">
            <h3 class="text-lg font-semibold mb-2">Reply to {{ comment.user_name }}</h3>
            <comment-form :post-id="postId" :parent-id="comment.id" @comment-added="handleReplyAdded" />
        </div>

        <!-- Child comments (replies) -->
        <div v-if="children && children.data.length > 0" class="ml-2 mt-4">
            <comment-item v-for="child in children.data" :key="child.id" :comment="child" :post-id="postId"
                :is-reply="true" @comment-added="$emit('comment-added')" />
        </div>

        <div v-if="children.meta && children.meta.last_page > 1" class="flex justify-center mt-8">
            <button v-for="page in children.meta.last_page" :key="page" @click="fetchChildren(page)"
                class="mx-1 px-3 py-1 rounded"
                :class="{ 'bg-blue-600 text-white': page === children.meta.current_page, 'bg-gray-200': page !== children.meta.current_page }">
                {{ page }}
            </button>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import CommentForm from './CommentForm.vue';
import 'lightbox2/dist/css/lightbox.min.css';
import lightbox from 'lightbox2';

export default {
    name: 'CommentItem',

    components: {
        CommentForm
    },

    props: {
        comment: {
            type: Object,
            required: true
        },
        postId: {
            type: Number,
            required: true
        },
        isReply: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            showReplyForm: false,
            loading: false,
            error: '',
            children: {
                data: [],
                meta: {}
            }
        };
    },

    created() {
        if (this.comment.has_children) {
            this.fetchChildren();
        }
        window.Echo.channel(`comments.${this.comment.id}`)
            .listen('.new-comment', (e) => {
                console.log(2);
                const currentPage = (this.children.meta && this.children.meta.current_page) ? this.children.meta.current_page : 1;
                this.fetchChildren(currentPage);
            });
    },

    mounted() {
        this.$nextTick(() => {
            lightbox.init();
        });
    },

    methods: {
        async fetchChildren(page = 1) {
            this.loading = true;
            try {
                const response = await axios.post(`/api/comments/${this.comment.id}/get-children?page=${page}`);
                this.children = {
                    data: response.data.data,
                    meta: {
                        current_page: response.data.current_page,
                        last_page: response.data.last_page
                    }
                };
            } catch (error) {
                console.error('Error fetching comments:', error);
                this.error = 'Failed to load comments. Please try again.';
            } finally {
                this.loading = false;
            }
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        },

        isImage(type) {
            return ['image/jpeg', 'image/png', 'image/gif'].includes(type);
        },

        isTextFile(type) {
            return type === 'text/plain';
        },

        handleReplyAdded() {
            this.showReplyForm = false;
            this.fetchChildren();
        }
    }
};
</script>
