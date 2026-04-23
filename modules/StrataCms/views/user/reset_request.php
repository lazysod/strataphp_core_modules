<?php
/**
 * CMS-Themed Password Reset Request Page
 */

use App\Modules\Cms\UserThemeWrapper;

// Generate the reset request form content
$formContent = '
<form id="resetRequestForm" method="post" action="/user/reset-request" style="margin: 0;">
    <input type="hidden" name="token" value="' . htmlspecialchars(\App\TokenManager::csrf()) . '">
    
    <div style="margin-bottom: 2rem;">
        <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--secondary-color);">Email Address</label>
        <input class="cms-input" id="email" name="email" type="email" placeholder="Enter your registered email" required 
               style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease;" />
        <small style="color: var(--text-light); font-size: 0.875rem; margin-top: 0.5rem; display: block;">
            We\'ll send a secure reset link to this email address
        </small>
    </div>
    
    <button class="cms-btn-primary" type="submit" id="submitButton" 
            style="width: 100%; padding: 1rem; background: var(--gradient-primary); color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
        🔐 Send Reset Link
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
</style>

<div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color); text-align: center; font-size: 0.9rem;">
    <p style="margin: 0.5rem 0;">Remember your password? <a href="/user/login" style="color: var(--primary-color); font-weight: 600;">Login here</a></p>
    <p style="margin: 0.5rem 0;">Don\'t have an account? <a href="/user/register" style="color: var(--primary-color); font-weight: 600;">Register here</a></p>
</div>

<div style="margin-top: 2rem; padding: 1.5rem; background: rgba(52, 152, 219, 0.05); border-radius: 8px; border-left: 4px solid var(--primary-color);">
    <h4 style="margin: 0 0 1rem 0; color: var(--secondary-color); font-size: 1rem;">Reset Process:</h4>
    <ol style="margin: 0; padding-left: 1.5rem; color: var(--text-light); font-size: 0.9rem;">
        <li style="margin-bottom: 0.5rem;">Enter your email address above</li>
        <li style="margin-bottom: 0.5rem;">Check your email for a reset link</li>
        <li style="margin-bottom: 0.5rem;">Click the link to create a new password</li>
        <li>Log in with your new password</li>
    </ol>
</div>';

// Generate the full page content
$pageContent = UserThemeWrapper::generateFormContent('Reset Your Password', $formContent, $error ?? '', $success ?? '');

// Render using CMS theme
UserThemeWrapper::renderUserPage('Password Reset Request', $pageContent, [
    'description' => 'Request a password reset link for your account',
    'title' => 'Reset Password | StrataPHP CMS',
    'slug' => 'reset-request',
    'noindex' => true
]);
