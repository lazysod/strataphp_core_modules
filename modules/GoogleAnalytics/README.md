# Google Analytics Module

A simple module for StrataPHP to embed Google Analytics tracking code using a Measurement ID.

## Features

- Admin UI to set and update your Google Analytics Measurement ID
- Secure file-based storage (no database required)
- Automatically embeds the correct tracking code on your site

## Installation

1. Copy the module to your `public_html/modules/` directory.
2. Enable the module in your admin panel or in `app/config.php`.
3. Visit the admin settings page to enter your Measurement ID.

## Configuration

The Measurement ID is stored in a secure JSON file:

```
storage/settings/google_analytics.json
```

Example contents:

```json
{
    "measurement_id": "G-XXXXXXXXXX"
}
```

## Usage

Once set, the module will automatically embed the Google Analytics tracking code on your site using the provided Measurement ID.

No database table or migration is required for this module.

## Customization

1. Modify the model in `models/GoogleAnalytics.php`
2. Update views in `views/` directory
3. Add custom routes in `routes.php`
4. Update database schema as needed

## Navigation Config Example

To add the Google Analytics module to your admin navigation, add this to `adminNavConfig.php`:

```php
[
    'label' => 'Google Analytics',
    'icon' => 'fa-chart-line',
    'url' => '/admin/google-analytics-settings',
    'show' => true
]
```

## License

Same as StrataPHP framework.