<template>
  <div class="messaging-container">
    <!-- Receiver Selection -->
    <div class="user-profiles">
      <!-- Search Input -->
      <div class="search-container">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search users..."
          class="search-input"
        />
        <button class="search-button" @click="handleSearch">
          🔍
        </button>
      </div>

      <!-- Filtered User List -->
      <div
        v-for="user in filteredUsers"
        :key="user.id"
        class="user-profile"
        :class="{ active: selectedReceiver === user.id }"
        @click="selectUser(user.id)"
      >
        <img :src="user.avatar || '/images/default-avatar.png'" alt="User Avatar" class="avatar" />
        <p>{{ user.name }}</p>
      </div>
    </div>

    <!-- Messages List -->
    <div class="messages-list">
      <div v-for="message in messages" :key="message.id" class="message">
        <template v-if="message.is_encrypted && message.encryption_type === 'aes'">
          <!-- AES Encrypted Message -->
          <strong>{{ message.sender?.name || 'Unknown Sender' }}:</strong>
          <div>
            <!-- Placeholder for AES-encrypted message -->
            <span
              v-if="!message.decryptedMessage && !message.showDecryptionField"
              @click="toggleDecryptionField(message)"
              class="encrypted-message"
            >
              🔒 Encrypted Message
            </span>

            <!-- Field to enter the decryption key -->
            <div v-if="message.showDecryptionField && !message.decryptedMessage" class="decryption-container">
              <input
                type="password"
                v-model="message.decryptionKey"
                placeholder="Enter decryption key"
                class="decryption-input"
              />
              <button @click="decryptMessage(message)" class="decryption-button">
                Decrypt
              </button>
              <button @click="toggleDecryptionField(message)" class="cancel-button">
              Cancel
            </button>
            </div>

            <!-- Display decrypted AES message -->
            <span v-else-if="message.decryptedMessage">🔓 {{ message.decryptedMessage }}</span>
          </div>
        </template>

        <template v-else-if="message.is_encrypted && message.encryption_type === 'atbash'">
          <!-- Atbash Encrypted Message -->
          <strong>{{ message.sender?.name || 'Unknown Sender' }}:</strong>
          <div>
            <!-- Show encrypted message and a clickable toggle -->
            <span
              v-if="!message.decryptedMessage && !message.showDecryptionField"
              @click="toggleDecryptionField(message)"
              class="encrypted-message"
            >
            
              🔒 {{ message.message }} 
            </span>

            <!-- Show the decrypt button -->
            <div v-if="message.showDecryptionField && !message.decryptedMessage" class="decryption-container">
              <input
                  type="password"
                  v-model="message.decryptionKey"
                  placeholder="Enter decryption key"
                  class="decryption-input"
                />
              <button @click="decryptMessage(message)" class="decryption-button">
                Decrypt
              </button>
              <button @click="toggleDecryptionField(message)" class="cancel-button">
                Cancel
              </button>
            </div>

            <!-- Show decrypted message -->
            <span v-else-if="message.decryptedMessage">🔓 {{ message.decryptedMessage }}</span>
          </div>
        </template>

        <template v-else>
          <!-- Plaintext Message -->
          <strong>{{ message.sender?.name || 'Unknown Sender' }}:</strong>
          {{ message.message }}
        </template>
      </div>
    </div>

    <!-- Message Input -->
    <div class="message-input">
      <input type="text" v-model="newMessage" placeholder="Type a message..." />
      <div>
        <select v-model="encryptionType">
          <option value="none">None</option>
          <option value="atbash">Atbash</option>
          <option value="aes">AES (Base64)</option>
        </select>
        <input
          type="password"
          v-if="encryptionType === 'aes' || encryptionType === 'atbash'"
          v-model="encryptionKey"
          placeholder="Enter encryption key"
        />

      </div>
      <button @click="sendMessage">Send</button>
    </div>
  </div>
</template>


<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import CryptoJS from 'crypto-js';

