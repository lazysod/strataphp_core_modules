<?php
/**
 * CMS-Themed Password Reset Page
 */

use App\Modules\Cms\UserThemeWrapper;

// Generate the password reset form content
$formContent = '
<form id="resetPasswordForm" method="post" action="/user/reset" style="margin: 0;">
    <input type="hidden" name="token" value="' . htmlspecialchars(\App\TokenManager::csrf()) . '">
    <input type="hidden" name="reset_token" value="' . htmlspecialchars($token ?? '') . '">
    
    <div style="margin-bottom: 1.5rem;">
        <label for="pwd" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--secondary-color);">New Password</label>
        <input class="cms-input" id="pwd" name="pwd" type="password" placeholder="Enter a strong password" required 
               style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease;" />
        <small style="color: var(--text-light); font-size: 0.875rem; margin-top: 0.5rem; display: block;">
            Use at least 8 characters with a mix of letters, numbers, and symbols
        </small>
    </div>
    
    <div style="margin-bottom: 2rem;">
        <label for="pwd2" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--secondary-color);">Confirm New Password</label>
        <input class="cms-input" id="pwd2" name="pwd2" type="password" placeholder="Re-enter your password" required 
               style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease;" />
    </div>
    
    <button class="cms-btn-primary" type="submit" id="submitButton" 
            style="width: 100%; padding: 1rem; background: var(--gradient-primary); color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
        🔒 Update Password
    </button>
</form>

<style>
.cms-input:focus {
    outline: none;
    border-color: var(--primary-color) !important;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.cms-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

/* Password strength indicator */
#pwd {
    position: relative;
}

#pwd:focus + .password-strength {
    display: block;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const pwd = document.getElementById("pwd");
    const pwd2 = document.getElementById("pwd2");
    
    function validatePasswords() {
        if (pwd2.value && pwd.value !== pwd2.value) {
            pwd2.style.borderColor = "var(--accent-color)";
            pwd2.style.boxShadow = "0 0 0 3px rgba(231, 76, 60, 0.1)";
        } else if (pwd2.value) {
            pwd2.style.borderColor = "#28a745";
            pwd2.style.boxShadow = "0 0 0 3px rgba(40, 167, 69, 0.1)";
        }
    }
    
    pwd.addEventListener("input", validatePasswords);
    pwd2.addEventListener("input", validatePasswords);
});
</script>

<div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color); text-align: center; font-size: 0.9rem;">
    <p style="margin: 0.5rem 0;">After updating your password, you\'ll be able to <a href="/user/login" style="color: var(--primary-color); font-weight: 600;">login here</a></p>
</div>

<div style="margin-top: 2rem; padding: 1.5rem; background: rgba(40, 167, 69, 0.05); border-radius: 8px; border-left: 4px solid #28a745;">
    <h4 style="margin: 0 0 1rem 0; color: var(--secondary-color); font-size: 1rem;">Password Security Tips:</h4>
    <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-light); font-size: 0.9rem;">
        <li style="margin-bottom: 0.5rem;">Use at least 8 characters</li>
        <li style="margin-bottom: 0.5rem;">Include uppercase and lowercase letters</li>
        <li style="margin-bottom: 0.5rem;">Add numbers and special characters</li>
        <li>Avoid common words or personal information</li>
    </ul>
</div>';

// Generate the full page content
$pageContent = UserThemeWrapper::generateFormContent('Set Your New Password', $formContent, $error ?? '', $success ?? '');

// Render using CMS theme
UserThemeWrapper::renderUserPage('Set New Password', $pageContent, [
    'description' => 'Create a new secure password for your account',
    'title' => 'Set New Password | StrataPHP CMS',
    'slug' => 'reset-password',
    'noindex' => true
]);
