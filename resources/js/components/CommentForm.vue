<template>
    <form @submit.prevent="submitForm" class="space-y-4">
        <input type="hidden" id='_token' name="_token" :value="form._token">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="user_name" class="block text-sm font-medium text-gray-700 mb-1">User Name *</label>
                <input id="user_name" v-model="form.user_name" type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.user_name }">
                <div v-if="errors.user_name" class="text-red-500 text-sm mt-1">{{ errors.user_name[0] }}</div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input id="email" v-model="form.email" type="email"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.email }">
                <div v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email[0] }}</div>
            </div>
        </div>

        <div>
            <label for="homepage" class="block text-sm font-medium text-gray-700 mb-1">Homepage</label>
            <input id="homepage" v-model="form.homepage" type="url"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.homepage }">
            <div v-if="errors.homepage" class="text-red-500 text-sm mt-1">{{ errors.homepage[0] }}</div>
        </div>

        <div>
            <label for="text" class="block text-sm font-medium text-gray-700 mb-1">Comment *</label>
            <div class="mb-2 flex space-x-2">
                <button type="button" @click="insertTag('strong')"
                    class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300" title="Bold">
                    [strong]
                </button>
                <button type="button" @click="insertTag('i')" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300"
                    title="Italic">
                    [i]
                </button>
                <button type="button" @click="insertTag('code')" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300"
                    title="Code">
                    [code]
                </button>
                <button type="button" @click="insertLink()" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300"
                    title="Link">
                    [a]
                </button>
            </div>
            <textarea id="text" v-model="form.text" rows="5"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.text }" ref="textArea"></textarea>
            <div v-if="errors.text" class="text-red-500 text-sm mt-1">{{ errors.text[0] }}</div>
        </div>

        <div>
            <label for="file" class="block text-sm font-medium text-gray-700 mb-1">Attachment (Image or Text
                File)</label>
            <input id="file" type="file" @change="handleFileChange"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.file }" accept=".jpg,.jpeg,.png,.gif,.txt">
            <div class="text-xs text-gray-500 mt-1">
                Allowed file types: JPG, PNG, GIF (max 320x240px), TXT (max 100kb)
            </div>
            <div v-if="errors.file" class="text-red-500 text-sm mt-1">{{ errors.file[0] }}</div>
        </div>

        <div>
            <label for="captcha" class="block text-sm font-medium text-gray-700 mb-1">CAPTCHA *</label>
            <div class="flex space-x-2 items-center">
                <img v-if="captchaUrl" :src="captchaUrl" alt="CAPTCHA" class="h-10" @click="refreshCaptcha">
                <span v-else>Loading CAPTCHA...</span>
                <input id="captcha" v-model="form.captcha" type="text"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.captcha }">
            </div>
            <div v-if="errors.captcha" class="text-red-500 text-sm mt-1">{{ errors.captcha[0] }}</div>
        </div>

        <div v-if="showPreview" class="p-4 bg-gray-50 rounded-lg border border-gray-200 mt-4">
            <h3 class="text-lg font-semibold mb-2">Comment Preview</h3>
            <div class="prose prose-sm max-w-none" v-html="form.text"></div>
        </div>

        <div class="flex space-x-4">
            <button type="button" @click="togglePreview"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                {{ showPreview ? 'Hide Preview' : 'Preview' }}
            </button>
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                :disabled="submitting">
                {{ submitting ? 'Submitting...' : 'Post Comment' }}
            </button>
        </div>
    </form>
</template>

<script>
import axios from 'axios';

