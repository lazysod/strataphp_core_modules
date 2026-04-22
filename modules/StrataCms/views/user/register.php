<?php
/**
 * CMS-Themed User Registration Page
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers.php';
if (!config('modules')['cms']['enabled']) {
    require __DIR__ . '/../../../user/views/register.php';
    return;
}

use App\Modules\Cms\UserThemeWrapper;

ob_start();
?>
<div class="d-flex justify-content-center align-items-center" style="min-height: 60vh;">
  <div class="cms-content w-100" style="max-width: 480px;">
    <h2 class="mb-4 text-center">Create Account</h2>
    <form id="userRegisterForm" method="post" action="/user/register">
      <input type="hidden" name="token" value="<?= htmlspecialchars(\App\TokenManager::csrf()) ?>">
      <div class="mb-4">
        <label for="display_name" class="form-label">Display Name</label>
        <input class="form-control" id="display_name" name="display_name" type="text" placeholder="Your display name" required />
      </div>
      <div class="mb-4">
        <label for="email" class="form-label">Email Address</label>
        <input class="form-control" id="email" name="email" type="email" placeholder="your@email.com" required />
      </div>
      <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <input class="form-control" id="password" name="password" type="password" placeholder="Enter a secure password" required />
      </div>
      <div class="mb-4">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input class="form-control" id="confirm_password" name="confirm_password" type="password" placeholder="Confirm your password" required />
      </div>
      <button class="btn btn-primary w-100" type="submit" id="submitButton">🚀 Create Account</button>
    </form>
    <div class="mt-4 text-center" style="font-size: 0.95rem;">
      <p>Already have an account? <a href="/user/login">Login here</a></p>
      <p style="color: var(--text-light);">
        By registering, you agree to our <a href="/terms">Terms of Service</a> 
        and <a href="/privacy">Privacy Policy</a>.
      </p>
    </div>
  </div>
</div>
<?php
$page['content'] = ob_get_clean();
UserThemeWrapper::renderUserPage('User Registration', $page['content'], [
    'description' => 'Create a new account to access all features',
    'title' => 'User Registration | Strata PHP: CMS',
]);
?>