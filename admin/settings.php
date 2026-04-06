<?php
session_start();
require_once '../config/db.php';
require_once '../config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$page_title = "Site Settings";

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $settings_data = $_POST['settings'];
    
    foreach ($settings_data as $key => $value) {
        $value = is_array($value) ? implode(',', $value) : trim($value);
        $key_escaped = $conn->real_escape_string($key);
        $value_escaped = $conn->real_escape_string($value);
        
        // Check if setting exists
        $check = $conn->query("SELECT id FROM site_settings WHERE setting_key = '$key_escaped'");
        
        if ($check->num_rows > 0) {
            // Update existing
            $conn->query("UPDATE site_settings SET setting_value = '$value_escaped', updated_at = NOW() WHERE setting_key = '$key_escaped'");
        } else {
            // Insert new
            $conn->query("INSERT INTO site_settings (setting_key, setting_value, setting_type, updated_at) VALUES ('$key_escaped', '$value_escaped', 'text', NOW())");
        }
    }
    
    logActivity('admin', $admin['id'], 'update_settings', "Updated site settings");
    setFlash('success', 'Settings updated successfully');
    redirect('settings.php');
}

// Get all settings
$settings_result = $conn->query("SELECT * FROM site_settings ORDER BY setting_key");
$settings = [];
if ($settings_result) {
    while ($row = $settings_result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Default settings if not exist
$default_settings = [
    'site_name' => 'Zuba Online Market',
    'site_tagline' => 'Your One-Stop Marketplace',
    'site_email' => 'info@zubamarket.com',
    'site_phone' => '+250788000000',
    'site_address' => 'Kigali, Rwanda',
    'currency' => 'RWF',
    'currency_symbol' => 'FRw',
    'tax_rate' => '0',
    'shipping_fee' => '0',
    'items_per_page' => '12',
    'enable_reviews' => '1',
    'enable_wishlist' => '1',
    'maintenance_mode' => '0',
    'facebook_url' => '',
    'twitter_url' => '',
    'instagram_url' => '',
    'linkedin_url' => '',
];

// Merge with defaults
foreach ($default_settings as $key => $value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $value;
    }
}

require_once 'includes/header.php';
?>

<style>
.content-header {
    margin-bottom: 30px;
}

.content-header h1 {
    margin: 0;
}

.settings-container {
    max-width: 900px;
}

.settings-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e5e7eb;
}

.settings-tab {
    padding: 12px 20px;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 14px;
    color: #666;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
}

.settings-tab:hover {
    color: #1a1a2e;
}

.settings-tab.active {
    color: #f97316;
    border-bottom-color: #f97316;
}

