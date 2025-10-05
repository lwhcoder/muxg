@extends('layouts.app')

@section('title', 'Profile - Muxg Chat')

@section('content')
<div>
    <h2>Your Profile</h2>
    
    <div id="profile-info">
        Loading profile...
    </div>
    
    <div id="edit-profile" style="margin-top: 30px;">
        <h3>Edit Profile</h3>
        <form id="profile-form">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required maxlength="255">
                <div id="username-error" class="error"></div>
            </div>
            
            <div>
                <label for="password">New Password (leave blank to keep current):</label>
                <input type="password" id="password" name="password" minlength="6">
                <div id="password-error" class="error"></div>
            </div>
            
            <div>
                <label for="avatar">Avatar URL:</label>
                <input type="url" id="avatar" name="avatar">
                <div id="avatar-error" class="error"></div>
            </div>
            
            <div>
                <button type="submit">Update Profile</button>
            </div>
        </form>
    </div>
    
    <div style="margin-top: 30px;">
        <h3>Danger Zone</h3>
        <button onclick="deleteAccount()" style="background: red; color: white;">Delete Account</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadProfile();
    
    const form = document.getElementById('profile-form');
    form.addEventListener('submit', handleUpdateProfile);
});

async function loadProfile() {
    try {
        const response = await makeAuthenticatedRequest('/api/auth/me');
        
        if (response.ok) {
            const data = await response.json();
            displayProfile(data.user);
            populateForm(data.user);
        } else {
            document.getElementById('profile-info').innerHTML = 'Failed to load profile';
        }
    } catch (error) {
        console.error('Error loading profile:', error);
        document.getElementById('profile-info').innerHTML = 'Error loading profile';
    }
}

function displayProfile(user) {
    document.getElementById('profile-info').innerHTML = `
        <div style="display: flex; align-items: center;">
            <img src="${user.avatar}" alt="${user.username}" width="80" height="80" style="border-radius: 50%; margin-right: 20px;">
            <div>
                <h3>${user.username}</h3>
                <p>Member since ${new Date(user.created_at).toLocaleDateString()}</p>
            </div>
        </div>
    `;
}

function populateForm(user) {
    document.getElementById('username').value = user.username;
    document.getElementById('avatar').value = user.avatar;
}

async function handleUpdateProfile(event) {
    event.preventDefault();
    
    // Clear previous errors
    clearErrors();
    
    const formData = new FormData(event.target);
    const data = {
        username: formData.get('username'),
        avatar: formData.get('avatar')
    };
    
    // Only include password if provided
    const password = formData.get('password');
    if (password) {
        data.password = password;
    }
    
    try {
        const response = await makeAuthenticatedRequest(`/api/users/${window.Laravel.userId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert('Profile updated successfully!');
            loadProfile(); // Reload to show updated info
            document.getElementById('password').value = ''; // Clear password field
        } else {
            if (result.errors) {
                displayErrors(result.errors);
            } else {
                alert(`Error: ${result.message}`);
            }
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        alert('Error updating profile');
    }
}

function clearErrors() {
    document.getElementById('username-error').textContent = '';
    document.getElementById('password-error').textContent = '';
    document.getElementById('avatar-error').textContent = '';
}

function displayErrors(errors) {
    if (errors.username) {
        document.getElementById('username-error').textContent = errors.username[0];
    }
    if (errors.password) {
        document.getElementById('password-error').textContent = errors.password[0];
    }
    if (errors.avatar) {
        document.getElementById('avatar-error').textContent = errors.avatar[0];
    }
}

async function deleteAccount() {
    if (!confirm('Are you sure you want to delete your account? This action cannot be undone!')) {
        return;
    }
    
    if (!confirm('This will permanently delete all your messages and data. Are you absolutely sure?')) {
        return;
    }
    
    try {
        const response = await makeAuthenticatedRequest(`/api/users/${window.Laravel.userId}`, {
            method: 'DELETE'
        });
        
        if (response.ok) {
            alert('Account deleted successfully');
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
        } else {
            const error = await response.json();
            alert(`Failed to delete account: ${error.message}`);
        }
    } catch (error) {
        console.error('Error deleting account:', error);
        alert('Error deleting account');
    }
}
</script>
@endsection