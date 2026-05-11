/**
 * LALIBELA GEBEYA - Messaging System (Inline Chat - Like Jiji.et)
 * Handles chat functionality on product detail page
 */

console.log('✓ messaging.js loading...');

let conversationId = null;
let currentUserId = null;
let sellerId = null;
let productId = null;
let messageRefreshInterval = null;

/**
 * Get correct API path based on current page location
 */
function getApiPath(endpoint) {
  // Get the base URL from the current location
  const protocol = window.location.protocol; // http: or https:
  const host = window.location.host; // localhost:8080 or example.com
  const pathname = window.location.pathname; // /lalibela-gebeya/test-messaging-full.php
  
  // Extract the base path (e.g., /lalibela-gebeya/)
  const pathParts = pathname.split('/').filter(p => p);
  let basePath = '';
  
  if (pathParts.length > 0) {
    // First part is usually the project folder
    basePath = '/' + pathParts[0] + '/';
  }
  
  // Return absolute path to API
  return `${protocol}//${host}${basePath}api/${endpoint}`;
}

/**
 * Initialize messaging system
 */
function initializeMessaging(productIdParam, sellerIdParam) {
  productId = productIdParam;
  sellerId = sellerIdParam;
  console.log('✓ Messaging initialized - Product:', productId, 'Seller:', sellerId);
}

/**
 * Open chat box
 */
function startChat() {
  console.log('✓ startChat() called');
  
  if (!productId || !sellerId) {
    console.error('✗ Missing productId or sellerId');
    alert('Error: Product or Seller information missing');
    return;
  }
  
  console.log('✓ Showing chat box...');
  
  // Show chat box
  const chatBox = document.getElementById('chatBox');
  if (chatBox) {
    chatBox.style.display = 'block';
    console.log('✓ Chat box is now visible');
  } else {
    console.error('✗ Chat box element not found');
    return;
  }
  
  // Load conversation
  loadConversation();
}

/**
 * Close chat box
 */
function closeChat() {
  console.log('✓ closeChat() called');
  const chatBox = document.getElementById('chatBox');
  if (chatBox) {
    chatBox.style.display = 'none';
    console.log('✓ Chat box is now hidden');
  }
  stopAutoRefresh();
}

/**
 * Load conversation from server
 */
function loadConversation() {
  console.log('✓ loadConversation() called');
  
  const chatMessages = document.getElementById('chatMessages');
  if (chatMessages) {
    chatMessages.innerHTML = '<div style="padding: 20px; text-align: center; color: #999;">Loading messages...</div>';
  }
  
  // Fetch conversation
  const url = `${getApiPath('get-conversation.php')}?product_id=${productId}`;
  console.log('✓ Fetching from:', url);
  
  fetch(url)
    .then(response => {
      console.log('✓ Response received:', response.status);
      if (!response.ok) throw new Error('Network error: ' + response.status);
      return response.json();
    })
    .then(data => {
      console.log('✓ Data received:', data);
      
      if (data.success) {
        conversationId = data.conversation_id;
        currentUserId = data.current_user_id;
        
        console.log('✓ Conversation ID:', conversationId);
        
        // Display messages
        displayMessages(data.messages || []);
        
        // Start auto-refresh
        startAutoRefresh();
        
        // Focus input and add Enter key handler
        setTimeout(() => {
          const input = document.getElementById('messageInput');
          if (input) {
            input.focus();
            console.log('✓ Input focused');
            
            // Add Enter key handler
            input.addEventListener('keydown', function(e) {
              if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
              }
            });
          }
        }, 100);
      } else {
        console.error('✗ API error:', data.message);
        showError('Failed to load conversation: ' + data.message);
      }
    })
    .catch(error => {
      console.error('✗ Fetch error:', error);
      showError('Error loading conversation: ' + error.message);
    });
}

/**
 * Display all messages
 */
