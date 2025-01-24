<template>
  <div class="messaging-container">
    <!-- Receiver Selection -->
    <div class="user-profiles">
      <div
        v-for="user in users"
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
          <strong>{{ message.sender.name }}:</strong>
          <div>
            <!-- Clickable encrypted message -->
            <span
              v-if="!message.decryptedMessage"
              @click="toggleDecryptionField(message)"
              class="encrypted-message"
            >
              {{ formatEncryptedMessage(message.message) }}
            </span>
            <!-- Show decrypted message -->
            <span v-else>🔓 {{ message.decryptedMessage }}</span>

            <!-- toggled -->
            <div v-if="message.showDecryptionField">
              <input
                type="password"
                v-model="message.decryptionKey"
                placeholder="Enter decryption key"
              />
              <button @click="decryptMessage(message)">
                Decrypt
              </button>
            </div>
          </div>
        </template>
        <template v-else>
          <strong>{{ message.sender.name }}:</strong>
          {{ message.message }}
        </template>
      </div>
    </div>

    <!-- Message Input -->
    <div class="message-input">
      <input
        type="text"
        v-model="newMessage"
        placeholder="Type a message..."
      />
      <input
        type="password"
        v-model="encryptionKey"
        placeholder="Enter encryption key (optional)"
        class="encryption-key"
      />
      <button @click="sendMessage">Send</button>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
  setup() {
    const messages = ref([]);
    const newMessage = ref('');
    const selectedReceiver = ref(null);
    const users = ref([]);
    const encryptionKey = ref('');

    const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const reversedAlphabet = 'ZYXWVUTSRQPONMLKJIHGFEDCBA';

    // Custom Encryption Logic
    const customEncrypt = (text) => {
        return text
          .split('')
          .map((char) => {
            const isUpperCase = char === char.toUpperCase();
            const baseChar = char.toUpperCase(); // Convert to uppercase for processing
            const index = alphabet.indexOf(baseChar);

            if (index === -1) return char; // Non-alphabetic characters

            const reverseChar = reversedAlphabet[index]; //Reverse counterpart
            const shiftedIndex = (alphabet.indexOf(reverseChar) + 1) % alphabet.length; //Shift forward
            const encryptedChar = alphabet[shiftedIndex];

            // Restore original case
            return isUpperCase ? encryptedChar : encryptedChar.toLowerCase();
          })
          .join('');
      };


    // Custom Decryption Logic
    const customDecrypt = (text) => {
        return text
          .split('')
          .map((char) => {
            const isUpperCase = char === char.toUpperCase();
            const baseChar = char.toUpperCase(); // Convert to uppercase for processing
            const index = alphabet.indexOf(baseChar);

            if (index === -1) return char; // Non-alphabetic characters remain unchanged

            const shiftedIndex = (index - 1 + alphabet.length) % alphabet.length; // Step 1: Shift backward
            const reverseChar = reversedAlphabet[shiftedIndex]; // Step 2: Reverse counterpart

            // Restore original case
            return isUpperCase ? reverseChar : reverseChar.toLowerCase();
          })
          .join('');
      };

    // Format Encrypted Message for Display
    const formatEncryptedMessage = (message) => {
      return message; // Display the actual encrypted message
    };

    // Fetch Users
    const fetchUsers = async () => {
      const response = await axios.get('/users');
      users.value = response.data;
    };

    // Fetch Messages
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

    // Decrypt Message
    const decryptMessage = (message) => {
      if (!message.decryptionKey) {
        alert('Please enter a decryption key.');
        return;
      }

      try {
        const decrypted = customDecrypt(message.message);
        if (decrypted) {
          message.decryptedMessage = decrypted;
          message.decryptionKey = '';
          message.showDecryptionField = false;
        } else {
          alert('Invalid decryption key!');
        }
      } catch (error) {
        alert('Decryption failed! Invalid key.');
      }
    };

    // Toggle Decryption Field
    const toggleDecryptionField = (message) => {
      message.showDecryptionField = !message.showDecryptionField;
    };

    // Send Message
    const sendMessage = async () => {
      if (!newMessage.value.trim()) {
        alert('Message cannot be empty.');
        return;
      }

      let encryptedMessage = newMessage.value;
      const isEncrypted = encryptionKey.value.trim() !== '';

      if (isEncrypted) {
        encryptedMessage = customEncrypt(newMessage.value);
      }

      const response = await axios.post('/messages', {
        receiver_id: selectedReceiver.value,
        message: encryptedMessage,
        is_encrypted: isEncrypted,
      });

      messages.value.push({
        ...response.data,
        decryptedMessage: null,
        decryptionKey: '',
        showDecryptionField: false,
      });

      newMessage.value = '';
      encryptionKey.value = '';
    };

    // Select Receiver
    const selectUser = (userId) => {
      selectedReceiver.value = userId;
      fetchMessages();
    };

    // Fetch data when the component mounts
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
      formatEncryptedMessage,
      encryptionKey,
    };
  },
};
</script>
<style>
  .messaging-container {
  background: #b4dfe5;
  backdrop-filter: blur(20px);
  border-radius: 1rem;
  border: 1px solid rgb(0, 0, 0);
  box-shadow: 0 10px 25px rgb(0, 0, 0);
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
  border: 1px solid #000000;
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
</style>
  