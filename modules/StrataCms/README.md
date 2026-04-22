# ⚠️ Media Module Dependency

The CMS module requires the Media module to be enabled for all image and media management features (uploads, media library, modal integration, etc.).

If the Media module is disabled:
- Media features in the CMS will be unavailable
- A warning will be shown in the admin panel
- All other CMS features will continue to work

**To enable:**
- Set `'media' => ['enabled' => true]` in `app/modules.php`
- Ensure the Media module is listed in the CMS `module.json` under `requires`

**Integration points:**
- Media manager modal in page editor
- Image uploads and selection
- Media library UI

# StrataPHP CMS Module

A professional Content Management System module for the StrataPHP framework, providing robust page and content management with seamless enable/disable functionality and a modern Bootstrap-based UI.


## 🔄 CMS Toggle Feature

**NEW**: The CMS module can be enabled or disabled at any time without breaking your site or losing content.

### **When CMS is Enabled** (`'enabled' => true`)
- Modern CMS themes for all user/admin pages
- Professional admin dashboard at `/admin/cms`
- Dynamic page routing and content management
- Image uploads with thumbnail generation
- SEO tools and rich text editing

### **When CMS is Disabled** (`'enabled' => false`)
- Graceful fallback to default StrataPHP themes
- Site continues working with basic admin panel
- Zero data loss – all content preserved
- Instant revert capability

**Perfect for:** Testing, gradual adoption, or switching between CMS and basic modes.

## 🚀 Quick Start

### **Accessing the CMS**

1. **Admin Dashboard**: `/admin/cms`
   - Main CMS dashboard with statistics and overview
   - Quick access to all CMS features

2. **Page Management**: `/admin/cms/pages`
   - View all pages (published, draft, private)
   - Create, edit, and delete pages

3. **Create New Page**: `/admin/cms/pages/create`
   - Add new pages with full editor
   - Set SEO metadata and status


### **Public Access**

- **Homepage**: `/`  
   Displays the page with slug 'home' or the first published page (created during installation)
- **Dynamic Pages**: `/page/{slug}` or `/{slug}`  
   Any published page is accessible via its slug (SEO-friendly URLs)
- **API Access**: `/api/cms/pages`  
   RESTful API for headless CMS usage (JSON responses)

## 📋 Prerequisites

- **Authentication Required**: You must be logged in as an admin to access CMS features
- **Database**: Migration automatically creates required tables
- **User Module**: CMS depends on the user module for authentication

## 🔧 Installation & Setup

### 1. **Enable the Module**
Configure the CMS module in `/app/config.php`:

```php
'modules' => [
    'cms' => [
        'enabled' => true,  // Set to false to disable CMS gracefully
        'suitable_as_default' => false,
    ],
]
```

**Toggle Benefits:**
- **Enable**: Get modern CMS themes, professional admin interface, dynamic routing
- **Disable**: Automatic fallback to default StrataPHP themes with zero breaking changes
- **Switch anytime**: No data loss, instant revert capability

### 2. **Run Database Migration**
```bash
php bin/migrate.php
```
This creates all necessary CMS tables and default content.

### 3. **Access Admin Panel**
- Go to `/admin/admin_login.php` to log in
- Navigate to `/admin/cms` to start using the CMS


## 📖 Features

### Page Management
- Create, edit, and delete pages
- Draft, published, and private status
- SEO metadata (title, description, keywords)
- URL slug generation
- Content hierarchy (parent/child pages)
- Menu order management

### Content System
- Rich content editing
- Excerpt support
- Featured images
- Dynamic routing

### Blog/Posts System (Ready for extension)
- Database structure for posts
- Categories and tags
- Author attribution
- View tracking

### Menu Management (Database ready)
- Menu creation system
- Hierarchical menu items
- Custom URL support

### API & Headless
- REST API endpoints
- JSON responses
- Headless CMS capabilities


## 🌐 Available Routes

### Admin Routes (Requires Authentication)
```
GET  /admin/cms                    - CMS Dashboard
GET  /admin/cms/pages              - List all pages
GET  /admin/cms/pages/create       - Create page form
POST /admin/cms/pages/create       - Store new page
GET  /admin/cms/pages/{id}/edit    - Edit page form
POST /admin/cms/pages/{id}/edit    - Update page
POST /admin/cms/pages/{id}/delete  - Delete page
```

### Public Routes
```
GET  /                            - Homepage
GET  /page/{slug}                 - View page by slug
GET  /{slug}                      - Dynamic page (fallback)
```

### API Routes
```
GET  /api/cms/pages               - Get all pages (JSON)
GET  /api/cms/pages/{slug}        - Get page by slug (JSON)
```


## 🎨 Theming

The CMS integrates with StrataPHP's theme system:

1. **Theme Templates**: `/themes/{theme}/page.php`
2. **Module Fallback**: `/modules/cms/views/page.php`
3. **Built-in Fallback**: Simple HTML output

**Template Variables Available:**
```php
$title            // Page title
$content          // Page content (HTML)
$meta_description // SEO description
$page             // Full page array
```

## 📊 Database Schema

The migration creates these tables:

### **cms_pages**
- Complete page management with SEO, hierarchy, and status
- Slug-based routing with automatic generation
- Template and featured image support

### **cms_posts**
- Blog/news system ready for implementation
- Category association and tag support
- View tracking and publication dates

### **cms_categories**
- Hierarchical category system
- SEO-friendly slugs

### **cms_menus & cms_menu_items**
- Dynamic menu creation
- Hierarchical menu structure
- Custom URLs and page linking

### **cms_content_revisions**
- Version history for content changes
- Author tracking for revisions


## 🔒 Security Features

- SQL injection protection
- Input validation and sanitization
- Authentication checks on admin routes
- Direct access prevention
- Error handling and logging


## 🚀 Getting Started Tutorial

### Step 1: Create Your First Page
1. Log in to the admin panel: `/admin/admin_login.php`
2. Go to CMS: `/admin/cms`
3. Click "Manage Pages" or go to `/admin/cms/pages`
4. Click "Create New Page"
5. Fill in page details (Title, Content, Status)
6. Save the page

### Step 2: View Your Page
- Visit `/about-us` to see your new page (slug auto-generated)

### Step 3: Customize Homepage
1. Go to `/admin/cms/pages`
2. Edit the "Welcome to StrataPHP" page
3. Update content with your site information
4. Save changes
5. Visit `/` to see the updated homepage


## 🎯 Next Steps

1. **Extend with Blog**: Implement blog functionality using the `cms_posts` table
2. **Menu Management**: Build admin interface for menu creation
3. **Theme Integration**: Create custom page templates
4. **Content Blocks**: Add reusable content components
5. **Media Library**: Integrate file upload and management


## 💡 Tips

- SEO: Always fill in meta descriptions for better search engine optimization
- Slugs: Keep slugs short and descriptive for better URLs
- Content: Use proper HTML structure for better styling
- Status: Use "Draft" for work-in-progress content


## 🆘 Troubleshooting

### Can't Access Admin
- Ensure you're logged in: `/admin/admin_login.php`
- Check user permissions in the database

### Pages Not Showing
- Verify page status is "published"
- Check if CMS module is enabled in config
- Ensure routes are properly loaded

### Database Errors
- Run migrations: `php bin/migrate.php`
- Check database connection in config

---

The **StrataPHP CMS Module** provides a solid, modern foundation for content management and can be extended to meet specific project requirements. Happy content managing! 🎉