function displayMessages(messages) {
  console.log('✓ displayMessages() called with', messages.length, 'messages');
  
  const chatMessages = document.getElementById('chatMessages');
  if (!chatMessages) {
    console.error('✗ chatMessages element not found');
    return;
  }
  
  chatMessages.innerHTML = '';
  
  if (!messages || messages.length === 0) {
    chatMessages.innerHTML = '<div style="padding: 20px; text-align: center; color: #999;">No messages yet. Start the conversation!</div>';
    return;
  }
  
  messages.forEach(msg => {
    displayMessage(msg);
  });
  
  // Scroll to bottom
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

/**
 * Display single message
 */
function displayMessage(message) {
  const chatMessages = document.getElementById('chatMessages');
  if (!chatMessages || !message) return;
  
  const isSent = message.sender_id === currentUserId;
  
  const msgDiv = document.createElement('div');
  msgDiv.className = `chat-message ${isSent ? 'sent' : 'received'}`;
  
  let html = '';
  if (!isSent && message.sender_name) {
    html += `<div class="msg-sender">${escapeHtml(message.sender_name)}</div>`;
  }
  html += `<div class="msg-text">${escapeHtml(message.message_text)}</div>`;
  html += `<div class="msg-time">${formatTime(message.created_at)}</div>`;
  
  msgDiv.innerHTML = html;
  chatMessages.appendChild(msgDiv);
  
  // Auto scroll
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

/**
 * Send message
 */
function sendMessage() {
  console.log('✓ sendMessage() called');
  
  const input = document.getElementById('messageInput');
  if (!input) {
    console.error('✗ messageInput not found');
    return;
  }
  
  const messageText = input.value.trim();
  if (!messageText) {
    console.warn('⚠ Empty message');
    return;
  }
  
  if (!conversationId || !sellerId || !productId) {
    console.error('✗ Missing conversation data');
    alert('Chat not initialized');
    return;
  }
  
  console.log('✓ Sending message:', messageText);
  
  const formData = new FormData();
  formData.append('conversation_id', conversationId);
  formData.append('receiver_id', sellerId);
  formData.append('product_id', productId);
  formData.append('message', messageText);
  
  fetch(getApiPath('send-message.php'), {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    console.log('✓ Send response:', data);
    
    if (data.success) {
      input.value = '';
      displayMessage(data.data);
      input.focus();
      console.log('✓ Message sent successfully');
    } else {
      console.error('✗ Send failed:', data.message);
      showError('Failed to send: ' + data.message);
    }
  })
  .catch(error => {
    console.error('✗ Send error:', error);
    showError('Error sending message');
  });
}

/**
 * Auto-refresh messages
 */
function startAutoRefresh() {
  console.log('✓ startAutoRefresh() called');
  stopAutoRefresh();
  messageRefreshInterval = setInterval(() => {
    if (conversationId) {
      refreshMessages();
    }
  }, 3000);
}

/**
 * Stop auto-refresh
 */
function stopAutoRefresh() {
  if (messageRefreshInterval) {
    clearInterval(messageRefreshInterval);
    messageRefreshInterval = null;
    console.log('✓ Auto-refresh stopped');
  }
}

/**
 * Refresh messages
 */
function refreshMessages() {
  if (!conversationId) return;
  
  fetch(`${getApiPath('get-conversation.php')}?conversation_id=${conversationId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.messages) {
        const currentCount = document.querySelectorAll('.chat-message').length;
        if (data.messages.length > currentCount) {
          console.log('✓ New messages found, updating...');
          displayMessages(data.messages);
        }
      }
    })
    .catch(error => console.error('✗ Refresh error:', error));
}

/**
 * Format time
 */
function formatTime(timestamp) {
  const date = new Date(timestamp);
  return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}

/**
 * Escape HTML
 */
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

/**
 * Show error
 */
function showError(message) {
  const chatMessages = document.getElementById('chatMessages');
  if (chatMessages) {
    const errorDiv = document.createElement('div');
    errorDiv.style.cssText = 'padding: 10px; background: #f8d7da; color: #721c24; border-radius: 5px; margin: 10px;';
    errorDiv.textContent = message;
    chatMessages.appendChild(errorDiv);
  }
}

// Make all functions globally available immediately
window.startChat = startChat;
window.closeChat = closeChat;
window.sendMessage = sendMessage;
window.initializeMessaging = initializeMessaging;
window.loadConversation = loadConversation;
window.displayMessages = displayMessages;
window.displayMessage = displayMessage;
window.refreshMessages = refreshMessages;

console.log('✓ messaging.js loaded - All functions registered globally');
console.log('  - startChat:', typeof window.startChat);
console.log('  - closeChat:', typeof window.closeChat);
console.log('  - sendMessage:', typeof window.sendMessage);
console.log('  - initializeMessaging:', typeof window.initializeMessaging);
