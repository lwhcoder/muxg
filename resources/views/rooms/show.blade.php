@extends('layouts.app')

@section('title', 'Chat Room - Muxg Chat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-screen flex flex-col">
    <!-- Room Header -->
    <div id="room-header" class="py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex-1">
                <h2 id="room-name" class="text-2xl font-bold text-gray-900 dark:text-white">
                    Loading...
                </h2>
                <div id="room-info" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    <!-- Room description and info will be populated here -->
                </div>
            </div>
            <div id="room-actions" class="flex items-center gap-3">
                <button onclick="leaveRoom()" class="btn-secondary text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Leave Room
                </button>
                <a href="{{ route('rooms.index') }}" class="btn-primary text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Rooms
                </a>
            </div>
        </div>
    </div>
    
    <!-- Chat Content -->
    <div id="room-content" class="flex-1 flex flex-col lg:flex-row gap-4 py-4 min-h-0">
        <!-- Chat Area -->
        <div id="chat-area" class="flex-1 flex flex-col min-h-0">
            <!-- Messages Container -->
            <div id="messages-container" class="flex-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg p-4 overflow-y-auto mb-4 min-h-0">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                    <span class="text-gray-600 dark:text-gray-400">Loading messages...</span>
                </div>
            </div>
            
            <!-- Message Input -->
            <form id="message-form" class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                <div class="flex gap-3">
                    <input 
                        type="text" 
                        id="message-input" 
                        placeholder="Type your message..." 
                        maxlength="2000"
                        class="flex-1 input"
                        required
                    >
                    <button type="submit" class="btn-primary px-6">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Members Sidebar -->
        <div id="members-sidebar" class="w-full lg:w-64 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
                Members
            </h3>
            <div id="members-list" class="space-y-2">
                <div class="flex items-center justify-center py-4">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-2"></div>
                    <span class="text-gray-600 dark:text-gray-400 text-sm">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentRoomId = '{{ $roomId ?? "" }}';
let currentUser = {
    id: window.Laravel.userId,
    username: window.Laravel.username
};
let messagesContainer, messageForm, messageInput;

document.addEventListener('DOMContentLoaded', function() {
    // Get room ID from URL if not provided
    if (!currentRoomId) {
        currentRoomId = window.location.pathname.split('/').pop();
    }
    
    messagesContainer = document.getElementById('messages-container');
    messageForm = document.getElementById('message-form');
    messageInput = document.getElementById('message-input');
    
    // Load initial data
    loadRoomInfo();
    loadMessages();
    loadMembers();
    
    // Set up message form
    messageForm.addEventListener('submit', handleSendMessage);
    
    // Set up real-time features
    setupRealTimeChat();
    
    // Auto-scroll to bottom on load
    setTimeout(() => {
        scrollToBottom();
    }, 1000);
});

async function loadRoomInfo() {
    try {
        const response = await makeAuthenticatedRequest(`/api/rooms/${currentRoomId}`);
        
        if (response.ok) {
            const data = await response.json();
            displayRoomInfo(data.room);
        } else {
            document.getElementById('room-name').textContent = 'Error loading room';
        }
    } catch (error) {
        console.error('Error loading room:', error);
        document.getElementById('room-name').textContent = 'Error loading room';
    }
}

function displayRoomInfo(room) {
    document.getElementById('room-name').textContent = room.name;
    document.getElementById('room-info').innerHTML = `
        <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
            ${room.description ? `<span>${room.description}</span>` : ''}
            <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
                ${room.members_count} ${room.members_count === 1 ? 'member' : 'members'}
            </span>
            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-xs">${room.visibility}</span>
        </div>
    `;
    document.title = `${room.name} - Muxg Chat`;
}

async function loadMessages() {
    try {
        const response = await makeAuthenticatedRequest(`/api/rooms/${currentRoomId}/messages`);
        
        if (response.ok) {
            const data = await response.json();
            displayMessages(data.messages);
        } else {
            messagesContainer.innerHTML = `
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400">Failed to load messages</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading messages:', error);
        messagesContainer.innerHTML = `
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-600 dark:text-gray-400">Error loading messages</p>
            </div>
        `;
    }
}

function displayMessages(messages) {
    if (messages.length === 0) {
        messagesContainer.innerHTML = `
            <div class="text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-6a2 2 0 012-2h8z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No messages yet</h3>
                <p class="text-gray-600 dark:text-gray-400">Be the first to say something!</p>
            </div>
        `;
        return;
    }
    
    const messagesHtml = messages.map(message => createMessageHtml(message)).join('');
    messagesContainer.innerHTML = messagesHtml;
    scrollToBottom();
}

function createMessageHtml(message) {
    const isOwnMessage = message.user.id === currentUser.id;
    
    // Handle cases where reactions might be undefined or empty
    const messageReactions = message.reactions || {};
    const userReactions = message.user_reactions || [];
    
    const reactions = Object.entries(messageReactions).map(([type, count]) => 
        `<button onclick="toggleReaction('${message.id}', '${type}')" 
                class="inline-flex items-center px-2 py-1 rounded-full text-xs transition-colors duration-200 ${userReactions.includes(type) ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'}">
            ${getReactionEmoji(type)} ${count}
        </button>`
    ).join('');
    
    return `
        <div id="message-${message.id}" class="mb-4 ${isOwnMessage ? 'flex justify-end' : 'flex justify-start'}">
            <div class="max-w-xs lg:max-w-md">
                <div class="${isOwnMessage ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'} rounded-lg px-4 py-2">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-xs font-bold text-white">
                                ${message.user.username.charAt(0).toUpperCase()}
                            </span>
                        </div>
                        <span class="text-xs font-medium ${isOwnMessage ? 'text-blue-100' : 'text-gray-600 dark:text-gray-400'}">
                            ${message.user.username}
                        </span>
                        <span class="text-xs ${isOwnMessage ? 'text-blue-200' : 'text-gray-500 dark:text-gray-500'}">
                            ${new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </span>
                    </div>
                    <div class="text-sm">${message.content}</div>
                </div>
                ${reactions || Object.keys(messageReactions).length > 0 ? `
                    <div class="flex items-center gap-1 mt-2 flex-wrap">
                        ${reactions}
                        <button onclick="showReactionPicker('${message.id}')" 
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </button>
                    </div>
                ` : `
                    <div class="mt-2">
                        <button onclick="showReactionPicker('${message.id}')" 
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            React
                        </button>
                    </div>
                `}
            </div>
        </div>
    `;
}

async function loadMembers() {
    try {
        const response = await makeAuthenticatedRequest(`/api/rooms/${currentRoomId}/members`);
        
        if (response.ok) {
            const data = await response.json();
            displayMembers(data.members);
        } else {
            document.getElementById('members-list').innerHTML = `
                <div class="text-center py-4">
                    <svg class="mx-auto h-8 w-8 text-red-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Failed to load</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading members:', error);
        document.getElementById('members-list').innerHTML = `
            <div class="text-center py-4">
                <svg class="mx-auto h-8 w-8 text-red-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-gray-600 dark:text-gray-400">Error loading</p>
            </div>
        `;
    }
}

function displayMembers(members) {
    const membersHtml = members.map(member => `
        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                <span class="text-sm font-bold text-white">
                    ${member.username.charAt(0).toUpperCase()}
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    ${member.username}
                    ${member.id === currentUser.id ? '<span class="text-xs text-blue-600 dark:text-blue-400 ml-1">(You)</span>' : ''}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                    Online
                </p>
            </div>
        </div>
    `).join('');
    
    document.getElementById('members-list').innerHTML = membersHtml;
}

async function handleSendMessage(event) {
    event.preventDefault();
    
    const content = messageInput.value.trim();
    if (!content) return;
    
    // Disable input while sending
    const submitButton = event.target.querySelector('button[type="submit"]');
    const originalButtonHTML = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = `
        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
    `;
    
    try {
        const response = await makeAuthenticatedRequest(`/api/rooms/${currentRoomId}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ content })
        });
        
        if (response.ok) {
            const messageData = await response.json();
            messageInput.value = '';
            // Add the new message immediately to the UI
            addNewMessage(messageData.data);
        } else {
            const error = await response.json();
            alert(`Failed to send message: ${error.message}`);
        }
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Error sending message');
    } finally {
        // Re-enable button
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonHTML;
    }
}

function setupRealTimeChat() {
    if (window.Echo) {
        console.log('Setting up real-time chat with Laravel Echo');
        // Join the room channel for real-time updates
        window.Echo.join(`room.${currentRoomId}`)
            .here((users) => {
                console.log('Users currently in room:', users);
            })
            .joining((user) => {
                console.log('User joined:', user);
                loadMembers(); // Refresh members list
            })
            .leaving((user) => {
                console.log('User left:', user);
                loadMembers(); // Refresh members list
            })
            .listen('.message.new', (event) => {
                console.log('Received new message event:', event);
                addNewMessage(event.message);
            })
            .listen('.reaction.new', (event) => {
                console.log('Received new reaction event:', event);
                updateMessageReactions(event.reaction);
            });
    } else {
        console.log('Laravel Echo not available, using manual updates only');
    }
}

function addNewMessage(message) {
    // Check if message already exists to avoid duplicates
    const existingMessage = document.getElementById(`message-${message.id}`);
    if (existingMessage) {
        console.log('Message already exists, skipping duplicate:', message.id);
        return; // Message already exists, don't add again
    }
    
    console.log('Adding new message to UI:', message.id);
    const messageHtml = createMessageHtml(message);
    messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
    scrollToBottom();
}

function updateMessageReactions(reaction) {
    // This would update the reaction display for the specific message
    // For now, just reload the messages to show updated reactions
    loadMessages();
}

async function toggleReaction(messageId, type) {
    try {
        const response = await makeAuthenticatedRequest(`/api/messages/${messageId}/reactions/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ type })
        });
        
        if (response.ok) {
            // Reaction will be updated via real-time event
        } else {
            console.error('Failed to toggle reaction');
        }
    } catch (error) {
        console.error('Error toggling reaction:', error);
    }
}

