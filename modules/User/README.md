# User Module

## Overview
The User module provides comprehensive user authentication and management functionality for the StrataPHP framework. It includes user registration, login, profile management, password reset, email verification, and session management.

**NEW**: The User module now features **adaptive theming** that automatically switches between CMS-enhanced themes and default StrataPHP themes based on CMS module availability.

## Features
- User registration with email verification
- Secure login/logout functionality
- User profile management
- Password reset via email
- Session management and tracking
- User activation system
- Email testing utilities
- CSRF protection
- Input validation and sanitization
- Secure password hashing
- **Adaptive theming system** - CMS-enhanced or default themes
- **Smart redirects** - Admin users to appropriate dashboards
- **Graceful fallbacks** - Works with or without CMS module

## Theming System

### **When CMS Module is Enabled**
- ✅ Modern CMS themes for all user pages (login, register, password reset)
- ✅ Professional styling with gradient backgrounds and responsive design
- ✅ Integrated navigation and branding
- ✅ Admin users redirect to `/admin/cms`
- ✅ Regular users redirect to `/user/profile`

### **When CMS Module is Disabled**  
- ✅ Automatic fallback to default StrataPHP themes
- ✅ Basic styling maintains functionality
- ✅ Admin users redirect to `/admin`
- ✅ Regular users redirect to `/user/profile`
- ✅ Zero configuration required

## Installation
The User module is automatically discovered by StrataPHP. To enable:

### Via Admin Interface (Recommended)
1. Navigate to `/admin/modules`
2. Find the User module and enable it via the interface
3. Configure email settings if needed

### Via Configuration
Enable the user module in `config.php`:

```php
'modules' => [
    'user' => [
        'enabled' => true
    ]
]
```

### Database Setup
Run migrations to create user tables:

```bash
php bin/migrate.php
```

### Module Validation
The User module meets all StrataPHP standards:
- ✅ Security best practices (password hashing, CSRF protection)
- ✅ Comprehensive error handling and logging
- ✅ Input validation and sanitization
- ✅ Framework convention compliance
- ✅ Complete documentation and code comments

## Configuration

### Module Configuration
Enable the user module in `config.php`:

```php
'modules' => [
    'user' => [
        'enabled' => true
    ]
]
```

### Email Configuration
Configure email settings for user verification and password reset:

```php
'mail' => [
    'host' => 'smtp.gmail.com',
    'username' => 'your-email@gmail.com',
    'password' => 'your-app-password',
    'port' => 587,
    'encryption' => 'tls',
    'from_email' => 'your-email@gmail.com',
    'from_name' => 'Your Site Name'
]
```

### Database Setup
Run migrations to create required tables:

```bash
php bin/migrate.php
```

Required tables:
- `users` - User account information
- `user_sessions` - Session tracking
- `migrations` - Migration tracking

## Usage

### User Registration
- Visit `/user/register` to create new account
- Email verification required (if configured)
- Automatic login after successful registration

### User Login
- Visit `/user/login` to access account
- Session-based authentication
- Remember me functionality
- Failed login attempt tracking

### Profile Management
- Visit `/user/profile` to update account info
- Change password functionality
- Update personal information
- View account statistics

### Password Reset
- Visit `/user/reset-request` to request password reset
- Email with reset link sent to user
- Visit `/user/reset` with token to set new password

## Routes
- `GET/POST /user/login` - User login
- `GET/POST /user/register` - User registration
- `GET/POST /user/profile` - User profile management
- `GET/POST /user/reset-request` - Password reset request
- `GET/POST /user/reset` - Password reset with token
- `GET/POST /user/email-test` - Email functionality testing
- `GET /` - Login page (when set as default module)

## File Structure
```
user/
├── controllers/
│   ├── EmailTestController.php          # Email testing utilities
│   ├── UserActivateController.php       # User activation
│   ├── UserLoginController.php          # Login/logout logic
│   ├── UserProfileController.php        # Profile management
│   ├── UserRegisterController.php       # User registration
│   ├── UserResetController.php          # Password reset
│   ├── UserResetRequestController.php   # Reset request handling
│   └── UserSessionsController.php       # Session management
├── views/                               # User interface templates
├── index.php                           # Module entry point
└── routes.php                          # Route definitions
```

## Development

### User Authentication
The module provides comprehensive authentication:

```php
// Check if user is logged in
$config = include __DIR__ . '/../../app/config.php';
$sessionPrefix = $config['session_prefix'] ?? 'app_';
if (isset($_SESSION[$sessionPrefix . 'user_id'])) {
    // User is authenticated
}

// Login user
$_SESSION[$sessionPrefix . 'user_id'] = $user['id'];
$_SESSION[$sessionPrefix . 'username'] = $user['username'];
```

### Session Management
Sessions are tracked with device and IP information:
- Session creation and expiration
- Device fingerprinting
- IP address tracking
- Session cleanup utilities

### Security Features
- Password hashing with PHP's `password_hash()`
- CSRF token protection
- Input sanitization and validation
- SQL injection prevention
- Session hijacking protection
- Email verification for new accounts

## API Integration
The user module can be extended to work with APIs:
- JWT token generation
- API authentication middleware
- OAuth integration
- Social media login

## Customization

### Custom User Fields
Add additional user fields:
1. Create migration for new columns
2. Update registration form and controller
3. Modify profile management

### Custom Authentication
Extend authentication logic:
1. Override controller methods
2. Add custom validation rules
3. Implement additional security measures

### Email Templates
Customize email templates:
1. Create custom email templates
2. Update controller email logic
3. Add HTML email support

## Security Best Practices
- Always validate and sanitize user input
- Use prepared statements for database queries
- Implement rate limiting for login attempts
- Use secure session configuration
- Enable HTTPS for production
- Regular security audits
- Strong password requirements

## Extension Ideas
- **Two-Factor Authentication**: SMS or app-based 2FA
- **Social Login**: Facebook, Google, Twitter integration
- **User Roles**: Admin, moderator, user permissions
- **Account Lockout**: Temporary lockout after failed attempts
- **Password History**: Prevent password reuse
- **Login Analytics**: Track login patterns
- **Email Preferences**: Notification settings
- **Account Deletion**: Self-service account removal

## Dependencies
- StrataPHP framework
- PHPMailer for email functionality
- Database connection
- Session management

## Troubleshooting

### Registration Issues
1. Check email configuration
2. Verify database tables exist
3. Check validation rules
4. Review error logs

### Login Problems
1. Verify password hashing method
2. Check session configuration
3. Ensure database connectivity
4. Review CSRF token handling

### Email Not Sending
1. Test email configuration
2. Check SMTP settings
3. Verify email templates
4. Check firewall restrictions

### Session Issues
1. Check session storage
2. Verify session configuration
3. Check cookie settings
4. Review session cleanup

## Testing
Use the email test functionality:
- Visit `/user/email-test`
- Verify email configuration
- Test email delivery
- Debug SMTP issues