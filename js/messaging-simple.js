/**
 * Simple Messaging System
 * Minimal, error-free chat functionality
 */

let chatData = {
  conversationId: null,
  currentUserId: null,
  sellerId: null,
  productId: null
};

// Initialize messaging
function initChat(productId, sellerId) {
  chatData.productId = productId;
  chatData.sellerId = sellerId;
  console.log('Chat initialized - Product:', productId, 'Seller:', sellerId);
}

// Open chat
function openChat() {
  const chatBox = document.getElementById('chatBox');
  if (chatBox) {
    chatBox.style.display = 'block';
    loadMessages();
  }
}

// Close chat
function closeChat() {
  const chatBox = document.getElementById('chatBox');
  if (chatBox) {
    chatBox.style.display = 'none';
  }
}

// Load messages
function loadMessages() {
  const url = 'api/get-conversation.php?product_id=' + chatData.productId;
  
  fetch(url)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        chatData.conversationId = data.conversation_id;
        chatData.currentUserId = data.current_user_id;
        
        const messagesDiv = document.getElementById('chatMessages');
        messagesDiv.innerHTML = '';
        
        if (data.messages && data.messages.length > 0) {
          data.messages.forEach(msg => {
            const div = document.createElement('div');
            div.className = 'msg ' + (msg.sender_id == chatData.currentUserId ? 'sent' : 'received');
            div.innerHTML = '<p>' + escapeHtml(msg.message_text) + '</p>';
            messagesDiv.appendChild(div);
          });
        } else {
          messagesDiv.innerHTML = '<p style="text-align:center; color:#999;">No messages yet</p>';
        }
        
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(e => alert('Error loading messages: ' + e.message));
}

// Send message
function sendMsg() {
  const input = document.getElementById('messageInput');
  const text = input.value.trim();
  
  if (!text) return;
  if (!chatData.conversationId) {
    alert('Chat not initialized');
    return;
  }
  
  const formData = new FormData();
  formData.append('conversation_id', chatData.conversationId);
  formData.append('receiver_id', chatData.sellerId);
  formData.append('product_id', chatData.productId);
  formData.append('message', text);
  
  fetch('api/send-message.php', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      input.value = '';
      loadMessages();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(e => alert('Error sending message: ' + e.message));
}

// Escape HTML
function escapeHtml(text) {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}

// Enter key to send
document.addEventListener('DOMContentLoaded', function() {
  const input = document.getElementById('messageInput');
  if (input) {
    input.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        sendMsg();
      }
    });
  }
});
