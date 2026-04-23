<?php
require_once dirname(__DIR__, 4) . '/bootstrap.php';
require dirname(__DIR__, 3) . '/views/partials/header.php';
use App\App;

// App::dump($_SESSION, 'Current User Data');

// Robust check: If no profiles, block access and show warning
if (empty($profile_list) || !is_array($profile_list) || count(array_filter($profile_list)) === 0) {
    header('Location: /user/dashboard');
    exit;
}
?>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="bg-dark rounded-3 py-5 px-4 px-md-5 mb-5">
                    <div class="text-center mb-5">
                        <h1 class="fw-bolder">Profile Settings</h1>
                    </div>
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
                                    <label class="form-label text-white">Profile Avatar</label><br>
                                    <?php
                                    $sessionPrefix = $config['session_prefix'] ?? 'app_';
                                    $sessionAvatar = $_SESSION[$sessionPrefix . 'profile']['profile_image'] ?? '';
                                    $dbAvatar = $user['avatar'] ?? '';
                                    $avatarToShow = $sessionAvatar; // $sessionAvatar ?: $dbAvatar;
                                    // Remove any leading slash for user_id/filename
                                    // Remove any duplicate /app/uploads/img/ from avatarToShow
                                    $avatarToShow = preg_replace('#^/?app/uploads/img/#', '', $avatarToShow);
                                    $fullAvatarPath = $avatarToShow ? '/app/uploads/img/profile/' . $avatarToShow : '';
                                    // echo $fullAvatarPath;
                                    if ($avatarToShow && file_exists($_SERVER['DOCUMENT_ROOT'] . $fullAvatarPath)) {
                                        echo '<img src="' . htmlspecialchars($fullAvatarPath) . '" alt="Avatar" class="rounded-circle mb-2" style="width:80px;height:80px;object-fit:cover;">';
                                    } else {
                                        $fallbackAvatar = !empty($config['base_url']) ? $config['base_url'] . '/assets/images/blank-avatar.png' : '/assets/images/blank-avatar.png';
                                        echo '<img src="' . htmlspecialchars($fallbackAvatar, ENT_QUOTES, 'UTF-8') . '" alt="Avatar" class="rounded-circle mb-2" style="width:80px;height:80px;object-fit:cover;">';
                                    }
                                    ?>
                                    <input type="file" name="avatar" accept="image/png,image/jpeg,image/jpg,image/webp" class="form-control mt-2" style="max-width:300px;margin:auto;">
                                    <small class="text-muted">Allowed: PNG, JPG, JPEG, WEBP. Max 2MB.</small>
                                </div>

                                <div class="d-grid"><button class="btn btn-primary btn-lg" type="submit">Update Avatar</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card bg-dark rounded-3 p-4">
                    <h3 class="text-center">Profile Info</h3>
                    <div class="alert alert-info" role="alert" style="display: none;">

                    </div>

                    <div class="">
                        <div class="form-group">
                            <label class="text-white">Profile Name</label>
                            <input type="text" name="profile_name" id="profile_name" value="<?php echo htmlspecialchars($profile['profile_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
                        </div>
                        <div class="form-group mt-4">
                            <div class="form-group">
                                <label for="profileDescription" class="text-white">Profile Description</label>
                                <textarea class="form-control" rows="4" id="bio"><?php echo htmlspecialchars($profile['bio'] ?? 'No description set.'); ?></textarea>
                            </div>
                        </div>
                        <div class="row  mt-2 d-flex align-items-center">
                            <div class="col-md-4">
                                <div class="">
                                    <label for="prideLogo" class="text-white">Pride Logo URL</label>
                                    <select class="form-select w-auto" id="logo">
                                        <?php
                                        if ($profile['pride_logo'] > 0) {
                                            echo '<option value="0">Plain</option>';
                                            echo '<option value="1" selected>Rainbow / Pride</option>';
                                        } else {
                                            echo '<option value="0" selected>Plain</option>';
                                            echo '<option value="1">Rainbow / Pride</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <?php
                                if ($profile['verified'] > 0) {
                                    $baseUrl = isset($site_config['base_url']) ? $site_config['base_url'] : (class_exists('App\\App') ? App::config('base_url') : '');
                                    echo '<img src="' . $baseUrl . '/app/uploads/img/verified.png" alt="Verified" class="verified-badge">';
                                    echo '<p>You are verified</p>';
                                } else {
                                    echo '<span class="badge bg-secondary">Unverified</span>';
                                }
                                ?>
                            </div>
                            <div class="col-md-4 text-center">
                                <label class="text-white">Profile Lock</label>
                                <p class="text-muted">When locked your profile will not be accessible</p>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="profileLockSwitch" <?php echo ($profile['locked'] > 0) ? 'checked' : ''; ?>> <label class="form-check-label text-white" for="profileLockSwitch"><span id="lockedLabel">Locked</span><span id="unlockedLabel">UnLocked</span></label>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <button class="btn btn-primary" type="button" id="updateProfileBtn">Update Profile</button>
                        </div>
                        <!-- Delete Profile Button and Modal -->
                        <div class="mt-4">
                            <button id="deleteProfileBtn" class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteProfileModal" <?php if (isset($profile_list) && count($profile_list) <= 1) {
                                echo 'disabled';
                                                                                                                                                           } ?>>
                                <i class="fa-solid fa-trash"></i> Delete This Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const lockSwitch = document.getElementById('profileLockSwitch');
        const lockedLabel = document.getElementById('lockedLabel');
        const unlockedLabel = document.getElementById('unlockedLabel');
        const updateBtn = document.getElementById('updateProfileBtn');
        const bioField = document.getElementById('bio');
        const logoSelect = document.getElementById('logo');
        const profileNameField = document.getElementById('profile_name');

        function updateLabels() {
            if (lockSwitch.checked) {
                lockedLabel.style.display = '';
                unlockedLabel.style.display = 'none';
            } else {
                lockedLabel.style.display = 'none';
                unlockedLabel.style.display = '';
            }
        }
        lockSwitch.addEventListener('change', updateLabels);
        updateLabels();

        // AJAX profile update
        updateBtn.addEventListener('click', function() {
            if (profileNameField.value.trim() === '') {
                updateBtn.textContent = 'Please enter profile name';
                updateBtn.classList.remove('btn-primary');
                updateBtn.classList.add('btn-danger');
                return;
            }
            updateBtn.disabled = true;
            updateBtn.textContent = 'Saving...';
            const data = {
                locked: lockSwitch.checked ? 1 : 0,
                bio: bioField.value,
                pride_logo: logoSelect.value,
                profile_name: profileNameField.value
            };
            // console.log('Updating profile with data:', data);
            // return;
            fetch('/ajax/editProfile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        updateBtn.textContent = 'Saved!';
                        updateBtn.classList.remove('btn-primary');
                        updateBtn.classList.add('btn-success');
                        setTimeout(() => {
                            updateBtn.textContent = 'Update Profile';
                            updateBtn.classList.remove('btn-success');
                            updateBtn.classList.add('btn-primary');
                        }, 1500);
                    } else if (result.error === 'No changes made.') {
                        updateBtn.textContent = 'No changes made.';
                        updateBtn.classList.remove('btn-primary', 'btn-danger');
                        updateBtn.classList.add('btn-secondary');
                        setTimeout(() => {
                            updateBtn.textContent = 'Update Profile';
                            updateBtn.classList.remove('btn-secondary');
                            updateBtn.classList.add('btn-primary');
                        }, 2000);
                    } else {
                        updateBtn.textContent = 'Error!';
                        updateBtn.classList.remove('btn-primary');
                        updateBtn.classList.add('btn-danger');
                        setTimeout(() => {
                            updateBtn.textContent = 'Update Profile';
                            updateBtn.classList.remove('btn-danger');
                            updateBtn.classList.add('btn-primary');
                        }, 2000);
                    }
                })
                .catch(() => {
                    updateBtn.textContent = 'Error!';
                    updateBtn.classList.remove('btn-primary');
                    updateBtn.classList.add('btn-danger');
                    setTimeout(() => {
                        updateBtn.textContent = 'Update Profile';
                        updateBtn.classList.remove('btn-danger');
                        updateBtn.classList.add('btn-primary');
                    }, 2000);
                })
                .finally(() => {
                    updateBtn.disabled = false;
                });
        });
    });