function showReactionPicker(messageId) {
    const reactions = ['like', 'love', 'laugh', 'wow', 'sad', 'angry'];
    
    // Remove existing picker if any
    const existingPicker = document.getElementById('reaction-picker');
    if (existingPicker) {
        existingPicker.remove();
    }
    
    const picker = reactions.map(type => 
        `<button onclick="toggleReaction('${messageId}', '${type}'); document.getElementById('reaction-picker').remove();" 
                class="flex flex-col items-center p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
            <span class="text-2xl mb-1">${getReactionEmoji(type)}</span>
            <span class="text-xs text-gray-600 dark:text-gray-400 capitalize">${type}</span>
        </button>`
    ).join('');
    
    // Create modal overlay
    const popup = document.createElement('div');
    popup.id = 'reaction-picker';
    popup.innerHTML = `
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="this.remove()">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-sm w-full p-6" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Choose a reaction</h4>
                    <button onclick="document.getElementById('reaction-picker').remove()" 
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    ${picker}
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(popup);
}

function getReactionEmoji(type) {
    const emojis = {
        'like': '👍',
        'love': '❤️',
        'laugh': '😂',
        'wow': '😮',
        'sad': '😢',
        'angry': '😠'
    };
    return emojis[type] || type;
}

async function leaveRoom() {
    if (!confirm('Are you sure you want to leave this room?')) return;
    
    try {
        const response = await makeAuthenticatedRequest(`/api/rooms/${currentRoomId}/members/${currentUser.id}`, {
            method: 'DELETE'
        });
        
        if (response.ok) {
            alert('Left room successfully');
            window.location.href = '/rooms';
        } else {
            const error = await response.json();
            alert(`Failed to leave room: ${error.message}`);
        }
    } catch (error) {
        console.error('Error leaving room:', error);
        alert('Error leaving room');
    }
}

function scrollToBottom() {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Auto-scroll when new messages arrive
const observer = new MutationObserver(() => {
    scrollToBottom();
});
observer.observe(messagesContainer, { childList: true });
</script>
@endsection