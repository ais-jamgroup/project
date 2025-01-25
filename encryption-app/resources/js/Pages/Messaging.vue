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
        <template v-if="message.is_encrypted">
          <strong>{{ message.sender?.name || 'Unknown Sender' }}:</strong>
          <div>
            <!-- Placeholder for encrypted message -->
            <span
              v-if="!message.decryptedMessage && !message.showDecryptionField"
              @click="toggleDecryptionField(message)"
              class="encrypted-message"
            >
              🔒 Encrypted Message (Click to decrypt)
            </span>

            <!-- Field to enter the decryption key -->
            <div v-if="message.showDecryptionField && !message.decryptedMessage">
              <input
                type="password"
                v-model="message.decryptionKey"
                placeholder="Enter decryption key"
              />
              <button @click="decryptMessage(message)">Decrypt</button>
            </div>

            <!-- Display decrypted message -->
            <span v-else-if="message.decryptedMessage">🔓 {{ message.decryptedMessage }}</span>
          </div>
        </template>
        <template v-else>
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
          v-if="encryptionType === 'aes'"
          v-model="encryptionKey"
          placeholder="Enter encryption key (for AES)"
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

  if (encryptionType.value === 'aes') {
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
      encryption_key: encryptionKey.value, // Send the plaintext key (user-provided)
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
    const response = await axios.post('/validate-key', {
      message_id: message.id,
      decryption_key: message.decryptionKey,
    });

    if (response.data.is_valid) {
      message.decryptedMessage = response.data.decrypted_message;
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
      message.showDecryptionField = !message.showDecryptionField;
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
}

.encrypted-message {
  color: #007bff;
  cursor: pointer;
  text-decoration: underline;
}

.encrypted-message:hover {
  color: #0056b3;
}

.message-input {
  display: flex;
  gap: 10px;
}

.message-input input {
  flex: 1;
  padding: 10px;
  border: 1px solid #ccc;
}

.message-input button {
  padding: 10px;
  cursor: pointer;
}

.search-container {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
}

.search-input {
  flex: 1;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 8px 0 0 8px;
  outline: none;
}

.search-button {
  padding: 10px;
  background-color: #f4976c;
  border: none;
  border-radius: 0 8px 8px 0;
  color: white;
  cursor: pointer;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.search-button:hover {
  background-color: #f08055;
}

.user-profiles {
  margin-top: 10px;
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

</style>
  