</script>

<div class="modal fade" id="deleteProfileModal" tabindex="-1" aria-labelledby="deleteProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProfileModalLabel">Delete Profile?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this profile? <strong>This will permanently delete all links, groups, and data for this profile.</strong></p>
                <p>This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteProfileBtn">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var deleteBtn = document.getElementById('deleteProfileBtn');
        var confirmBtn = document.getElementById('confirmDeleteProfileBtn');
        if (deleteBtn && confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                confirmBtn.disabled = true;
                fetch('/ajax/dashboard_ajax.php?action=delete_profile', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            profile_id: <?php echo (int)($_SESSION[$sessionPrefix . 'active_profile'] ?? 0); ?>
                        })
                    })
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            if (data.no_profiles) {
                                alert('Profile deleted. You have no profiles left. Please create a new profile to continue.');
                                window.location = '/user/dashboard';
                            } else if (data.profile_id) {
                                alert('Profile deleted. Switched to another profile.');
                                window.location = '/user/dashboard';
                            } else {
                                alert('Profile deleted.');
                                window.location = '/user/dashboard';
                            }
                        } else {
                            alert(data.error || 'Failed to delete profile.');
                        }
                        confirmBtn.disabled = false;
                    });
            });
        }
    });
</script>
<?php require dirname(__DIR__, 3) . '/views/partials/footer.php'; ?>