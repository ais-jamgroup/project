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
    const snackbar = ref({
      message: '',
      visible: false,
      type: '', // 'error' or 'success'
    });

    const showSnackbar = (message, type = 'error') => {
      snackbar.value.message = message;
      snackbar.value.type = type;
      snackbar.value.visible = true;

      setTimeout(() => {
        snackbar.value.visible = false;
      }, 3000); // Snackbar disappears after 3 seconds
    };

    const filteredUsers = computed(() => {
      if (!searchQuery.value) return users.value;
      return users.value.filter((user) =>
        user.name.toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    });

    const atbashEncrypt = (text) => {
      const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
      const reversedAlphabet = 'ZYXWVUTSRQPONMLKJIHGFEDCBA';

      return text
        .split('')
        .map((char) => {
          const isUpperCase = char === char.toUpperCase();
          const baseChar = char.toUpperCase();
          const index = alphabet.indexOf(baseChar);

          if (index === -1) return char;

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

          if (index === -1) return char;

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
        isInvalidKey: false,
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
        showSnackbar('Message cannot be empty.', 'error');
        return;
      }

      let encryptedMessage = newMessage.value;
      let isEncrypted = false;

      if (encryptionType.value === 'advancedAtbash') {
        if (!encryptionKey.value.trim()) {
          showSnackbar('Please provide an encryption key for AdvancedAtbash.', 'error');
          return;
        }

        encryptedMessage = advancedAtbashEncrypt(newMessage.value);
        isEncrypted = true;
      } else if (encryptionType.value === 'atbash') {
        if (!encryptionKey.value.trim()) {
          showSnackbar('Please provide an encryption key for Atbash.', 'error');
          return;
        }

        encryptedMessage = atbashEncrypt(newMessage.value);
        isEncrypted = true;
      } else if (encryptionType.value === 'aes') {
        if (!encryptionKey.value.trim()) {
          showSnackbar('Please provide an encryption key for AES.', 'error');
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
          isInvalidKey: false,
        });

        newMessage.value = '';
        encryptionKey.value = '';
        showSnackbar('Message sent successfully!', 'success');
      } catch (error) {
        showSnackbar('An error occurred while sending the message.', 'error');
        console.error(error);
      }
    };

    const deleteMessage = async (messageId) => {
    try {
        await axios.delete(`/messages/${messageId}`);

        // Remove the message from the local array
        messages.value = messages.value.filter((msg) => msg.id !== messageId);

        showSnackbar('Message deleted successfully!', 'success');
    } catch (error) {
        console.error('Error deleting message:', error);
        showSnackbar('Failed to delete message.', 'error');
    }
};

    const decryptMessage = async (message) => {
      if (!message.decryptionKey) {
        showSnackbar('Please enter a decryption key.', 'error');
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
          message.isInvalidKey = false;

          showSnackbar('Message decrypted successfully!', 'success');
        } else {
          message.isInvalidKey = true;
          showSnackbar('Invalid decryption key!', 'error');
        }
      } catch (error) {
        message.isInvalidKey = true;
        console.error(error);
        showSnackbar('Invalid decryption key', 'error');
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
      snackbar,
      showSnackbar,
      deleteMessage,
    };
    
  },
  
};
</script>

