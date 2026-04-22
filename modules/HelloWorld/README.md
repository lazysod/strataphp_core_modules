# Example Module: HelloWorld

This is a minimal example of a custom module for the Strata Framework.

## Directory Structure

```
modules/
  helloworld/
    controllers/
      HelloWorldController.php
    models/
      HelloWorld.php
    views/
      hello.php
    assets/
      style.css
    README.md
```

## HelloWorldController.php
```php
<?php
class HelloWorldController {
    public function index() {
        include __DIR__ . '/../views/hello.php';
    }
}
```

## HelloWorld.php (Model)
```php
<?php
class HelloWorld {
    public function getMessage() {
        return "Hello, world!";
    }
}
```

## hello.php (View)
```php
<?php
$model = new HelloWorld();
echo '<h1>' . htmlspecialchars($model->getMessage()) . '</h1>';
```

## style.css
```css
h1 { color: #007bff; }
```

## README.md (for your module)
```
# HelloWorld Module

A simple example module for the Strata Framework.

- Place this folder in `public_html/modules/helloworld/`
- Access via route/controller as configured
```

---

This skeleton can be copied and renamed for your own modules. Follow the main framework README for best practices on DB, config, security, and more.
