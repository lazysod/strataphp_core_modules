# Admin Module

## Overview
The Admin module provides comprehensive administrative functionality for the StrataPHP framework, including module management, user administration, link management, and system monitoring.

## Features

### Module Management
- **Dual-View Interface**: Switch between table and card views for module display
- **Enable/Disable Modules**: Toggle module activation with real-time feedback
- **Module Validation**: Built-in quality and security checks for all modules
- **Safe Module Deletion**: Automatic backups before module removal
- **Module Details**: View comprehensive information about each module
- **Bulk Operations**: Select and modify multiple modules simultaneously

### Links Management
- **CRUD Operations**: Add, edit, delete, and reorder links from the admin panel
- **Icon Auto-Detection**: FontAwesome icon detection for popular domains
- **Drag & Drop Ordering**: Intuitive link reordering interface
- **URL Validation**: Ensures all links are properly formatted
- **Adult Content Support**: NSFW marking and confirmation dialogs

### User Administration
- **User Management**: Create, edit, suspend, and delete user accounts
- **Session Monitoring**: View and manage active user sessions
- **Device Tracking**: Monitor login devices and locations
- **Bulk Operations**: Perform actions on multiple users

### System Administration
- **Configuration Management**: Update system settings through web interface
- **Security Monitoring**: Track authentication events and security logs
- **Database Management**: Monitor database performance and integrity
- **Backup Systems**: Automated backups for critical operations

## Admin Interface Routes

### Module Management (`/admin/modules`)
- **View Modules**: See all installed and available modules
- **Enable/Disable**: Toggle module activation status
- **Validate Modules**: Run security and quality checks
- **Delete Modules**: Remove modules with automatic backup

### Links Management (`/admin/links`)
- **Manage Links**: Full CRUD operations for link management
- **Reorder Links**: Drag and drop interface for link ordering
- **Icon Management**: Auto-detection and manual icon selection

### User Management (`/admin/users`)
- **User Administration**: Complete user account management
- **Session Management**: Monitor and control user sessions
- **Security Actions**: Suspend, unsuspend, and delete accounts

## Installation & Enabling

The Admin module is automatically installed and cannot be disabled (core system module).

### Access Requirements
- Admin authentication required for all admin routes
- Session-based security with device tracking
- CSRF protection on all forms
- Automatic session timeout and security logging

## Development & Extension

### Adding New Admin Features
1. Create controllers in `controllers/` directory
2. Add routes to `routes.php`
3. Create views in `views/` directory
4. Follow security best practices (authentication, CSRF, validation)

### Security Guidelines
- All admin actions require authentication verification
- Use CSRF tokens on all forms
- Validate and sanitize all input
- Log security-relevant events
- Implement proper error handling

## Navigation Config Example

To add the Admin module to your admin navigation, add this to `adminNavConfig.php`:

```php
[
    'label' => 'Admin',
    'icon' => 'fa-cogs',
    'url' => '/admin',
    'show' => true
]
```