export default {
  setup() {
    const messages = ref([]);
    const newMessage = ref('');
    const selectedReceiver = ref(null);
    const users = ref([]);
    const encryptionKey = ref('');
    const encryptionType = ref('none'); // Default to no encryption
    const searchQuery = ref(''); 

    const filteredUsers = computed(() => {
      if (!searchQuery.value) return users.value;
      return users.value.filter((user) =>
        user.name.toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    });

    // Atbash encryption logic
    const atbashEncrypt = (text) => {
      const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
      const reversedAlphabet = 'ZYXWVUTSRQPONMLKJIHGFEDCBA';

      return text
        .split('')
        .map((char) => {
          const isUpperCase = char === char.toUpperCase();
          const baseChar = char.toUpperCase();
          const index = alphabet.indexOf(baseChar);

          if (index === -1) return char; // Non-alphabetic characters remain unchanged

          const reverseChar = reversedAlphabet[index];
          return isUpperCase ? reverseChar : reverseChar.toLowerCase();
        })
        .join('');
    };

    const fetchUsers = async () => {
      const response = await axios.get('/users');
      users.value = response.data;
    };

    const fetchMessages = async () => {
      if (!selectedReceiver.value) return;

      const response = await axios.get('/messages', {
        params: { receiver_id: selectedReceiver.value },
      });

      messages.value = response.data.map((message) => ({
        ...message,
        decryptedMessage: null,
        decryptionKey: '',
        showDecryptionField: false,
      }));
    };
    const padKey = (key) => {
  const requiredLengths = [16, 24, 32];
  let paddedKey = key;

  // Repeat the key until it matches the nearest valid AES key length
  while (!requiredLengths.includes(paddedKey.length)) {
    paddedKey += key;
    if (paddedKey.length > 32) {
      paddedKey = paddedKey.slice(0, 32); // Trim to max valid length
      break;
    }
  }
  return paddedKey;
};

const sendMessage = async () => {
  if (!newMessage.value.trim()) {
    alert('Message cannot be empty.');
    return;
  }

  let encryptedMessage = newMessage.value; // Default to plaintext
  let isEncrypted = false;

  if (encryptionType.value === 'atbash') {
    if (!encryptionKey.value.trim()) {
      alert('Please provide an encryption key for Atbash.');
      return;
    }

    encryptedMessage = atbashEncrypt(newMessage.value); // Encrypt with Atbash
    isEncrypted = true;
  } else if (encryptionType.value === 'aes') {
    if (!encryptionKey.value.trim()) {
      alert('Please provide an encryption key for AES.');
      return;
    }

    // Pad the key
    const key = CryptoJS.enc.Utf8.parse(padKey(encryptionKey.value)); // Expand key to valid AES length
    const iv = CryptoJS.lib.WordArray.random(16); // Generate a random IV
    const ciphertext = CryptoJS.AES.encrypt(newMessage.value, key, {
      iv: iv,
      mode: CryptoJS.mode.CBC,
      padding: CryptoJS.pad.Pkcs7,
    });

    // Combine IV and ciphertext, encode in Base64
    encryptedMessage = iv.concat(ciphertext.ciphertext).toString(CryptoJS.enc.Base64);
    isEncrypted = true;
  }

  try {
    // Send the encrypted message to the backend
    const response = await axios.post('/messages', {
      receiver_id: selectedReceiver.value,
      message: encryptedMessage, // Send the encrypted message
      is_encrypted: isEncrypted, // Indicate encryption
      encryption_type: encryptionType.value, // Specify encryption type
      encryption_key: encryptionType.value === 'atbash' || encryptionType.value === 'aes' ? encryptionKey.value : null, // Send key if required
    });

    messages.value.push({
      ...response.data,
      decryptedMessage: null,
      decryptionKey: '',
      showDecryptionField: false,
    });

    // Reset input fields
    newMessage.value = '';
    encryptionKey.value = '';
  } catch (error) {
    alert('An error occurred while sending the message.');
    console.error(error);
  }
};



const decryptMessage = async (message) => {
  if (!message.decryptionKey) {
    alert('Please enter a decryption key.');
    return;
  }

  try {
    // Send the key to the backend for validation
    const response = await axios.post('/validate-key', {
      message_id: message.id,
      decryption_key: message.decryptionKey,
    });

    if (response.data.is_valid) {
      if (message.encryption_type === 'atbash') {
        // Decrypt the message with Atbash if the key is valid
        message.decryptedMessage = atbashEncrypt(message.message);
      } else if (message.encryption_type === 'aes') {
        // Handle AES decryption logic if necessary
        message.decryptedMessage = response.data.decrypted_message;
      }

      message.showDecryptionField = false;
      message.decryptionKey = '';
    } else {
      alert('Invalid decryption key!');
    }
  } catch (error) {
    alert('Decryption failed! Please try again.');
    console.error(error);
  }
};


    const toggleDecryptionField = (message) => {
      if (message.encryption_type === 'atbash') {
    message.showDecryptionField = !message.showDecryptionField;
  } else if (message.encryption_type === 'aes') {
    message.showDecryptionField = !message.showDecryptionField;
  }
    };

    const handleSearch = () => {
      console.log('Search triggered for:', searchQuery.value);
    };

    const selectUser = (userId) => {
      selectedReceiver.value = userId;
      fetchMessages();
    };

    onMounted(() => {
      fetchUsers();
    });

    return {
      messages,
      newMessage,
      selectedReceiver,
      users,
      selectUser,
      sendMessage,
      decryptMessage,
      toggleDecryptionField,
      encryptionKey,
      encryptionType,
      padKey,
      searchQuery,
      filteredUsers,
      handleSearch,
    };
  },
};
</script>

<style>
/* Messaging Container */
.messaging-container {
  background: #b4dfe5;
  backdrop-filter: blur(20px);
  border-radius: 1rem;
  border: 1px solid #303c6c;
  box-shadow: 0 10px 50px #303c6c;
  padding: 1.5rem;
  width: 100%;
  max-width: 900px;
}

/* User Profiles Container */
.user-profiles {
  display: flex;
  gap: 10px;
  overflow-x: auto;
  padding: 10px;
  background-color: #303c6c;
}

.user-profile {
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
  padding: 10px;
  border: 1px solid #f4976c;
  border-radius: 10px;
  min-width: 80px;
  text-align: center;
  transition: 0.2s ease;
  background: #d2fdff;
}

.user-profile.active {
  background-color: #f4976c;
  border-color: #007bff;
}

.avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
}

