@extends('layouts.app')

@section('title', 'Users - Muxg Chat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Community Members
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Connect with members in our community
            </p>
        </div>

        <div id="users-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div class="text-center py-12 col-span-full">
                <div class="card p-8">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-600 dark:text-gray-400">Loading users...</p>
                </div>
            </div>
        </div>
        
        <div id="pagination-container" class="mt-8"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
});

async function loadUsers(page = 1) {
    try {
        const response = await makeAuthenticatedRequest(`/api/users?page=${page}`);
        
        if (response.ok) {
            const data = await response.json();
            displayUsers(data.users);
            displayPagination(data.pagination);
        } else {
            document.getElementById('users-container').innerHTML = `
                <div class="text-center py-12 col-span-full">
                    <div class="card p-8">
                        <svg class="mx-auto h-16 w-16 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Failed to load users</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">There was an error loading the user list</p>
                        <button onclick="loadUsers()" class="btn-primary">Try Again</button>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading users:', error);
        document.getElementById('users-container').innerHTML = `
            <div class="text-center py-12 col-span-full">
                <div class="card p-8">
                    <svg class="mx-auto h-16 w-16 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Connection Error</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Unable to connect to the server</p>
                    <button onclick="loadUsers()" class="btn-primary">Retry</button>
                </div>
            </div>
        `;
    }
}

function displayUsers(users) {
    const container = document.getElementById('users-container');
    
    if (users.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12 col-span-full">
                <div class="card p-8">
                    <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No users found</h3>
                    <p class="text-gray-600 dark:text-gray-400">There are no registered users yet</p>
                </div>
            </div>
        `;
        return;
    }
    
    const usersHtml = users.map(user => `
        <div class="card hover:shadow-lg transition-shadow duration-200">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-xl font-bold text-white">
                        ${user.username.charAt(0).toUpperCase()}
                    </span>
                </div>
                
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                    ${user.username}
                    ${user.id === window.Laravel?.userId ? '<span class="text-sm text-blue-600 dark:text-blue-400 ml-1">(You)</span>' : ''}
                </h3>
                
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    <div class="flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Joined ${new Date(user.created_at).toLocaleDateString()}
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = usersHtml;
}

function displayPagination(pagination) {
    const container = document.getElementById('pagination-container');
    
    if (pagination.last_page <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let paginationHtml = `
        <div class="flex items-center justify-between bg-white dark:bg-gray-800 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg">
            <div class="flex items-center space-x-2">
    `;
    
    if (pagination.current_page > 1) {
        paginationHtml += `
            <button onclick="loadUsers(${pagination.current_page - 1})" 
                    class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md transition-colors duration-200">
                Previous
            </button>
        `;
    }
    
    paginationHtml += `</div>`;
    
    paginationHtml += `
        <div class="text-sm text-gray-700 dark:text-gray-300">
            Page ${pagination.current_page} of ${pagination.last_page}
        </div>
    `;
    
    paginationHtml += `<div class="flex items-center space-x-2">`;
    
    if (pagination.current_page < pagination.last_page) {
        paginationHtml += `
            <button onclick="loadUsers(${pagination.current_page + 1})" 
                    class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md transition-colors duration-200">
                Next
            </button>
        `;
    }
    
    paginationHtml += `</div></div>`;
    
    container.innerHTML = paginationHtml;
}
</script>
@endsection