<template>
  <div class="messaging-container">
    <div class="user-profiles">
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

    <div class="messages-list">
      <div v-for="message in messages" :key="message.id" class="message">
        <template v-if="message.is_encrypted && message.encryption_type === 'aes'">
          <strong>{{ message.sender?.name || 'Unknown Sender' }}:</strong>
          <div>
            <span
              v-if="!message.decryptedMessage && !message.showDecryptionField"
              @click="toggleDecryptionField(message)"
              class="encrypted-message"
            >
              🔒 Encrypted Message
            </span>

            <div v-if="message.showDecryptionField && !message.decryptedMessage" class="decryption-container">
              <input
                type="password"
                v-model="message.decryptionKey"
                placeholder="Enter decryption key"
                :class="{ invalid: message.isInvalidKey }"
                class="decryption-input"
              />
              <button @click="decryptMessage(message)" class="decryption-button">
                Decrypt
              </button>
              <button @click="toggleDecryptionField(message)" class="cancel-button">
                Cancel
              </button>
              <button @click="deleteMessage(message.id)" class="delete-button">
                Delete
              </button>
              <p v-if="message.isInvalidKey" class="error-message"></p>
            </div>

            <span v-else-if="message.decryptedMessage">🔓 {{ message.decryptedMessage }}
              <button @click="deleteMessage(message.id)" class="delete-button">
                Delete
              </button>
            </span>
          </div>
        </template>

        <template v-else-if="message.is_encrypted && message.encryption_type === 'atbash'">
          <strong>{{ message.sender?.name || 'Unknown Sender' }}:</strong>
          <div>
            <span
              v-if="!message.decryptedMessage && !message.showDecryptionField"
              @click="toggleDecryptionField(message)"
              class="encrypted-message"
            >
              🔒 {{ message.message }}
            </span>

            <div v-if="message.showDecryptionField && !message.decryptedMessage" class="decryption-container">
              <input
                type="password"
                v-model="message.decryptionKey"
                placeholder="Enter decryption key"
                :class="{ invalid: message.isInvalidKey }"
                class="decryption-input"
              />
              <button @click="decryptMessage(message)" class="decryption-button">
                Decrypt
              </button>
              <button @click="toggleDecryptionField(message)" class="cancel-button">
                Cancel
              </button>
              <button @click="deleteMessage(message.id)" class="delete-button">
                Delete
              </button>
              <p v-if="message.isInvalidKey" class="error-message"></p>
            </div>

            <span v-else-if="message.decryptedMessage">
              🔓 {{ message.decryptedMessage }}
              <button @click="deleteMessage(message.id)" class="delete-button">
                Delete
              </button>
            </span>
          </div>
        </template>

        <template v-else-if="message.is_encrypted && message.encryption_type === 'advancedAtbash'">
          <strong>{{ message.sender?.name || 'Unknown Sender' }}:</strong>
          <div>
            <span
              v-if="!message.decryptedMessage && !message.showDecryptionField"
              @click="toggleDecryptionField(message)"
              class="encrypted-message"
            >
              🔒 {{ message.message }}</span>

            <div v-if="message.showDecryptionField && !message.decryptedMessage" class="decryption-container">
              <input
                type="password"
                v-model="message.decryptionKey"
                placeholder="Enter decryption key"
                :class="{ invalid: message.isInvalidKey }"
                class="decryption-input"
              />
              <button @click="decryptMessage(message)" class="decryption-button">
                Decrypt
              </button>
              <button @click="toggleDecryptionField(message)" class="cancel-button">
                Cancel
              </button>
              <button @click="deleteMessage(message.id)" class="delete-button">
                Delete
              </button>
              <p v-if="message.isInvalidKey" class="error-message"></p>
            </div>

            <span v-else-if="message.decryptedMessage">🔓 {{ message.decryptedMessage }}
              <button @click="deleteMessage(message.id)" class="decrypted-delete-button">
                Delete
              </button>
            </span>
          </div>
        </template>

        <template v-else>
          <strong>{{ message.sender?.name || 'Unknown Sender' }}:</strong>
          {{ message.message }}
        </template>
      </div>
    </div>

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
          v-if="encryptionType === 'aes' || encryptionType === 'atbash' || encryptionType === 'advancedAtbash'"
          v-model="encryptionKey"
          placeholder="Enter encryption key"
        />
      </div>
      <button @click="sendMessage">Send</button>
    </div>

  


    <!-- Snackbar -->
    <div
      class="snackbar"
      :class="{ show: snackbar.visible, error: snackbar.type === 'error', success: snackbar.type === 'success' }"
    >
      {{ snackbar.message }}
    </div>
  </div>
</template>
