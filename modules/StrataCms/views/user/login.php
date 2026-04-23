<?php
/**
 * CMS-Themed User Login Page
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers.php';

use App\Modules\Cms\UserThemeWrapper;

if (!config('modules')['cms']['enabled']) {
    require __DIR__ . '/../../../user/views/login.php';
    return;
}

ob_start();
?>
<div class="d-flex justify-content-center align-items-center" style="min-height: 60vh;">
  <div class="cms-content w-100" style="max-width: 420px;">
    <h2 class="mb-4 text-center">Sign In</h2>
    <form id="userLoginForm" method="post" action="/user/login">
      <input type="hidden" name="token" value="<?= htmlspecialchars(\App\TokenManager::csrf()) ?>">
      <div class="mb-4">
        <label for="email" class="form-label">Email Address</label>
        <input class="form-control" id="email" name="email" type="email" placeholder="your@email.com" required />
      </div>
      <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <input class="form-control" id="password" name="password" type="password" placeholder="Enter your password" required />
      </div>
      <button class="btn btn-primary w-100" type="submit" id="submitButton">🔐 Sign In</button>
    </form>
    <div class="mt-4 text-center" style="font-size: 0.95rem;">
      <p>Don't have an account? <a href="/user/register">Register here</a></p>
      <p><a href="/user/reset-request">Forgot your password?</a></p>
    </div>
  </div>
</div>
<?php
$page['content'] = ob_get_clean();
UserThemeWrapper::renderUserPage('Sign In', $page['content'], [
    'description' => 'Sign in to your account to access all features',
    'title' => 'Sign In | Strata PHP: CMS',
    'slug' => 'login',
    'noindex' => true
]);
?>