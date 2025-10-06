# 🚀 Muxg Chat - Real-time Messaging Platform

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-13+-316192?style=for-the-badge&logo=postgresql)](https://postgresql.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![WebSockets](https://img.shields.io/badge/WebSockets-Real--time-00D8FF?style=for-the-badge)](https://laravel.com/docs/reverb)

A modern, production-ready real-time messaging platform built with Laravel 12, featuring WebSocket-powered live chat, elegant UI with dark/light themes, and comprehensive user management.

## ✨ Features

### 🔥 Core Features
- **Real-time Messaging** - Instant message delivery using Laravel Reverb WebSockets
- **Multi-room Chat** - Create and join public/private chat rooms
- **Message Reactions** - React to messages with emojis (👍 ❤️ 😂 😮 😢 😠)
- **User Presence** - See who's online in real-time
- **Responsive Design** - Works seamlessly on desktop, tablet, and mobile

### 🎨 Modern UI/UX
- **Dark/Light Theme Toggle** - User preference with localStorage persistence
- **Tailwind CSS 4** - Modern, utility-first styling
- **Roboto Font** - Clean, professional typography
- **Smooth Animations** - Loading states, transitions, and micro-interactions
- **Mobile-First** - Responsive design optimized for all devices

### 🛡️ Security & Authentication
- **Session-based Auth** - Secure login/registration system
- **Bearer Token Support** - Dual authentication for web and API
- **PostgreSQL** - Robust database with multi-schema architecture
- **UUID Primary Keys** - Enhanced security and scalability

### 🔧 Developer Experience
- **Clean Architecture** - Separate concerns with proper MVC structure
- **API-First Design** - RESTful APIs with comprehensive endpoints
- **Real-time Broadcasting** - Laravel Echo + Pusher integration
- **Modern PHP 8.2+** - Latest language features and type declarations

## 🏗️ Architecture

### Database Schema
```
auth schema:
├── users (authentication)
└── sessions (user sessions)

public schema:
├── rooms (chat rooms)
├── messages (chat messages)
├── reactions (message reactions)
└── room_members (user-room relationships)
```

### Tech Stack
- **Backend**: Laravel 12.x, PHP 8.2+
- **Database**: PostgreSQL 13+
- **Frontend**: Tailwind CSS 4, Alpine.js, Vite
- **Real-time**: Laravel Reverb, Laravel Echo, Pusher
- **Testing**: PHPUnit, Laravel Dusk

## 🚀 Quick Start

### Prerequisites
- **PHP 8.2+** with extensions: `pdo_pgsql`, `mbstring`, `openssl`, `bcmath`, `json`
- **Composer 2.x**
- **Node.js 18+** and npm
- **PostgreSQL 13+**
- **Git**

### 1. Clone & Install

```bash
# Clone the repository
git clone https://github.com/yourusername/muxg.git
cd muxg

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Database Configuration

Edit your `.env` file with your PostgreSQL credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=muxg
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Separate auth schema connection
DB_AUTH_CONNECTION=auth
DB_AUTH_HOST=127.0.0.1
DB_AUTH_PORT=5432
DB_AUTH_DATABASE=muxg
DB_AUTH_USERNAME=your_username
DB_AUTH_PASSWORD=your_password
```

### 4. Database Setup

```bash
# Create database schemas
createdb muxg
psql -d muxg -c "CREATE SCHEMA IF NOT EXISTS auth;"
psql -d muxg -c "CREATE SCHEMA IF NOT EXISTS public;"

# Run migrations
php artisan migrate
```

### 5. WebSocket Configuration

```bash
# Configure Reverb for real-time features
php artisan install:broadcasting

# Update your .env with Reverb settings
echo "
REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
" >> .env
```

### 6. Build Assets

```bash
# Build frontend assets
npm run build

# Or for development with hot reloading
npm run dev
```

### 7. Start Development Servers

```bash
# Option 1: Use the convenient dev command (runs all services)
composer run dev

# Option 2: Start services manually
php artisan serve &           # Laravel app (port 8000)
php artisan reverb:start &    # WebSocket server (port 8080)
php artisan queue:work &      # Queue worker
npm run dev &                 # Vite dev server
```

Visit `http://localhost:8000` to access the application! 🎉

## 🧪 Testing

```bash
# Run all tests
composer run test

# Run specific test suites
php artisan test --filter=Feature
php artisan test --filter=Unit

# Run with coverage
php artisan test --coverage
```

## 📁 Project Structure

```
muxg/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/          # API endpoints
│   │   └── Web/          # Web controllers
│   ├── Models/           # Eloquent models
│   ├── Events/           # Broadcasting events
│   └── Middleware/       # Custom middleware
├── database/
│   ├── migrations/       # Database migrations
│   └── seeders/         # Database seeders
├── resources/
│   ├── views/           # Blade templates
│   ├── js/              # JavaScript files
│   └── css/             # Stylesheets
├── routes/
│   ├── web.php          # Web routes
│   ├── api.php          # API routes
│   └── channels.php     # Broadcasting channels
└── public/              # Public assets
```

## 🔧 Development

### Adding New Features

1. **API Endpoints**: Add to `routes/api.php` and create controllers in `app/Http/Controllers/Api/`
2. **Web Pages**: Add to `routes/web.php` and create controllers in `app/Http/Controllers/Web/`
3. **Real-time Events**: Create events in `app/Events/` and configure channels in `routes/channels.php`
4. **Database Changes**: Create migrations with `php artisan make:migration`

### Code Style

This project follows PSR-12 coding standards with Laravel Pint:

```bash
# Format code
./vendor/bin/pint

# Check formatting
./vendor/bin/pint --test
```

### Debugging

```bash
# View logs in real-time
php artisan pail

# Clear caches during development
php artisan optimize:clear
```

## 🚀 Deployment

### Production Setup

1. **Environment**: Set `APP_ENV=production` and `APP_DEBUG=false`
2. **Database**: Use managed PostgreSQL service (AWS RDS, DigitalOcean, etc.)
3. **WebSockets**: Configure Pusher or deploy Reverb with proper infrastructure
4. **Queue**: Set up Redis and configure queue workers
5. **Assets**: Run `npm run build` for optimized production assets

### Docker Support

```bash
# Build and run with Docker
docker-compose up -d

# Run migrations in container
docker-compose exec app php artisan migrate
```

## 🤝 Contributing

We welcome contributions! Please follow these steps:

### Getting Started

1. **Fork** the repository
2. **Clone** your fork: `git clone https://github.com/yourusername/muxg.git`
3. **Create a branch**: `git checkout -b feature/amazing-feature`
4. **Install dependencies**: `composer install && npm install`
5. **Set up environment**: Copy `.env.example` to `.env` and configure
6. **Run tests**: `composer run test`

### Development Workflow

1. **Write Tests**: Add tests for new features
2. **Follow Standards**: Use Laravel conventions and PSR-12
3. **Update Documentation**: Update README and code comments
4. **Test Thoroughly**: Ensure all tests pass
5. **Submit PR**: Create a pull request with clear description

### Contribution Areas

- 🐛 **Bug Fixes**: Report and fix issues
- ✨ **New Features**: Chat enhancements, UI improvements
- 📚 **Documentation**: Improve guides and code comments
- 🧪 **Testing**: Add more comprehensive tests
- 🎨 **Design**: UI/UX improvements and accessibility
- ⚡ **Performance**: Optimization and scaling improvements

## 📝 API Documentation

### Authentication
```http
POST /api/auth/login
POST /api/auth/register
POST /api/auth/logout
```

### Rooms
```http
GET    /api/rooms              # List rooms
POST   /api/rooms              # Create room
GET    /api/rooms/{id}         # Room details
POST   /api/rooms/{id}/members # Join room
DELETE /api/rooms/{id}/members/{userId} # Leave room
```

### Messages
```http
GET  /api/rooms/{id}/messages           # Get messages
POST /api/rooms/{id}/messages           # Send message
POST /api/messages/{id}/reactions/toggle # Toggle reaction
```

### WebSocket Events
- `message.new` - New message in room
- `reaction.new` - New reaction on message
- `user.joined` - User joined room
- `user.left` - User left room

## 🆘 Support

### Common Issues

**Database Connection Error**
```bash
# Check PostgreSQL service
sudo systemctl status postgresql

# Verify connection
psql -h localhost -U username -d muxg
```

**WebSocket Not Working**
```bash
# Check Reverb server
php artisan reverb:start --debug

# Verify port availability
netstat -tulpn | grep :8080
```

**Permission Issues**
```bash
# Fix Laravel permissions
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Getting Help

- 📖 **Documentation**: Check this README and inline code comments
- 🐛 **Issues**: [Create an issue](https://github.com/yourusername/muxg/issues) for bugs
- 💬 **Discussions**: [Start a discussion](https://github.com/yourusername/muxg/discussions) for questions
- 📧 **Email**: Contact maintainers for security issues

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- **Laravel Team** - For the amazing framework
- **Tailwind CSS** - For the utility-first CSS framework
- **Pusher** - For real-time WebSocket infrastructure
- **Contributors** - Thank you to all who help improve this project!

---

<div align="center">

**⭐ Star this repo if you find it helpful!**

[Report Bug](https://github.com/yourusername/muxg/issues) • [Request Feature](https://github.com/yourusername/muxg/issues) • [Contribute](CONTRIBUTING.md)

</div>
