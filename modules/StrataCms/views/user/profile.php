<?php
/**
 * User Profile Page
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/helpers.php';
if (!config('modules')['cms']['enabled']) {
    require __DIR__ . '/../../../user/views/profile.php';
    return;
}

use App\Modules\Cms\UserThemeWrapper;

ob_start();
?>
<div class="d-flex justify-content-center align-items-center" style="min-height: 60vh;">
  <div class="cms-content w-100" style="max-width: 540px;">
    <h2 class="mb-4 text-center">Your Profile</h2>
    <form method="post" action="" enctype="multipart/form-data">
      <div class="mb-3 text-center">
        <label class="form-label">Avatar</label><br>
        <?php
        $avatarPath = $user['avatar'] ?? '';
        if ($avatarPath && file_exists($_SERVER['DOCUMENT_ROOT'] . $avatarPath)) {
            echo '<img src="' . htmlspecialchars($config['base_url'] ?? '') . htmlspecialchars($avatarPath) . '" alt="Avatar" class="rounded-circle mb-2" style="width:80px;height:80px;object-fit:cover;">';
        } else {
            $gravatar = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user['email'] ?? ''))) . '?s=80&r=g&d=mm';
            echo '<img src="' . htmlspecialchars($gravatar, ENT_QUOTES, 'UTF-8') . '" alt="Avatar" class="rounded-circle mb-2" style="width:80px;height:80px;object-fit:cover;">';
        }
        ?>
        <input type="file" name="avatar" accept="image/png,image/jpeg,image/jpg,image/webp" class="form-control mt-2" style="max-width:300px;margin:auto;">
        <small class="text-muted">Allowed: PNG, JPG, JPEG, WEBP. Max 2MB.</small>
      </div>
      <div class="form-floating mb-3">
        <input class="form-control" id="display_name" name="display_name" type="text" value="<?php echo htmlspecialchars($user['display_name'] ?? '') ?>"  />
        <label for="display_name">Display Name <span style="color:red">*</span></label>
      </div>
      <div class="form-floating mb-3">
        <input class="form-control" id="first_name" name="first_name" type="text" value="<?php echo htmlspecialchars($user['first_name'] ?? '') ?>" required />
        <label for="first_name">First Name <span style="color:red">*</span></label>
      </div>
      <div class="form-floating mb-3">
        <input class="form-control" id="second_name" name="second_name" type="text" value="<?php echo htmlspecialchars($user['second_name'] ?? '') ?>" required />
        <label for="second_name">Second Name <span style="color:red">*</span></label>
      </div>
      <div class="form-floating mb-3">
        <input class="form-control" id="email" name="email" type="email" value="<?php echo htmlspecialchars($user['email'] ?? '') ?>" required />
        <label for="email">Email address <span style="color:red">*</span></label>
      </div>
      <div class="form-floating mb-3">
        <input class="form-control" id="pwd" name="pwd" type="password" placeholder="New password (leave blank to keep current)" />
        <label for="pwd">New Password</label>
      </div>
      <div class="form-floating mb-3">
        <input class="form-control" id="pwd2" name="pwd2" type="password" placeholder="Confirm new password" />
        <label for="pwd2">Confirm New Password</label>
      </div>
      <div class="d-grid"><button class="btn btn-primary btn-lg" type="submit">Update Profile</button></div>
    </form>
  </div>
</div>
<?php
$page['content'] = ob_get_clean();
UserThemeWrapper::renderUserPage('Your Profile', $page['content'], [
    'description' => 'Manage your user profile and account settings.',
    'title' => 'Your Profile | Strata PHP: CMS',
    'slug' => 'user/profile',
    'noindex' => true
]);
?>
