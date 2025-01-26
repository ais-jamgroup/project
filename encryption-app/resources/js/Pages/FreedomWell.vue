<script setup>
import { ref } from 'vue';
import CryptoJS from 'crypto-js';
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
            <div class="post-input bg-[#d2fdff]">
                <h1 class="text-xl font-bold mb-4 text-[#303c6c] text-center">Freedom Well</h1>
                <textarea
                    v-model="newPost"
                    placeholder="Share your thoughts..."
                    class="w-full p-3 border border-[#303c6c] rounded-md focus:ring-2 focus:ring-[#b4dfe5] focus:outline-none"
                ></textarea>
                <div class="flex items-center mt-4 space-x-4">
                    <input
                        type="text"
                        v-model="encryptionKey"
                        placeholder="Encryption Key (optional)"
                        class="p-3 border border-[#303c6c] rounded-md flex-1 focus:ring-2 focus:ring-[#b4dfe5] focus:outline-none"
                    />
                    <label class="flex items-center space-x-2 text-[#303c6c]">
                        <input
                            type="checkbox"
                            v-model="isEncrypted"
                            class="form-checkbox h-5 w-5 text-[#f4976c] border-[#303c6c]"
                        />
                        <span>Encrypt</span>
                    </label>
                    <button
                        @click="addPost"
                        class="px-6 py-2 bg-[#f4976c] text-white font-semibold rounded-md shadow-md hover:bg-[#e0865c] transition-all"
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
                    class="post bg-[#d2fdff]"
                >
                    <p v-if="post.encrypted" class="text-[#303c6c] font-medium">
                        <strong>Encrypted:</strong> {{ post.content }}
                    </p>
                    <p v-else class="text-[#303c6c] font-medium">
                        {{ post.content }}
                    </p>
                    <div v-if="post.encrypted" class="mt-4">
                        <input
                            type="text"
                            v-model="encryptionKey"
                            placeholder="Enter key to decrypt"
                            class="p-3 border border-[#303c6c] rounded-md w-full focus:ring-2 focus:ring-[#b4dfe5] focus:outline-none"
                        />
                        <p class="mt-3 text-[#303c6c] font-medium">
                            <strong>Decrypted:</strong>
                            {{ decryptPost(post.content, encryptionKey) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.freedom-well {
    display: grid;
    grid-template-columns: auto 1fr;
    width: 100%;
    height: 100vh;
    background: #fbe8a6;
    overflow: hidden;
}

.sidebar {
    width: 250px;
    background: #303c6c;
    transition: width 0.3s ease;
    color: white;
}

.sidebar.collapsed {
    width: 80px;
}

.content {
    padding: 1rem;
    flex-direction: column;
    gap: 1rem;
}

.post-input {
    position: sticky;
    top: 1rem;
    background: #d2fdff;
    padding: 2rem 2rem;
    max-width: 800px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    display: flex;
    flex-direction: column;
    margin-top: 1rem;
    overflow: hidden;
}

.posts {
    flex: 1;
    padding: 0.5rem;
    max-height: calc(100vh - 50px);
    overflow-y: auto;
    margin-top: 1rem;
    box-sizing: border-box;
}

.posts .post {
    background: #d2fdff;
    border: 1px solid #b4dfe5;
    margin-bottom: 0.5rem;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

textarea {
    resize: none;
    color: #303c6c;
    min-height: 120px;
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 0.8rem;
    box-sizing: border-box;
    background: #fff;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

textarea:focus {
    border-color: #f4976c;
    background-color: #ffffff;
    box-shadow: 0 0 5px rgba(244, 151, 108, 0.5);
    outline: none;
}

input {
    color: #303c6c;
    padding: 0.8rem;
    width: 70%;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-sizing: border-box;
    background: #fff;
    transition: all 0.3s ease;
}

input:focus {
    border-color: #f4976c;
    background-color: #ffffff;
    box-shadow: 0 0 5px rgba(244, 151, 108, 0.5);
    outline: none;
}

label {
    color: #303c6c;
    display: flex;
    align-items: center;
}

input[type="checkbox"] {
    accent-color: #f4976c;
    width: 20px;
    height: 20px;
    cursor: pointer;
}

button {
    padding: 0.8rem 1.5rem;
    background: #f4976c;
    color: white;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

button:hover {
    background-color: #f08055;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

::-webkit-scrollbar {
    width: 2px;
    height: 6px;
}

::-webkit-scrollbar-thumb {
    background-color: #f4976c;
    border-radius: 50px;
}

::-webkit-scrollbar-thumb:hover {
    background-color: #e0865c;
}

::-webkit-scrollbar-track {
    background-color: #d2fdff;
    border-radius: 10px;
}

* {
    scrollbar-width: thin;
    scrollbar-color: #f4976c #d2fdff;
}

.freedom-well-message {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    margin-top: 1rem;
    margin-left: auto;
    margin-right: auto;
    position: relative;
    text-align: center;
}

.freedom-icon-container {
    position: relative;
    display: inline-block;
    width: 120px;
    height: 120px;
}

.freedom-icon {
    width: 60%;
    height: 60%;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 1;
    fill: #f4976c;
}

.freedom-text {
    position: absolute;
    top: 50%;
    left: 63%;
    transform: translate(-15%, -15%);
    z-index: 2;
    font-size: 1.3rem;
    color: #303c6c;
    font-weight: bold;
    text-align: center;
    padding: 2rem;
}
</style>