/* Messages List */
.messages-list {
  flex: 1;
  overflow-y: auto;
  border: 1px solid #ccc;
  padding: 10px;
  height: 300px;
  margin-bottom: 10px;
  background: #d2fdff;
}

.message {
  margin-bottom: 10px;
  padding: 10px;
  border-radius: 6px;
  background-color: #ffffff; 
  transition: background-color 0.3s ease;
}

.message:hover {
  background-color: #b4dfe5;
}

/* Encrypted Message */
.encrypted-message {
  color: #303c6c;
  cursor: pointer;
}

.encrypted-message:hover {
  color: #0056b3;
}

/* Message Input Container */
.message-input {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.message-input input {
  flex: 1;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 8px;
  outline: none;
  font-size: 14px;
  background-color: #f4f1de;
}

.message-input input:focus {
  border-color: #f4976c;
  background-color: #ffffff;
  box-shadow: 0 0 5px rgba(244, 151, 108, 0.5);
}

/* Select Dropdown Styling */
.message-input select {
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  outline: none;
  font-size: 14px;
  background-color: #f4f1de;
  color: #303c6c;
  cursor: pointer;
  width: 130px;
}

.message-input select:focus {
  border-color: #f4976c;
  box-shadow: 0 0 5px rgba(244, 151, 108, 0.5);
}

/* Encryption Key Field */
.message-input input[type="password"] {
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  outline: none;
  font-size: 14px;
  background-color: #f4f1de;
}

.message-input input[type="password"]:focus {
  border-color: #f4976c;
  background-color: #ffffff;
  box-shadow: 0 0 5px rgba(244, 151, 108, 0.5);
}

/* Decryption Container */
.decryption-container {
  display: flex;
  gap: 10px;
  margin-top: 10px;
  align-items: center;
}

/* Decryption Input Field */
.decryption-input {
  flex: 1;
  border: 1px solid #ccc;
  border-radius: 8px;
  outline: none;
  font-size: 14px;
  background-color: #f4f1de;
}

.decryption-input:focus {
  border-color: #f4976c;
  background-color: #ffffff;
  box-shadow: 0 0 5px rgba(244, 151, 108, 0.5);
}

/* Decryption Button */
.decryption-button {
  padding: 10px 14px;
  background-color: #f4976c;
  border: none;
  border-radius: 8px;
  color: white;
  font-size: 14px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.decryption-button:hover {
  background-color: #f08055;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Send Button */
.message-input button {
  padding: 10px 14px;
  background-color: #f4976c;
  border: none;
  border-radius: 8px;
  color: white;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.message-input button:hover {
  background-color: #f08055;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Search Container */
.search-container {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-bottom: 10px;
  width: 300px;
}

.search-input {
  flex: 1;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 8px 0 0 8px;
  outline: none;
  background-color: #f4f1de;
  color: #303c6c;
}

.search-input:focus {
  border-color: #f4976c;
  background-color: #ffffff;
  box-shadow: 0 0 5px rgba(244, 151, 108, 0.5);
}

.search-button {
  padding: 10px 14px;
  background-color: #f4976c;
  border: none;
  border-radius: 0 8px 8px 0;
  color: white;
  cursor: pointer;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s ease;
}

.search-button:hover {
  background-color: #f08055;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.cancel-button {
  padding: 10px 14px;
  background-color: #ccc;
  border: none;
  border-radius: 8px;
  color: #303c6c;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.cancel-button:hover {
  background-color: #bbb;
}


/* Custom Scrollbar */
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
</style>

  