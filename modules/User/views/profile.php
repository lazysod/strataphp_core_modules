<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap.php';
require dirname(__DIR__, 3) . '/views/partials/header.php';
use App\App;

?>
<section class="py-5">
    <div class="container px-5">
        <div class="bg-light rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="row gx-5 justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <?php if (!empty($success)) : ?>
                        <div class="alert alert-success text-center alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($error)) : ?>
                        <div class="alert alert-danger text-center alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <form method="post" action="" enctype="multipart/form-data">
                        <div class="mb-3 text-center">
                            <label class="form-label text-white">Account Avatar</label><br>
                            <?php
                            $avatarPath = $user['avatar'] ?? '';
                            if ($avatarPath && file_exists($_SERVER['DOCUMENT_ROOT'] . $avatarPath)) {
                                echo '<img src="' . htmlspecialchars($avatarPath) . '" alt="Avatar" class="rounded-circle mb-2" style="width:80px;height:80px;object-fit:cover;">';
                            } else {
                                // fallback to gravatar
                                $gravatar = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user['email'] ?? ''))) . '?s=80&r=g&d=mm';
                                echo '<img src="' . htmlspecialchars($gravatar, ENT_QUOTES, 'UTF-8') . '" alt="Avatar" class="rounded-circle mb-2" style="width:80px;height:80px;object-fit:cover;">';
                            }
                            ?>
                            <input type="file" name="avatar" accept="image/png,image/jpeg,image/jpg,image/webp" class="form-control mt-2" style="max-width:300px;margin:auto;">
                            <small class="text-muted">Allowed: PNG, JPG, JPEG, WEBP. Max 2MB.</small>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" id="display_name" name="display_name" type="text" value="<?php echo htmlspecialchars($user['display_name'] ?? '') ?>"  />
                            <label for="display_name">Account Display Name <span style="color:red">*</span></label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" id="first" name="first_name" type="text" value="<?php echo htmlspecialchars($user['first_name'] ?? '') ?>" required />
                            <label for="first">First Name <span style="color:red">*</span></label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" id="last" name="second_name" type="text" value="<?php echo htmlspecialchars($user['second_name'] ?? '') ?>" required />
                            <label for="last">Second Name <span style="color:red">*</span></label>
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
        </div>
    </div>
</section>
<?php require dirname(__DIR__, 3) . '/views/partials/footer.php'; ?>