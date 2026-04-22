# Home Module

## Overview
The Home module provides the main landing page functionality for the StrataPHP framework. This is a minimal module that serves as the default homepage when no other specific module is set as the default.

## Features
- Simple homepage routing
- Can serve as default module for root URL (`/`)
- Minimal footprint for fast loading
- Easily customizable for different homepage needs

## Installation
The Home module is automatically discovered by StrataPHP. To enable:

1. Set as default module in your configuration (optional)
2. Routes are automatically loaded from `routes.php`

## Configuration

### Setting as Default Module
To make this the homepage for your site, configure in `config.php`:

```php
'default_module' => 'home',
'modules' => [
    'home' => [
        'enabled' => true
    ]
]
```

## Usage

### Accessing the Home Page
- Visit `/` when set as default module
- Visit `/home` for direct access

## Routes
- `GET /` - Homepage (when set as default module)
- `GET /home` - Direct access to home page

## File Structure
```
home/
└── routes.php       # Route definitions only
```

**Note**: This is a minimal module with only route definitions. Controllers and views would need to be added for custom functionality.

## Development

### Adding Controllers
To add functionality to the home module:

1. Create a `controllers/` directory
2. Add a `HomeController.php` with your logic:
   ```php
   <?php
   namespace App\Modules\Home\Controllers;
   
   class HomeController
   {
       public function index()
       {
           // Your homepage logic here
           include __DIR__ . '/../views/home.php';
       }
   }
   ```

3. Update `routes.php` to use the controller:
   ```php
   $router->get('/home', [HomeController::class, 'index']);
   ```

### Adding Views
1. Create a `views/` directory
2. Add your homepage template files
3. Reference them from your controller

### Adding Models
1. Create a `models/` directory
2. Add any data models needed for homepage content

## Extension Ideas
- Hero section with call-to-action
- Latest blog posts or news
- Feature highlights
- Company information
- Social media integration
- Newsletter signup
- Dynamic content from database

## Dependencies
- StrataPHP framework
- No external dependencies

## Best Practices
- Keep the homepage lightweight for fast loading
- Include clear navigation to other sections
- Optimize for SEO with proper meta tags
- Make it responsive for mobile devices
- Include essential company/site information

## Framework Integration

### Module Management
- **Admin Interface**: Enable/disable via `/admin/modules`
- **Validation**: Passes all StrataPHP quality and security checks
- **Generator**: Can be recreated using `php bin/create-module.php home`

### StrataPHP Features
- ✅ Automatic route discovery
- ✅ Framework convention compliance  
- ✅ CSRF protection ready
- ✅ Error handling implementation
- ✅ Documentation standards

## Customization Examples

### Simple Static Homepage
```php
// controllers/HomeController.php
public function index()
{
    $page_title = 'Welcome to Our Site';
    $meta_description = 'Your site description here';
    include __DIR__ . '/../views/home.php';
}
```

### Dynamic Homepage with Database Content
```php
// controllers/HomeController.php
public function index()
{
    $db = new DB($config['db']);
    $recent_posts = $db->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 3");
    $featured_content = $db->query("SELECT * FROM content WHERE featured = 1");
    
    include __DIR__ . '/../views/home.php';
}
```

---

*This module is part of the StrataPHP framework. For more information about module development, validation, and management, see the main framework documentation.*