export default {
    props: {
        postId: {
            type: Number,
            required: true
        },
        parentId: {
            type: Number,
            default: null
        }
    },

    data() {
        return {
            form: {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                user_name: '',
                email: '',
                homepage: '',
                text: '',
                captcha: '',
                file: null
            },
            errors: {},
            submitting: false,
            showPreview: false,
            captchaUrl: '',
            captchaKey: ''
        };
    },

    mounted() {
        this.refreshCaptcha();
    },

    methods: {
        async submitForm() {
            if (!this.validateForm()) {
                return;
            }

            this.submitting = true;
            this.errors = {};

            const formData = new FormData();
            formData.append('_token', this.form._token);
            formData.append('user_name', this.form.user_name);
            formData.append('email', this.form.email);

            if (this.form.homepage) {
                formData.append('homepage', this.form.homepage);
            }

            formData.append('text', this.form.text);
            formData.append('captcha', this.form.captcha);
            formData.append('captcha_key', this.captchaKey);

            if (this.form.file) {
                formData.append('file', this.form.file);
            }

            try {
                let response;

                if (this.parentId) {
                    // If this is a reply to another comment
                    response = await axios.post(`/api/comments/${this.parentId}/reply`, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    });
                } else {
                    // If this is a new comment on the post
                    response = await axios.post(`/api/posts/${this.postId}/comments`, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    });
                }

                this.resetForm();

                // Emit event to update comments list
                this.$emit('comment-added');
            } catch (error) {
                if (error.response && error.response.data && error.response.data.errors) {
                    this.errors = error.response.data.errors;
                } else {
                    console.error('Error submitting comment:', error);
                    alert('An error occurred while submitting your comment. Please try again.');
                }
            } finally {
                this.submitting = false;
                this.refreshCaptcha();
            }
        },

        resetForm() {
            this.form = {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                user_name: '',
                email: '',
                homepage: '',
                text: '',
                captcha: '',
                file: null
            };
            this.showPreview = false;

            const fileInput = document.getElementById('file');
            if (fileInput) {
                fileInput.value = '';
            }
        },

        handleFileChange(e) {
            const file = e.target.files[0];
            if (!file) return;

            const allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            const allowedTextTypes = ['text/plain'];

            if (allowedImageTypes.includes(file.type) || allowedTextTypes.includes(file.type)) {
                if (file.type === 'text/plain' && file.size > 100 * 1024) {
                    alert('Text files must be less than 100KB.');
                    e.target.value = '';
                    return;
                }

                this.form.file = file;
            } else {
                alert('Only JPG, PNG, GIF or TXT files are allowed.');
                e.target.value = '';
            }
        },

        insertTag(tag) {
            const textarea = this.$refs.textArea;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = this.form.text;

            const selectedText = text.substring(start, end);
            const replacement = `<${tag}>${selectedText}</${tag}>`;

            this.form.text = text.substring(0, start) + replacement + text.substring(end);

            // Set cursor position after the inserted tag
            this.$nextTick(() => {
                textarea.focus();
                const newCursorPos = start + replacement.length;
                textarea.setSelectionRange(newCursorPos, newCursorPos);
            });
        },

        insertLink() {
            const textarea = this.$refs.textArea;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = this.form.text;

            const selectedText = text.substring(start, end);
            const url = prompt('Enter the URL:', 'http://');

            if (url) {
                const title = prompt('Enter the title (optional):', '');
                const titleAttr = title ? ` title="${title}"` : '';
                const replacement = `<a href="${url}"${titleAttr}>${selectedText || url}</a>`;

                this.form.text = text.substring(0, start) + replacement + text.substring(end);

                // Set cursor position after the inserted tag
                this.$nextTick(() => {
                    textarea.focus();
                    const newCursorPos = start + replacement.length;
                    textarea.setSelectionRange(newCursorPos, newCursorPos);
                });
            }
        },

        async refreshCaptcha() {
            try {
                const response = await axios.get('/captcha/api/default');
                this.captchaUrl = response.data.image;
                this.captchaKey = response.data.code;
            } catch (error) {
                console.error('Error loading captcha:', error);
            }
        },

        togglePreview() {
            this.showPreview = !this.showPreview;
        },

        validateForm() {
            const errors = {};
            const { user_name, email, homepage, text, captcha, file } = this.form;

            // Validate user_nam
            if (!user_name.trim()) {
                errors.user_name = ['The user name field is required.'];
            } else if (!/^[a-z0-9]+$/i.test(user_name)) {
                errors.user_name = ['The user name may only contain letters and numbers.'];
            }

            // Validate email
            if (!email.trim()) {
                errors.email = ['The email field is required.'];
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errors.email = ['The email must be a valid email address.'];
            }

            // Validate homepage
            if (homepage && !/^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*\/?$/.test(homepage)) {
                errors.homepage = ['The homepage format is invalid.'];
            }

            // Validate text
            if (!text.trim()) {
                errors.text = ['The text field is required.'];
            }

            // Validate captcha
            if (!captcha.trim()) {
                errors.captcha = ['The captcha field is required.'];
            }

            // Validate file
            if (file) {
                const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'txt'];
                const extension = file.name.split('.').pop().toLowerCase();
                
                if (!allowedExtensions.includes(extension)) {
                    errors.file = ['Allowed file types: jpg, jpeg, png, gif, txt.'];
                }
                
                if (extension === 'txt' && file.size > 102400) {
                    errors.file = ['The txt file must not be greater than 100KB.'];
                }
            }

            this.errors = errors;
            return Object.keys(errors).length === 0;
        },

        handleFileChange(e) {
            const file = e.target.files[0];
            if (!file) return;

            const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'txt'];
            const extension = file.name.split('.').pop().toLowerCase();

            if (!allowedExtensions.includes(extension)) {
                alert('Only JPG, PNG, GIF or TXT files are allowed.');
                e.target.value = '';
                return;
            }

            if (extension === 'txt' && file.size > 100 * 1024) {
                alert('Text files must be less than 100KB.');
                e.target.value = '';
                return;
            }

            this.form.file = file;
        },
    }
};
</script>
