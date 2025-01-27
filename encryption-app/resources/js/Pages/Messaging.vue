<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import CryptoJS from 'crypto-js';
import '../../css/messaging.css';

export default {
  setup() {
    const messages = ref([]);
    const newMessage = ref('');
    const selectedReceiver = ref(null);
    const users = ref([]);
    const encryptionKey = ref('');
    const encryptionType = ref('none');
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

    const advancedAtbashEncrypt = (text) => {
      const alphabet = 'ZYXWVUTSRQPONMLKJIHGFEDCBA~`/?><.,:;|}{][+_)(*&^%$#@!9876543210';
      const reversedAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+[]{}|;:,.<>?/`~0123456789';

      return text
        .split('')
        .map((char) => {
          const isUpperCase = char === char.toUpperCase();
          const baseChar = char.toUpperCase();
          const index = alphabet.indexOf(baseChar);

          if (index === -1) return char; // Non-alphabetic characters remain unchanged

          const reverseChar = reversedAlphabet[index];
          const forwardIndex = (alphabet.indexOf(reverseChar) + 1) % alphabet.length;
          const finalChar = alphabet[forwardIndex];

          return isUpperCase ? finalChar : finalChar.toLowerCase();
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

      while (!requiredLengths.includes(paddedKey.length)) {
        paddedKey += key;
        if (paddedKey.length > 32) {
          paddedKey = paddedKey.slice(0, 32);
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

      let encryptedMessage = newMessage.value;
      let isEncrypted = false;

      if (encryptionType.value === 'advancedAtbash') {
        if (!encryptionKey.value.trim()) {
          alert('Please provide an encryption key for AdvancedAtbash.');
          return;
        }

        encryptedMessage = advancedAtbashEncrypt(newMessage.value);
        isEncrypted = true;
      } else if (encryptionType.value === 'atbash') {
        if (!encryptionKey.value.trim()) {
          alert('Please provide an encryption key for Atbash.');
          return;
        }

        encryptedMessage = atbashEncrypt(newMessage.value);
        isEncrypted = true;
      } else if (encryptionType.value === 'aes') {
        if (!encryptionKey.value.trim()) {
          alert('Please provide an encryption key for AES.');
          return;
        }

        const key = CryptoJS.enc.Utf8.parse(padKey(encryptionKey.value));
        const iv = CryptoJS.lib.WordArray.random(16);
        const ciphertext = CryptoJS.AES.encrypt(newMessage.value, key, {
          iv: iv,
          mode: CryptoJS.mode.CBC,
          padding: CryptoJS.pad.Pkcs7,
        });

        encryptedMessage = iv.concat(ciphertext.ciphertext).toString(CryptoJS.enc.Base64);
        isEncrypted = true;
      }

      try {
        const response = await axios.post('/messages', {
          receiver_id: selectedReceiver.value,
          message: encryptedMessage,
          is_encrypted: isEncrypted,
          encryption_type: encryptionType.value,
          encryption_key: encryptionKey.value || null,
        });

        messages.value.push({
          ...response.data,
          decryptedMessage: null,
          decryptionKey: '',
          showDecryptionField: false,
        });

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
          if (message.encryption_type === 'advancedAtbash') {
            message.decryptedMessage = advancedAtbashEncrypt(message.message);
          } else if (message.encryption_type === 'atbash') {
            message.decryptedMessage = atbashEncrypt(message.message);
          } else if (message.encryption_type === 'aes') {
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
        <template v-else-if="message.is_encrypted && message.encryption_type === 'advancedAtbash'">
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
          <option value="advancedAtbash">AdvancedAtbash</option>
          <option value="aes">AES (Base64)</option>
        </select>

        <input
          type="password"
          v-if="encryptionType === 'aes' || encryptionType === 'atbash'|| encryptionType === 'advancedAtbash'"
          v-model="encryptionKey"
          placeholder="Enter encryption key"
        />

      </div>
      <button @click="sendMessage">Send</button>
    </div>
  </div>
</template>
