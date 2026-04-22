# Cookie Banner Plugin

A simple plugin for StrataPHP CMS to display a cookie consent banner.

## Features
- Customizable message
- Configurable cookie name and duration
- Optional link to a privacy/read more page
- Customizable button text and styles
- Easy integration in any theme

## Usage
1. Place the `CookieBanner.php`, `config.php`, and `index.php` in `modules/cookiebanner/`.
2. In your theme or layout, add:

```php
$cookieConfig = include __DIR__ . '/modules/cookiebanner/config.php';
$banner = new \App\Modules\CookieBanner\CookieBanner($cookieConfig);
echo $banner->render();
```

3. Edit `config.php` to change the message, cookie name, duration, button text, styles, or read more link.

## Configuration Options (`config.php`)
| Key           | Type   | Description                                                      | Default Value                        |
|---------------|--------|------------------------------------------------------------------|--------------------------------------|
| cookie_name   | string | Name of the consent cookie                                       | 'cookie_consent'                     |
| cookie_length | int    | Cookie duration in days                                          | 365                                  |
| message       | string | Banner message                                                   | 'We use 🍪 cookies ...'               |
| read_more_url | string | URL for privacy/read more link                                   | '/privacy'                           |
| button_text   | string | Text for the accept button                                       | 'Accept'                             |
| banner_style  | string | Inline CSS for the banner container                             | see config.php for default           |
| button_style  | string | Inline CSS for the accept button                                | see config.php for default           |

## Example `config.php`
```php
return [
    'cookie_name' => 'cookie_consent',
    'cookie_length' => 365, // days
    'message' => 'We use 🍪 cookies to ensure you get the best experience on our website.',
    'read_more_url' => '/privacy',
    'button_text' => 'Accept',
    'banner_style' => 'position:fixed;bottom:0;left:0;width:100%;background:#222;color:#fff;padding:18px 10px;z-index:9999;text-align:center;box-shadow:0 -2px 8px rgba(0,0,0,0.15);',
    'button_style' => 'margin-left:20px;padding:8px 18px;background:#ffd700;color:#222;border:none;border-radius:4px;cursor:pointer;font-weight:bold;',
];
```
