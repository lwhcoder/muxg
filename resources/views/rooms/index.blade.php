@extends('layouts.app')

@section('title', 'Chat Rooms - Muxg Chat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" style="color: white !important;">
    <div class="py-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Chat Rooms
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Join conversations and connect with others
                </p>
            </div>
            <a href="{{ route('rooms.create') }}" class="btn-primary inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create Room
            </a>
        </div>

        @if($rooms->isEmpty())
            <div class="text-center py-12">
                <div class="card p-8">
                    <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-6a2 2 0 012-2h8z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        No rooms available
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Get started by creating the first chat room
                    </p>
                    <a href="{{ route('rooms.create') }}" class="btn-primary inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Create Your First Room
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($rooms as $room)
                    <div class="card hover:shadow-lg transition-shadow duration-200">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $room->name }}
                                </h3>
                                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                    </svg>
                                    {{ $room->members_count ?? 0 }} {{ Str::plural('member', $room->members_count ?? 0) }}
                                </div>
                            </div>
                            
                            @if($room->description)
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-2">
                                    {{ $room->description }}
                                </p>
                            @endif
                            
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Created {{ $room->created_at->diffForHumans() }}
                                </div>
                                <a href="{{ route('rooms.show', $room) }}" class="btn-secondary text-sm">
                                    Join Room
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($rooms->hasPages())
                <div class="mt-8">
                    {{ $rooms->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection