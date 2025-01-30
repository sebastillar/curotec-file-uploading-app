# File Upload and Collaboration Platform

A modern file upload and collaboration platform built with Laravel 10 and Vue 3, featuring real-time updates and secure file management.

## 🚀 Features

### Core Features

- Secure file upload with drag-and-drop functionality
- Real-time collaboration and file sharing
- File versioning system
- Access control and permissions management

### Technical Features

- Laravel 10 backend with RESTful API
- Vue 3 frontend with Composition API
- Real-time updates using Laravel WebSockets
- Secure file storage with Laravel Media Library
- PostgreSQL database for robust data management

## 🛠️ Technical Requirements

### Backend

- Laravel 10+
- PostgreSQL
- PHP 8.2+

### Frontend

- Vue 3
- Node.js 18+

### Required Packages

- laravel/sanctum
- pusher/pusher-php-server
- spatie/laravel-medialibrary

## Required PHP Extensions

The following PHP extensions must be installed:

- pdo_pgsql (for PostgreSQL database)
- exif (for Media Library)
- sodium (for encryption)
- fileinfo (for file handling)
- gd (for image processing)

These extensions are installed in the Docker container via the Dockerfile

## 📋 Prerequisites

- Docker and Docker Compose
- Git
- Composer
- Node.js (18+)
- NPM or Yarn

## ⚙️ Installation

1. Clone the repository:

   - `git clone https://github.com/sebastillar/curotec-file-uploading-app.git`
   - `cd curotec-file-uploading-app`

2. Copy environment files:

   - `cp .env.example .env`

3. Start Docker containers:

   - `docker compose up -d`

4. Install PHP dependencies:

   - `docker compose exec app composer install`

5. Install Node.js dependencies:

   - `docker compose run --rm node npm install`

6. Run database migrations:

   - `docker compose exec app php artisan migrate`

7. Build frontend assets:
   - `docker compose run --rm node npm run build`

## 🔒 Security Measures

- File validation and sanitization
- Secure file storage implementation
- Role-based access control
- API authentication using Sanctum
- CSRF protection
- XSS prevention

## ⚡ Performance Optimizations

- Chunked file uploads for large files
- Efficient database indexing
- Caching implementation
- Optimized real-time updates
- Lazy loading of components

## Database Setup

### Migrations and Seeding

Run the following commands to set up your database:

- `docker compose exec app php artisan migrate`
- `docker compose exec app php artisan db:seed`

## 🧪 Testing

Run PHP tests:

- `docker compose exec app php artisan test`

Run JavaScript tests:

- `docker compose run --rm node npm run test`

## 📝 Development Guidelines

### Coding Standards

- PSR-12 for PHP code
- Vue.js Style Guide for frontend
- TypeScript for type safety
- ESLint and Prettier for code formatting

### Git Workflow

- Feature branch workflow
- Conventional commits
- Pull request reviews
- Automated testing on PR

## 🚧 Known Limitations

- Maximum file size: 100MB
- Supported file types: Images, PDFs, Office documents
- Concurrent users limit: 10 per file
- Browser support: Modern browsers only (Chrome, Firefox, Safari, Edge)

## 🤝 Contributing

1. Create a feature branch:

   - `git checkout -b feature/your-feature-name`

2. Make your changes following our coding standards

3. Write clear commit messages:

   - `git commit -m "feat: add file upload functionality"`

4. Push your branch and create a Pull Request

## 📚 Additional Documentation

- [API Documentation](docs/api.md)
- [Development Setup](docs/development.md)
- [Testing Guide](docs/testing.md)
- [Deployment Guide](docs/deployment.md)

## 🔍 Architectural Decisions

### Database Design

- Normalized schema for efficient data management
- Soft deletes for data recovery
- Optimized indexes for performance

### File Storage

- Secure file handling using Media Library
- Efficient storage organization
- Automatic file cleanup

### Real-time Implementation

- WebSocket implementation for live updates
- Real-time file status updates
- User presence tracking

## ⚠️ Challenges and Solutions

1. **Large File Uploads**

   - Implemented chunked uploads
   - Progress tracking
   - Resumable uploads

2. **Real-time Collaboration**

   - WebSocket implementation
   - Conflict resolution
   - State management

3. **Performance**
   - Optimized queries
   - Efficient caching
   - Load balancing consideration

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
