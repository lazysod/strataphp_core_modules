# Links Module

## Overview
The Links module provides a Linktree-style landing page functionality, displaying a collection of important links in an organized, mobile-friendly format. Perfect for social media bio links, personal portfolios, or business link collections.

## Features
- Linktree-style link display
- Database-driven link management
- About page with bio information
- Mobile-friendly responsive design
- Link ordering and organization
- Adult content warning support
- Integration with admin panel for link management

## Installation
The Links module is automatically discovered by StrataPHP. To enable:

### Via Admin Interface (Recommended)
1. Navigate to `/admin/modules` 
2. Find the Links module in the list
3. Check the enable checkbox and click "Save Changes"

### Via Configuration
Enable the links module in `config.php`:

```php
'modules' => [
    'links' => [
        'enabled' => true
    ]
]
```

### Database Setup
Run migrations to create the links table:

```bash
php bin/migrate.php
```

### Module Validation
The Links module passes all StrataPHP validation checks:
- ✅ Required files and structure present
- ✅ Security best practices implemented  
- ✅ Proper error handling and documentation
- ✅ Framework convention compliance

## Configuration

### Setting as Default Module
To make this your site's homepage:

```php
'default_module' => 'links'
```

Or use the admin interface at `/admin/modules` to select default module.

### Database Schema
The links table includes:
- `id` - Primary key
- `title` - Link display text
- `url` - Target URL
- `description` - Optional description
- `order` - Display order
- `created_at` - Creation timestamp
- `updated_at` - Last modified timestamp

## Usage

### Accessing the Links Page
- Visit `/links` for direct access
- Visit `/` when set as default module
- Visit `/links/about` for the about page

### Managing Links
Links are managed through the admin interface:
1. Login to `/admin`
2. Navigate to the Links section
3. Add, edit, or reorder links as needed

## Routes
- `GET /links` - Main links page
- `GET /links/about` - About page with bio
- `GET /` - Main page (when set as default module)

## File Structure
```
links/
├── controllers/
│   └── LinksController.php      # Main controller logic
├── views/
│   ├── links.php               # Main links display
│   └── about.php               # About page
├── index.php                   # Module entry point
└── routes.php                  # Route definitions
```

## Development

### Customizing the Links Display
Edit `views/links.php` to modify:
- Layout and styling
- Link formatting
- Additional information display
- Mobile responsiveness

### Customizing the About Page
Edit `views/about.php` and update the controller:
```php
public function about()
{
    $bio = 'Your custom bio content here';
    // Or load from database:
    // $bio = $db->query("SELECT bio FROM settings WHERE id = 1")[0]['bio'];
    include __DIR__ . '/../views/about.php';
}
```

### Adding New Features
1. **Social Media Icons**: Add social links to the about page
2. **Link Analytics**: Track click counts for each link
3. **Categories**: Group links by category
4. **Custom Styling**: Add themes or custom CSS

## Database Integration
The module uses the `Links` model from the admin module:

```php
use App\Modules\Admin\Models\Links;

$db = new DB($config['db']);
$linksModel = new Links($db, $config);
$links = $linksModel->getAll();
```

## Styling and Theming
The module uses the default StrataPHP theme. To customize:

1. Create custom CSS for link styling
2. Modify the view templates
3. Add JavaScript for interactive features
4. Consider mobile-first responsive design

## Security Features
- Input sanitization for URLs and descriptions
- SQL injection prevention through prepared statements
- XSS protection for display content

## Extension Ideas
- **Link Categories**: Organize links into groups
- **Click Tracking**: Monitor link performance
- **Social Media Integration**: Display social media feeds
- **Custom Themes**: Multiple visual styles
- **QR Code Generation**: For easy mobile sharing
- **Link Validation**: Check for broken links
- **Scheduling**: Show/hide links based on time
- **User Profiles**: Multiple link collections

## Dependencies
- StrataPHP framework
- Admin module (for Links model)
- Database connection

## Best Practices
- Keep link titles concise and descriptive
- Use descriptive URLs
- Regularly check for broken links
- Optimize for mobile viewing
- Include essential contact/social links
- Maintain consistent link ordering

## Framework Integration

### Module Management
- **Admin Interface**: Full management available at `/admin/links`
- **Module Control**: Enable/disable via `/admin/modules` 
- **Validation**: Meets all StrataPHP security and quality standards
- **Generator**: Recreate using `php bin/create-module.php links`

### StrataPHP Features Used
- ✅ Database migrations for schema management
- ✅ CSRF protection on all forms
- ✅ Input validation and sanitization
- ✅ Error handling with try-catch blocks
- ✅ PHPDoc documentation throughout
- ✅ Framework routing conventions

## Navigation Config Example

To add the Links module to your admin navigation, add this to `adminNavConfig.php`:

```php
[
    'label' => 'Links',
    'icon' => 'fa-link',
    'url' => '/admin/links',
    'show' => true
]
```

## Troubleshooting

### Links Not Displaying
1. Verify database connection
2. Check if links table exists and has data
3. Ensure module is enabled in configuration

---

*This module is part of the StrataPHP framework. For more information about module development, admin interface usage, and validation standards, see the main framework documentation.*
4. Check for PHP errors in logs

### Admin Panel Access
1. Ensure admin module is enabled
2. Create admin user if needed
3. Check admin authentication

### Mobile Display Issues
1. Test responsive design on various devices
2. Check CSS media queries
3. Verify touch-friendly link sizing