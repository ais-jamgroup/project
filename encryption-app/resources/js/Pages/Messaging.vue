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
                <img :src="user.avatar || 
                  '/images/default-avatar.png'" 
                  alt="User Avatar" 
                  class="avatar" />

                <p>{{ user.name }}</p>
            </div>
        </div>

      <!-- TO DO !!!!! -->
      <!-- <div class="messages-list">
        <div v-for="message in messages" :key="message.id" class="message">
          <strong v-if="message.sender_id === userId">You:</strong>
          <strong v-else>{{ message.sender.name }}:</strong>
          {{ message.message }}
        </div>
      </div> -->

     <div class="messages-list">
      <div v-for="message in messages" :key="message.id" class="message">
        <template v-if="message.is_encrypted">
          <strong>{{ message.sender.name }}:</strong>
          <div>
            <span v-if="!message.decryptedMessage">Encrypted Message</span>
            <span v-else>{{ message.decryptedMessage }}</span>
            <input
              v-if="!message.decryptedMessage"
              type="password"
              v-model="message.decryptionKey"
              placeholder="Enter decryption key"
            />
            <button v-if="!message.decryptedMessage" @click="decryptMessage(message)">Decrypt</button>
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
  import CryptoJS from 'crypto-js';

  export default {
    props: ['userId'], 
    setup(props) {
      const messages = ref([]);
      const newMessage = ref('');
      const selectedReceiver = ref(null); 
      const users = ref([]);
      const encryptionKey = ref(''); 

      // Fetch Messages Based on Selected Receiver
      const fetchMessages = async () => {
        if (!selectedReceiver.value) return;

        const response = await axios.get('/messages', {
          params: { receiver_id: selectedReceiver.value },
        });

        messages.value = response.data;
      };

      const fetchUsers = async () => {
        const response = await axios.get('/users');
        users.value = response.data;
      };

      // Select User
      const selectUser = (userId) => {
        selectedReceiver.value = userId;
        fetchMessages(); 
      };

      const decryptMessage = (message) => {
        try {
            const bytes = CryptoJS.AES.decrypt(message.message, message.decryptionKey);
            const originalText = bytes.toString(CryptoJS.enc.Utf8);
            if (originalText) {
              message.decryptedMessage = originalText; // Save decrypted message
              message.decryptionKey = '';
            } else {
              alert('Invalid decryption key!');
            }
          } catch (error) {
            alert('Invalid decryption key!');
          }
      };

    const sendMessage = async () => {
      if (!newMessage.value.trim()) return;

      let encryptedMessage = newMessage.value;
      if (encryptionKey.value.trim()) {
        encryptedMessage = CryptoJS.AES.encrypt(newMessage.value, encryptionKey.value).toString();
      }

      const response = await axios.post('/messages', {
        receiver_id: selectedReceiver.value, // Send to the selected receiver
        message: encryptedMessage,
        is_encrypted: !!encryptionKey.value.trim(), // Flag to indicate if the message is encrypted
      });

      messages.value.push(response.data);
      newMessage.value = '';
      encryptionKey.value = ''; // Clear the key field
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
        encryptionKey,
      };
    },
  };

</script>

<style>
  .messaging-container {
    background:#b4dfe5; 
    backdrop-filter: blur(20px);
    border-radius: 1rem;
    border: 1px solid rgb(0, 0, 0);
    box-shadow: 0 10px 25px rgb(0, 0, 0);
    padding: 1.5rem;
    width: 100%; 
    max-width: 900px;
  }
  
  .receiver-select {
    margin-bottom: 10px;
    padding: 5px;
    width: 100%;
  }
  
  .messages-list {
    flex: 1;
    overflow-y: auto;
    border: 1px solid #ccc;
    padding: 10px;
    height: 300px;
    margin-bottom: 10px;
    background:#d2fdff; 
  }
  
  .message {
    margin-bottom: 10px;
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
    background:#d2fdff; 
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

  .user-profiles {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    scrollbar-width: thin; 
    -ms-overflow-style: none;
  }

  .user-profiles::-webkit-scrollbar {
    height: 8px;
    background-color: #000000;
  }

  .user-profiles::-webkit-scrollbar-thumb {
    background-color: #cccccc;
    border-radius: 4px;
  }
  
</style>
  