.settings-section {
    display: none;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.settings-section.active {
    display: block;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a2e;
    margin: 0 0 20px 0;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    color: #333;
    font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
    box-sizing: border-box;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
}

.form-note {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
    background-color: #f97316;
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-primary {
    background: #f97316;
    color: white;
}

.btn-primary:hover {
    background: #ea580c;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.info-box {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.info-box p {
    margin: 0;
    font-size: 14px;
    color: #1e40af;
}

.warning-box {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.warning-box p {
    margin: 0;
    font-size: 14px;
    color: #92400e;
}

@media (max-width: 768px) {
    .settings-tabs {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .settings-tab {
        white-space: nowrap;
        padding: 10px 16px;
        font-size: 13px;
    }
    
    .settings-section {
        padding: 20px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>

<div class="content-header">
    <h1><?php echo $page_title; ?></h1>
</div>

<?php if ($flash = getFlash()): ?>
    <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
<?php endif; ?>

<?php if (empty($settings) || count($settings) < 5): ?>
<div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <p style="margin: 0 0 10px 0; color: #92400e; font-weight: 600;">⚠️ Settings Not Initialized</p>
    <p style="margin: 0 0 10px 0; color: #92400e; font-size: 14px;">Your site settings table appears to be empty or incomplete.</p>
    <a href="init-settings.php" style="display: inline-block; padding: 8px 16px; background: #f97316; color: white; text-decoration: none; border-radius: 5px; font-size: 14px;">Initialize Settings Now</a>
</div>
<?php endif; ?>

<!-- Debug Info (remove in production) -->
<?php if (isset($_GET['debug'])): ?>
<div style="background: #f3f4f6; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 12px;">
    <strong>Debug Info:</strong><br>
    Settings loaded from database: <?php echo count($settings); ?><br>
    <details>
        <summary>View all settings</summary>
        <pre><?php print_r($settings); ?></pre>
    </details>
</div>
<?php endif; ?>

<div class="settings-container">
    <div class="settings-tabs">
        <button class="settings-tab active" onclick="switchTab('general')">General</button>
        <button class="settings-tab" onclick="switchTab('currency')">Currency & Pricing</button>
        <button class="settings-tab" onclick="switchTab('features')">Features</button>
        <button class="settings-tab" onclick="switchTab('social')">Social Media</button>
        <button class="settings-tab" onclick="switchTab('advanced')">Advanced</button>
    </div>

    <form method="POST">
        <input type="hidden" name="update_settings" value="1">
        
        <!-- General Settings -->
        <div id="general" class="settings-section active">
            <h2 class="section-title">General Settings</h2>
            
            <div class="info-box">
                <p>Configure basic site information and contact details.</p>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Site Name *</label>
                    <input type="text" name="settings[site_name]" value="<?php echo e($settings['site_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Site Tagline</label>
                    <input type="text" name="settings[site_tagline]" value="<?php echo e($settings['site_tagline']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Contact Email *</label>
                    <input type="email" name="settings[site_email]" value="<?php echo e($settings['site_email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Contact Phone *</label>
                    <input type="text" name="settings[site_phone]" value="<?php echo e($settings['site_phone']); ?>" required>
                </div>
                
                <div class="form-group full-width">
                    <label>Address</label>
                    <textarea name="settings[site_address]" rows="3"><?php echo e($settings['site_address']); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Currency & Pricing -->
        <div id="currency" class="settings-section">
            <h2 class="section-title">Currency & Pricing Settings</h2>
            
            <div class="info-box">
                <p>Configure currency, tax rates, and shipping fees.</p>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Currency Code *</label>
                    <input type="text" name="settings[currency]" value="<?php echo e($settings['currency']); ?>" placeholder="RWF" required>
                    <div class="form-note">e.g., RWF, USD, EUR</div>
                </div>
                
                <div class="form-group">
                    <label>Currency Symbol *</label>
                    <input type="text" name="settings[currency_symbol]" value="<?php echo e($settings['currency_symbol']); ?>" placeholder="FRw" required>
                    <div class="form-note">e.g., FRw, $, €</div>
                </div>
                
                <div class="form-group">
                    <label>Tax Rate (%)</label>
                    <input type="number" name="settings[tax_rate]" value="<?php echo e($settings['tax_rate']); ?>" min="0" max="100" step="0.01">
                    <div class="form-note">Enter 0 for no tax</div>
                </div>
                
                <div class="form-group">
                    <label>Default Shipping Fee</label>
                    <input type="number" name="settings[shipping_fee]" value="<?php echo e($settings['shipping_fee']); ?>" min="0" step="0.01">
                    <div class="form-note">Enter 0 for free shipping</div>
                </div>
            </div>
        </div>
        
        <!-- Features -->
        <div id="features" class="settings-section">
            <h2 class="section-title">Feature Settings</h2>
            
            <div class="info-box">
                <p>Enable or disable site features and configure display options.</p>
            </div>
            
            <div class="form-group">
                <label>Items Per Page</label>
                <input type="number" name="settings[items_per_page]" value="<?php echo e($settings['items_per_page']); ?>" min="6" max="100">
                <div class="form-note">Number of items to display per page in listings</div>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <span>Enable Reviews</span>
                    <label class="toggle-switch">
                        <input type="hidden" name="settings[enable_reviews]" value="0">
                        <input type="checkbox" name="settings[enable_reviews]" value="1" <?php echo $settings['enable_reviews'] == '1' ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </label>
                <div class="form-note">Allow customers to leave reviews on products, properties, and vehicles</div>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <span>Enable Wishlist</span>
                    <label class="toggle-switch">
                        <input type="hidden" name="settings[enable_wishlist]" value="0">
                        <input type="checkbox" name="settings[enable_wishlist]" value="1" <?php echo $settings['enable_wishlist'] == '1' ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </label>
                <div class="form-note">Allow customers to save items to wishlist</div>
            </div>
        </div>
        
        <!-- Social Media -->
        <div id="social" class="settings-section">
            <h2 class="section-title">Social Media Links</h2>
            
            <div class="info-box">
                <p>Add your social media profile URLs. Leave blank to hide.</p>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Facebook URL</label>
                    <input type="url" name="settings[facebook_url]" value="<?php echo e($settings['facebook_url']); ?>" placeholder="https://facebook.com/yourpage">
                </div>
                
                <div class="form-group">
                    <label>Twitter URL</label>
                    <input type="url" name="settings[twitter_url]" value="<?php echo e($settings['twitter_url']); ?>" placeholder="https://twitter.com/yourhandle">
                </div>
                
                <div class="form-group">
                    <label>Instagram URL</label>
                    <input type="url" name="settings[instagram_url]" value="<?php echo e($settings['instagram_url']); ?>" placeholder="https://instagram.com/yourhandle">
                </div>
                
                <div class="form-group">
                    <label>LinkedIn URL</label>
                    <input type="url" name="settings[linkedin_url]" value="<?php echo e($settings['linkedin_url']); ?>" placeholder="https://linkedin.com/company/yourcompany">
                </div>
            </div>
        </div>
        
        <!-- Advanced -->
        <div id="advanced" class="settings-section">
            <h2 class="section-title">Advanced Settings</h2>
            
            <div class="warning-box">
                <p><strong>Warning:</strong> These settings can affect site functionality. Change with caution.</p>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <span>Maintenance Mode</span>
                    <label class="toggle-switch">
                        <input type="hidden" name="settings[maintenance_mode]" value="0">
                        <input type="checkbox" name="settings[maintenance_mode]" value="1" <?php echo $settings['maintenance_mode'] == '1' ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </label>
                <div class="form-note">When enabled, only admins can access the site. Customers will see a maintenance message.</div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="button" onclick="location.reload()" class="btn btn-secondary">Reset</button>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<script>
function switchTab(tabName) {
    // Hide all sections
    document.querySelectorAll('.settings-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Remove active from all tabs
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected section
    document.getElementById(tabName).classList.add('active');
    
    // Activate clicked tab
    event.target.classList.add('active');
}
</script>

<?php require_once 'includes/footer.php'; ?>
