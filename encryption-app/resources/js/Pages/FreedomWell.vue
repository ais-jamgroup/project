<script setup>
import { ref } from 'vue';
import CryptoJS from 'crypto-js';
import '../../css/freedomwell.css'
import rawSvg from '../../../public/images/noun-well-80360.svg?raw';

const posts = ref([]);
const newPost = ref('');
const encryptionKey = ref('');
const isEncrypted = ref(false);
const svgContent = ref(rawSvg);

// Add a new post
function addPost() {
    if (isEncrypted.value && encryptionKey.value) {
        const encryptedPost = CryptoJS.AES.encrypt(newPost.value, encryptionKey.value).toString();
        posts.value.unshift({ content: encryptedPost, encrypted: true });
    } else {
        posts.value.unshift({ content: newPost.value, encrypted: false });
    }
    newPost.value = '';
}

// Decrypt post
function decryptPost(content, key) {
    try {
        const bytes = CryptoJS.AES.decrypt(content, key);
        return bytes.toString(CryptoJS.enc.Utf8);
    } catch {
        return 'Invalid Key';
    }
}
</script>

<template>
    <div class="freedom-well">
        <!-- Post Input Section -->
        <div class="post-input-container">
            <div class="post-input">
                <h1 class="post-title">Freedom Well</h1>
                <textarea
                    v-model="newPost"
                    placeholder="Share your thoughts..."
                    class="textarea"
                ></textarea>
                <div class="input-actions">
                    <input
                        type="text"
                        v-model="encryptionKey"
                        placeholder="Encryption Key (optional)"
                        class="input-key"
                    />
                    <label class="encrypt-label">
                        <input
                            type="checkbox"
                            v-model="isEncrypted"
                            class="checkbox-encrypt"
                        />
                        <span>Encrypt</span>
                    </label>
                    <button
                        @click="addPost"
                        class="btn-post"
                    >
                        Post
                    </button>
                </div>
            </div>
            
            <!-- Engaging Icon and Message -->
            <div class="freedom-well-message">
                <div class="freedom-icon" v-html="svgContent"></div>
                <div class="freedom-text">
                    Use Freedom Well to share your thoughts freely and securely.
                </div>
            </div>
        </div>

         <!-- Posts Section -->
        <div class="posts-container">
            <div class="posts">
                <div
                    v-for="(post, index) in posts"
                    :key="index"
                    class="post"
                >
                    <p v-if="post.encrypted" class="post-content">
                        <strong>Encrypted:</strong> {{ post.content }}
                    </p>
                    <p v-else class="post-content">
                        {{ post.content }}
                    </p>
                    <div v-if="post.encrypted" class="decrypt-section">
                        <input
                            type="text"
                            v-model="encryptionKey"
                            placeholder="Enter key to decrypt"
                            class="input-key"
                        />
                        <p class="decrypted-content">
                            <strong>Decrypted:</strong>
                            {{ decryptPost(post.content, encryptionKey) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
