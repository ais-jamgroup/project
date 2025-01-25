<script setup>
import { ref } from 'vue';
import CryptoJS from 'crypto-js'; // Install if not already included
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
    display: grid; /* Use CSS Grid for layout */
    grid-template-columns: auto 1fr; /* Sidebar + Content */
    width: 100%;
    height: 100vh;
    background: #fbe8a6; /* Yellow background */
    overflow: hidden;
}

/* Sidebar */
.sidebar {
    width: 250px; /* Default sidebar width */
    background: #303c6c; /* Dark blue sidebar */
    transition: width 0.3s ease; /* Smooth transition when toggling sidebar */
    color: white;
}

/* Collapsed Sidebar */
.sidebar.collapsed {
    width: 80px; /* Collapsed sidebar width */
}

/* Content Area */
.content {
    padding: 1rem; /* Space around content */
    flex-direction: column; /* Stack input and posts */
    gap: 1rem; /* Space between post-input and posts */
}

/* Post Input Section */
.post-input {
    position: sticky; /* Keep it at the top */
    top: 1rem; /* Adjust to make it lower (e.g., 4rem from the top) */
    background: #d2fdff; /* Light blue background */
    padding: 0.5rem 1rem; /* Reduce padding for shorter height */
    max-width: 800px; /* Limit the width */
    
    border-radius: 10px; /* Rounded corners */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    z-index: 1000; /* Ensure it's above other content */
    display: flex; /* Use flex layout for alignment */
    flex-direction: column; /* Stack child elements vertically */
    gap: 0.5rem; /* Reduce the spacing between elements */
    margin-top: 1rem; /* Prevent extra space below the input */
    overflow: hidden;
}


/* Posts Section */
.posts {
    flex: 1; /* Take up remaining space */
    padding: 0.5rem; /* Reduce padding */
    max-height: calc(100vh - 50px); /* Adjust height to fit the screen, accounting for post-input */
    overflow-y: auto; /* Allow scrolling for long lists */
    margin-top: 1rem; /* Add some spacing from the input area */
    box-sizing: border-box; /* Prevent scrollbar from affecting width */
}

/* Individual Post Styling */
.posts .post {
    background: #d2fdff; /* Light blue background for posts */
    border: 1px solid #b4dfe5; /* Border matching the theme */
    margin-bottom: 0.5rem; /* Reduce space between posts */
    padding: 1rem;
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
}
/* Textarea Styles */
textarea {
    resize: none;
    font-family: 'Arial', sans-serif;
    color: #303c6c; /* Dark text color */
    min-height: 120px; /* Adequate height for the text area */
    width: 100%; /* Full width of the parent container */
    border: 1px solid #303c6c; /* Border color matching theme */
    border-radius: 8px; /* Rounded corners */
    padding: 0.8rem; /* Internal spacing */
    box-sizing: border-box; /* Include padding in width/height calculation */
    background: #fff; /* White background for readability */
    margin-bottom: 1rem; /* Space below the textarea */
}

/* Input Field Styles */
input {
    font-family: 'Arial', sans-serif;
    color: #303c6c; /* Dark text color */
    padding: 0.8rem;
    width: 70%; /* Make the input field longer */
    border: 1px solid #303c6c; /* Border color matching theme */
    border-radius: 8px;
    box-sizing: border-box; /* Include padding in width/height calculation */
    background: #fff; /* White background for readability */
}

/* Checkbox and Label Styling */
label {
    font-family: 'Arial', sans-serif;
    color: #303c6c; /* Dark text color for label */
    display: flex;
    align-items: center;
    gap: 0.5rem; /* Space between checkbox and label text */
}

input[type="checkbox"] {
    accent-color: #f4976c; /* Checkbox color matching the theme */
    width: 20px;
    height: 20px;
    cursor: pointer;
}

/* Button Styles */
button {
    padding: 0.8rem 1.5rem;
    background: #f4976c; /* Orange button background */
    color: white;
    font-weight: bold;
    border: none;
    border-radius: 8px; /* Rounded corners */
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth transitions */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Button shadow */
}

button:hover {
    background: #e0865c; /* Darker orange on hover */
    transform: scale(1.05); /* Slight scaling effect */
}

/* Webkit-based Browsers (Chrome, Edge, Safari) */
::-webkit-scrollbar {
    width: 2px; /* Make the scrollbar thin */
    height: 6px; /* Horizontal scrollbar height */
}

::-webkit-scrollbar-thumb {
    background-color: #f4976c; /* Scrollbar thumb color */
    border-radius: 50px; /* Rounded edges for the thumb */
}

::-webkit-scrollbar-thumb:hover {
    background-color: #e0865c; /* Darker color on hover */
}

::-webkit-scrollbar-track {
    background-color: #d2fdff; /* Track color */
    border-radius: 10px; /* Rounded edges for the track */
}

/* Firefox */
* {
    scrollbar-width: thin; /* Make scrollbar thin */
    scrollbar-color: #f4976c #d2fdff; /* Thumb color and track color */
}

/* Freedom Well Message Section */
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

/* Container for Icon and Text */
.freedom-icon-container {
    position: relative;
    display: inline-block;
    width: 120px; /* Adjust the size */
    height: 120px; /* Adjust the size */
}

/* SVG Icon */
.freedom-icon {
    width: 60%;
    height: 60%;
    position: absolute; /* Make it the background layer */
    top: 0;
    left: 0;
    z-index: 1; /* Keep it below the text */
    fill: #f4976c;
}

/* Freedom Text */
.freedom-text {
    position: absolute; /* Position the text on top of the SVG */
    top: 50%; /* Center vertically */
    left: 63%; /* Center horizontally */
    transform: translate(-15%, -15%); /* Adjust for proper centering */
    z-index: 2; /* Keep it above the SVG */
    font-size: 1.3rem;
    color: #303c6c; /* Match theme */
    font-weight: bold;
    text-align: center;
    padding: 2rem;
}

</style>
