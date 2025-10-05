@extends('layouts.app')

@section('title', 'Create Room - Muxg Chat')

@section('content')
@extends('layouts.app')

@section('title', 'Create Room - Muxg Chat')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-8">
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <a href="{{ route('rooms.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Create New Room
                </h1>
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                Set up a new chat room for your community
            </p>
        </div>

        <div class="card p-8">
            <form method="POST" action="{{ route('rooms.store') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Room Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}" 
                        required
                        maxlength="255"
                        class="input @error('name') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                        placeholder="Enter a descriptive room name"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Choose a clear, memorable name for your room
                    </p>
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="4"
                        maxlength="1000"
                        class="input @error('description') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                        placeholder="Describe what this room is about (optional)"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Help others understand the purpose of this room
                    </p>
                </div>
                
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="flex flex-col sm:flex-row gap-4 sm:justify-end">
                        <a href="{{ route('rooms.index') }}" class="btn-secondary text-center">
                            Cancel
                        </a>
                        <button type="submit" class="btn-primary">
                            Create Room
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('create-room-form');
    form.addEventListener('submit', handleCreateRoom);
});

async function handleCreateRoom(event) {
    event.preventDefault();
    
    // Clear previous errors
    clearErrors();
    
    const formData = new FormData(event.target);
    const data = {
        name: formData.get('name'),
        description: formData.get('description'),
        visibility: formData.get('visibility')
    };
    
    try {
        const response = await makeAuthenticatedRequest('/api/rooms', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert('Room created successfully!');
            window.location.href = `/rooms/${result.room.id}`;
        } else {
            if (result.errors) {
                displayErrors(result.errors);
            } else {
                alert(`Error: ${result.message}`);
            }
        }
    } catch (error) {
        console.error('Error creating room:', error);
        alert('Error creating room');
    }
}

function clearErrors() {
    document.getElementById('name-error').textContent = '';
    document.getElementById('description-error').textContent = '';
    document.getElementById('visibility-error').textContent = '';
}

function displayErrors(errors) {
    if (errors.name) {
        document.getElementById('name-error').textContent = errors.name[0];
    }
    if (errors.description) {
        document.getElementById('description-error').textContent = errors.description[0];
    }
    if (errors.visibility) {
        document.getElementById('visibility-error').textContent = errors.visibility[0];
    }
}
</script>
@endsection