@extends('layouts.app')

@section('title', 'Dashboard - Muxg Chat')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="text-center py-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            Welcome to Muxg Chat, {{ auth()->user()->username }}!
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-300">
            Connect, chat, and collaborate with your community
        </p>
    </div>
    
    <!-- Quick Actions -->
    <div class="card p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('rooms.index') }}" class="group p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors duration-200">
                <div class="text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 group-hover:text-blue-500 dark:group-hover:text-blue-400 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <h3 class="font-medium text-gray-900 dark:text-white">Browse Rooms</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Explore and join chat rooms</p>
                </div>
            </a>
            
            <a href="{{ route('rooms.create') }}" class="group p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors duration-200">
                <div class="text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 group-hover:text-blue-500 dark:group-hover:text-blue-400 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <h3 class="font-medium text-gray-900 dark:text-white">Create New Room</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Start your own chat room</p>
                </div>
            </a>
            
            <a href="{{ route('users.index') }}" class="group p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors duration-200">
                <div class="text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 group-hover:text-blue-500 dark:group-hover:text-blue-400 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <h3 class="font-medium text-gray-900 dark:text-white">View All Users</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Connect with community members</p>
                </div>
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Your Recent Rooms -->
        <div class="card p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Your Recent Rooms</h2>
            <div id="user-rooms" class="space-y-3">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    <span class="ml-2 text-gray-500 dark:text-gray-400">Loading your rooms...</span>
                </div>
            </div>
        </div>
        
        <!-- Online Users -->
        <div class="card p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Online Users</h2>
            <div id="online-users" class="space-y-3">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    <span class="ml-2 text-gray-500 dark:text-gray-400">Loading online users...</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load user's rooms
    loadUserRooms();
    
    // Set up real-time presence
    setupPresence();
});

async function loadUserRooms() {
    try {
        const response = await makeAuthenticatedRequest(`/api/users/${window.Laravel.userId}/rooms`);
        
        if (response.ok) {
            const data = await response.json();
            displayUserRooms(data.rooms);
        } else {
            document.getElementById('user-rooms').innerHTML = 'Failed to load rooms';
        }
    } catch (error) {
        console.error('Error loading rooms:', error);
        document.getElementById('user-rooms').innerHTML = 'Error loading rooms';
    }
}

function displayUserRooms(rooms) {
    const container = document.getElementById('user-rooms');
    
    if (rooms.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 mb-4">You haven't joined any rooms yet.</p>
                <a href="/rooms" class="btn-primary">Browse rooms to get started!</a>
            </div>
        `;
        return;
    }
    
    const roomsHtml = rooms.map(room => `
        <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h4 class="font-medium text-gray-900 dark:text-white">
                        <a href="/rooms/${room.id}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
                            ${room.name}
                        </a>
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">${room.description}</p>
                    <small class="text-xs text-gray-500 dark:text-gray-400">
                        Joined: ${new Date(room.joined_at).toLocaleDateString()}
                    </small>
                </div>
                <div class="ml-4">
                    <a href="/rooms/${room.id}" class="btn-ghost text-xs">
                        Enter Room
                    </a>
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = roomsHtml;
}

function setupPresence() {
    if (window.Echo) {
        // Join global online users channel
        window.Echo.join('online-users')
            .here((users) => {
                displayOnlineUsers(users);
            })
            .joining((user) => {
                addOnlineUser(user);
            })
            .leaving((user) => {
                removeOnlineUser(user);
            });
    }
}

function displayOnlineUsers(users) {
    const container = document.getElementById('online-users');
    
    if (users.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">No other users online</p>
            </div>
        `;
        return;
    }
    
    const usersHtml = users.map(user => `
        <div id="online-user-${user.id}" class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg">
            <img src="${user.avatar}" alt="${user.username}" class="w-8 h-8 rounded-full mr-3">
            <div class="flex-1">
                <span class="font-medium text-gray-900 dark:text-white">${user.username}</span>
                <div class="flex items-center mt-1">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Online</span>
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = usersHtml;
}

function addOnlineUser(user) {
    const container = document.getElementById('online-users');
    const userElement = document.createElement('div');
    userElement.id = `online-user-${user.id}`;
    userElement.className = 'flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg animate-fade-in';
    userElement.innerHTML = `
        <img src="${user.avatar}" alt="${user.username}" class="w-8 h-8 rounded-full mr-3">
        <div class="flex-1">
            <span class="font-medium text-gray-900 dark:text-white">${user.username}</span>
            <div class="flex items-center mt-1">
                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Online</span>
            </div>
        </div>
    `;
    container.appendChild(userElement);
}

function removeOnlineUser(user) {
    const userElement = document.getElementById(`online-user-${user.id}`);
    if (userElement) {
        userElement.remove();
    }
}
</script>
@endsection