<script setup>
import { ref } from 'vue';
import axios from 'axios';
import '../../css/freedomwell.css';
import rawSvg from '../../../public/images/noun-well-80360.svg?raw';

const posts = ref([]);
const newPost = ref('');
const postingEncryptionKey = ref(''); // Key for the Freedom Well posting input
const isEncrypted = ref(false);
const svgContent = ref(rawSvg);

// Fetch all posts from the server
async function fetchPosts() {
    const response = await axios.get('/freedomwell/posts');
    posts.value = response.data.map((post) => ({
        ...post,
        showDecryptionField: false, // Add a flag to toggle decryption input field
        decryptionKey: '', // Add a unique decryption key for each post
        decryptedContent: '', // Temporarily hold decrypted content for each post
    }));
}

// Add a new post
async function addPost() {
    let encryptedPost = newPost.value;

    // Encrypt only when encryption is enabled
    if (isEncrypted.value && postingEncryptionKey.value) {
        encryptedPost = advancedAtbashEncrypt(newPost.value);
    }

    try {
        await axios.post('/freedomwell/posts', {
            content: encryptedPost,
            encrypted: isEncrypted.value,
            encryption_key: isEncrypted.value ? postingEncryptionKey.value : null,
        });

        // Clear the input fields and fetch the updated posts
        newPost.value = '';
        postingEncryptionKey.value = '';
        isEncrypted.value = false;
        fetchPosts();
    } catch (error) {
        console.error('Error adding post:', error);
    }
}

// AdvancedAtbash encryption function
const advancedAtbashEncrypt = (text) => {
    const alphabet = 'ZYXWVUTSRQPONMLKJIHGFEDCBA~`/?><.,:;|}{][+_)(*&^%$#@!9876543210';
    const reversedAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+[]{}|;:,.<>?/`~0123456789';

    return text
        .split('')
        .map((char) => {
            const isUpperCase = char === char.toUpperCase();
            const baseChar = char.toUpperCase();
            const index = alphabet.indexOf(baseChar);

            if (index === -1) return char;

            const reverseChar = reversedAlphabet[index];
            const forwardIndex = (alphabet.indexOf(reverseChar) + 1) % alphabet.length;
            const finalChar = alphabet[forwardIndex];

            return isUpperCase ? finalChar : finalChar.toLowerCase();
        })
        .join('');
};

// AdvancedAtbash decryption function
const advancedAtbashDecrypt = (text) => {
    const alphabet = 'ZYXWVUTSRQPONMLKJIHGFEDCBA~`/?><.,:;|}{][+_)(*&^%$#@!9876543210';
    const reversedAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+[]{}|;:,.<>?/`~0123456789';

    return text
        .split('')
        .map((char) => {
            const isUpperCase = char === char.toUpperCase();
            const baseChar = char.toUpperCase();
            const index = alphabet.indexOf(baseChar);

            if (index === -1) return char;

            const reverseChar = reversedAlphabet[index];
            const backwardIndex = (alphabet.indexOf(reverseChar) - 1 + alphabet.length) % alphabet.length;
            const finalChar = alphabet[backwardIndex];

            return isUpperCase ? finalChar : finalChar.toLowerCase();
        })
        .join('');
};

// Toggle decryption input field visibility
function toggleDecryptionField(post) {
    post.showDecryptionField = !post.showDecryptionField; // Toggle visibility
}

// Decrypt a specific post with key validation
// Decrypt a specific post with key validation
function decryptPost(post) {
    // Check if a decryption key has been entered
    if (!post.decryptionKey) {
        post.decryptedContent = 'Please enter a decryption key.';
        return;
    }

    // Validate the encryption key before attempting decryption
    if (post.decryptionKey !== post.encryption_key) {
        post.decryptedContent = 'Invalid decryption key.';
        return;
    }

    try {
        // If the key is valid, decrypt the content
        const decrypted = advancedAtbashDecrypt(post.content);
        post.decryptedContent = decrypted;
    } catch (error) {
        // Handle any errors during decryption
        post.decryptedContent = 'Decryption failed. Please try again.';
    }
}


// Fetch posts on component mount
fetchPosts();
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
                        v-model="postingEncryptionKey"
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
                    <!-- User Info -->
                    <p class="post-meta">
                        <strong>Posted by:</strong> {{ post.user?.name || 'Anonymous' }}
                    </p>

                    <!-- Encrypted or Plain Content -->
                    <p v-if="post.encrypted" class="post-content">
                        <strong>Encrypted:</strong>
                        <span
                            @click="toggleDecryptionField(post)"
                            class="encrypted-message"
                        >
                            {{ post.content }}
                        </span>
                    </p>
                    <p v-else class="post-content">
                        {{ post.content }}
                    </p>

                    <!-- Decryption Section -->
                    <div
                        v-if="post.showDecryptionField && post.encrypted"
                        class="decrypt-section"
                    >
                        <input
                            type="text"
                            v-model="post.decryptionKey"
                            placeholder="Enter key to decrypt"
                            class="input-key"
                        />
                        <button
                            @click="decryptPost(post)"
                            class="decrypt-button"
                        >
                            Decrypt
                        </button>
                        <p class="decrypted-content" v-if="post.decryptedContent">
                            <strong>Decrypted:</strong>
                            {{ post.decryptedContent }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

