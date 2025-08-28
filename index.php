<?php
/*
Plugin Name: SmartPhysio Membership Suite
Plugin URI: https://github.com/codewithhamza1/smartphysio-membership-suite-for-wordpress
Description: Manages a free membership system for physiotherapits & clinic users based on engagement actions.
Version: 1.0.2
Author: Muhammad Hamza Yousaf
License: GPL-2.0+
*/

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class SmartPhysio_Membership_Suite {
    private $table_name;
    private $plugin_dir;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'sps_members';
        $this->plugin_dir = plugin_dir_path(__FILE__);
        
        // Initialize plugin
        add_action('init', [$this, 'register_shortcodes']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        register_activation_hook(__FILE__, [$this, 'activate_plugin']);
    }

    public function activate_plugin() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create database table
        $sql = "CREATE TABLE $this->table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            full_name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) NOT NULL,
            google_review boolean DEFAULT 0,
            social_follow boolean DEFAULT 0,
            shared_contacts boolean DEFAULT 0,
            referred_patient boolean DEFAULT 0,
            is_eligible boolean DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Create asset directories and files
        $this->create_asset_files();
    }

    private function create_asset_files() {
        // Create directories
        $css_dir = $this->plugin_dir . 'assets/css/';
        $js_dir = $this->plugin_dir . 'assets/js/';
        
        if (!file_exists($css_dir)) {
            wp_mkdir_p($css_dir);
        }
        if (!file_exists($js_dir)) {
            wp_mkdir_p($js_dir);
        }

        // Define file contents
        $files = [
            'assets/css/style.css' => $this->get_style_css_content(),
            'assets/css/admin-style.css' => $this->get_admin_style_css_content(),
            'assets/js/script.js' => $this->get_script_js_content(),
            'assets/js/admin-script.js' => $this->get_admin_script_js_content()
        ];

        // Create files if they don't exist
        foreach ($files as $file_path => $content) {
            $full_path = $this->plugin_dir . $file_path;
            if (!file_exists($full_path)) {
                file_put_contents($full_path, $content);
            }
        }
    }

    private function get_style_css_content() {
        return "/* SmartPhysio Membership Suite Styles */
.sps-membership-form-container,
.sps-membership-status {
    max-width: 600px;
    margin: 20px auto;
    padding: 20px;
    background: #F1F5F9;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(59,130,246,0.1);
}

.sps-form-group {
    margin-bottom: 15px;
}

.sps-form-group label {
    display: block;
    color: #1E293B;
    margin-bottom: 5px;
}

.sps-form-group input[type=\"text\"],
.sps-form-group input[type=\"email\"],
.sps-form-group input[type=\"tel\"] {
    width: 100%;
    padding: 8px;
    border: 1px solid #CBD5E1;
    border-radius: 4px;
    font-family: inherit;
}

.sps-submit-button {
    background: linear-gradient(90deg, #3B82F6, #06B6D4);
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-family: inherit;
}

.sps-submit-button:hover {
    background: linear-gradient(90deg, #1D4ED8, #06B6D4);
}

.sps-success {
    color: #10B981;
}

.sps-warning {
    color: #F59E0B;
}

.sps-error {
    color: #EF4444;
}

.sps-membership-status ul {
    list-style: none;
    padding: 0;
}

.sps-membership-status li {
    margin-bottom: 10px;
}

@media (max-width: 600px) {
    .sps-membership-form-container,
    .sps-membership-status {
        margin: 10px;
        padding: 15px;
    }
}";
    }

    private function get_admin_style_css_content() {
        return "/* SmartPhysio Membership Suite Admin Styles */
.wrap {
    font-family: inherit;
}

.wp-list-table {
    background: #F1F5F9;
    border: 1px solid #CBD5E1;
}

.wp-list-table th {
    background: linear-gradient(90deg, #3B82F6, #06B6D4);
    color: white;
}

.wp-list-table td {
    vertical-align: middle;
}

.wp-list-table .button {
    background: #1D4ED8;
    color: white;
    border: none;
}

.wp-list-table .button:hover {
    background: #3B82F6;
}

/* Enhanced form styling */
#sps-update-form {
    background: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 20px;
}

#sps-update-form .button-primary {
    background: linear-gradient(90deg, #3B82F6, #06B6D4);
    border: none;
    padding: 12px 24px;
    font-size: 16px;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(59,130,246,0.3);
}

#sps-update-form .button-primary:hover {
    background: linear-gradient(90deg, #1D4ED8, #0891B2);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(59,130,246,0.4);
}

/* Checkbox and select styling */
#sps-update-form input[type=\"checkbox\"] {
    transform: scale(1.2);
    cursor: pointer;
}

#sps-update-form select {
    padding: 6px 12px;
    border: 1px solid #CBD5E1;
    border-radius: 4px;
    background: white;
    cursor: pointer;
}

/* Success message styling */
.notice-success {
    border-left-color: #10B981 !important;
    background: #ECFDF5 !important;
}

.notice-success p {
    color: #065F46 !important;
    font-weight: 500;
}

/* Search and Filter Interface */
.sps-search-filter-form {
    background: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.sps-search-filter-row {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.sps-search-box {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 300px;
}

.sps-search-input {
    flex: 1;
    padding: 8px 12px;
    border: 2px solid #E2E8F0;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s ease;
}

.sps-search-input:focus {
    outline: none;
    border-color: #3B82F6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
}

.sps-filter-box {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sps-filter-box select {
    padding: 8px 12px;
    border: 2px solid #E2E8F0;
    border-radius: 6px;
    background: white;
    font-size: 14px;
    min-width: 150px;
}

.sps-clear-filters {
    margin-left: auto;
}

.sps-clear-filters .button {
    background: #6B7280;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    color: white;
    text-decoration: none;
    display: inline-block;
}

.sps-clear-filters .button:hover {
    background: #4B5563;
    color: white;
}

/* Results Summary */
.sps-results-summary {
    background: #F8FAFC;
    padding: 15px 20px;
    border-radius: 6px;
    border-left: 4px solid #3B82F6;
    margin: 20px 0;
}

.sps-results-summary p {
    margin: 0;
    color: #374151;
    font-size: 14px;
}

.sps-results-summary strong {
    color: #1F2937;
}

/* No Results Styling */
.wp-list-table tbody tr td[colspan=\"8\"] {
    background: #F9FAFB;
}

.wp-list-table tbody tr td[colspan=\"8\"] p {
    margin: 10px 0;
}

.wp-list-table tbody tr td[colspan=\"8\"] a {
    color: #3B82F6;
    text-decoration: none;
    font-weight: 500;
}

.wp-list-table tbody tr td[colspan=\"8\"] a:hover {
    text-decoration: underline;
}

/* Search Highlighting */
.sps-highlight {
    background-color: #FEF3C7 !important;
    border-radius: 3px;
    padding: 2px 4px;
    font-weight: 500;
}

/* Search Input Placeholder */
.sps-search-input::placeholder {
    color: #9CA3AF;
    font-style: italic;
}

/* Loading State */
.sps-search-filter-form.loading {
    opacity: 0.7;
    pointer-events: none;
}

.sps-search-filter-form.loading::after {
    content: 'Searching...';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #3B82F6;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    z-index: 1000;
}

/* Responsive design */
@media (max-width: 768px) {
    .sps-search-filter-row {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }
    
    .sps-search-box {
        min-width: auto;
    }
    
    .sps-filter-box {
        justify-content: center;
    }
    
    .sps-clear-filters {
        margin-left: 0;
        text-align: center;
    }
    
    #sps-update-form {
        padding: 15px;
        margin: 10px;
    }
    
    .wp-list-table {
        font-size: 14px;
    }
}";
    }

    private function get_script_js_content() {
        return "jQuery(document).ready(function($) {
    $('#sps-membership-form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: spsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'sps_submit_form',
                nonce: spsAjax.nonce,
                sps_full_name: $('#sps_full_name').val(),
                sps_email: $('#sps_email').val(),
                sps_phone: $('#sps_phone').val(),
                sps_google_review: $('#sps_google_review').is(':checked') ? 1 : 0,
                sps_social_follow: $('#sps_social_follow').is(':checked') ? 1 : 0,
                sps_shared_contacts: $('#sps_shared_contacts').is(':checked') ? 1 : 0,
                sps_referred_patient: $('#sps_referred_patient').is(':checked') ? 1 : 0
            },
            success: function(response) {
                $('#sps-form-message').html(
                    response.success ? 
                    '<div class=\"sps-success\">' + response.data.message + '</div>' :
                    '<div class=\"sps-error\">' + response.data.message + '</div>'
                );
                if (response.success) {
                    $('#sps-membership-form')[0].reset();
                }
            },
            error: function() {
                $('#sps-form-message').html('<div class=\"sps-error\">An error occurred</div>');
            }
        });
    });
});";
    }

    private function get_admin_script_js_content() {
        return "jQuery(document).ready(function($) {
    // Auto-update eligibility when checkboxes change
    $('input[type=\"checkbox\"]').on('change', function() {
        var row = $(this).closest('tr');
        var googleReview = row.find('input[name*=\"google_review\"]').is(':checked');
        var socialFollow = row.find('input[name*=\"social_follow\"]').is(':checked');
        var sharedContacts = row.find('input[name*=\"shared_contacts\"]').is(':checked');
        var referredPatient = row.find('input[name*=\"referred_patient\"]').is(':checked');
        
        var eligibilitySelect = row.find('select[name*=\"is_eligible\"]');
        
        // Auto-calculate eligibility
        if (googleReview && socialFollow && sharedContacts && referredPatient) {
            eligibilitySelect.val('1');
        } else {
            eligibilitySelect.val('0');
        }
    });
    
    // Search functionality enhancements
    $('.sps-search-input').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            $(this).closest('form').submit();
        }
    });
    
    // Auto-submit filter changes
    $('.sps-filter-box select').on('change', function() {
        $(this).closest('form').submit();
    });
    
    // Show success message if it exists
    if ($('.notice-success').length > 0) {
        setTimeout(function() {
            $('.notice-success').fadeOut();
        }, 3000);
    }
    
    // Highlight search terms in results
    var searchTerm = $('.sps-search-input').val();
    if (searchTerm) {
        $('.wp-list-table tbody td').each(function() {
            var text = $(this).text();
            if (text.toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1) {
                $(this).addClass('sps-highlight');
            }
        });
    }
});";
    }

    public function enqueue_assets() {
        if (has_shortcode(get_post()->post_content, 'membership_form') || 
            has_shortcode(get_post()->post_content, 'membership_status')) {
            wp_enqueue_style(
                'sps-membership-style',
                plugin_dir_url(__FILE__) . 'assets/css/style.css',
                [],
                '1.0.2'
            );
            wp_enqueue_script(
                'sps-membership-script',
                plugin_dir_url(__FILE__) . 'assets/js/script.js',
                ['jquery'],
                '1.0.2',
                true
            );
            wp_localize_script('sps-membership-script', 'spsAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('sps-membership-nonce')
            ]);
        }
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_sps-membership-suite') {
            return;
        }
        wp_enqueue_style(
            'sps-membership-admin-style',
            plugin_dir_url(__FILE__) . 'assets/css/admin-style.css',
            [],
            '1.0.2'
        );
        wp_enqueue_script(
            'sps-membership-admin-script',
            plugin_dir_url(__FILE__) . 'assets/js/admin-script.js',
            ['jquery'],
            '1.0.2',
            true
        );
    }

    public function register_shortcodes() {
        add_shortcode('membership_form', [$this, 'render_membership_form']);
        add_shortcode('membership_status', [$this, 'render_membership_status']);
    }

    public function render_membership_form($atts) {
    ob_start();

    // Check if user is logged in
    if (is_user_logged_in()) {
        global $wpdb;
        $current_user = wp_get_current_user();
        $email = sanitize_email($current_user->user_email);

        // Check if user is enrolled
        $member = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE email = %s",
                $email
            )
        );

        // If enrolled, show message
        if ($member) {
            ?>
            <div class="sps-membership-form-container">
                <div class="sps-success">You are already enrolled in free membership program</div>
            </div>
            <?php
            return ob_get_clean();
        }
    }

    // Show form if not logged in or not enrolled
    ?>
    <div class="sps-membership-form-container">
        <form id="sps-membership-form" method="post">
            <?php wp_nonce_field('sps_membership_form', 'sps_nonce'); ?>
            <div class="sps-form-group">
                <label for="sps_full_name">Full Name</label>
                <input type="text" id="sps_full_name" name="sps_full_name" required>
            </div>
            <div class="sps-form-group">
                <label for="sps_email">Email</label>
                <input type="email" id="sps_email" name="sps_email" required>
            </div>
            <div class="sps-form-group">
                <label for="sps_phone">Phone</label>
                <input type="tel" id="sps_phone" name="sps_phone" required>
            </div>
            <div class="sps-form-group">
                <label><input type="checkbox" name="sps_google_review"> I have left a Google Review ✅</label>
            </div>
            <div class="sps-form-group">
                <label><input type="checkbox" name="sps_social_follow"> I have followed all social platforms ✅</label>
            </div>
            <div class="sps-form-group">
                <label><input type="checkbox" name="sps_shared_contacts"> I have shared with 5 contacts ✅</label>
            </div>
            <div class="sps-form-group">
                <label><input type="checkbox" name="sps_referred_patient"> I have referred a treated patient ✅</label>
            </div>
            <button type="submit" class="sps-submit-button">Submit</button>
        </form>
        <div id="sps-form-message"></div>
    </div>
    <?php
    return ob_get_clean();
}

    public function render_membership_status($atts) {
        $atts = shortcode_atts(['email' => ''], $atts);
        $email = sanitize_email($atts['email']);
        
        // If no email provided, use current user's email if logged in
        if (empty($email) && is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $email = $current_user->user_email;
        }

        // Check if user is not logged in or no valid email found
        if (!is_email($email)) {
            return '<div class="sps-error">Please login first or get enrolled</div>';
        }

        global $wpdb;
        $member = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE email = %s",
                $email
            )
        );

        // Check if email is not enrolled in the database
        if (!$member) {
            return '<div class="sps-warning">You are not enrolled <a href="' . esc_url(get_permalink(get_page_by_path('free-membership-program  '))) . '" class="sps-submit-button">Get Enrolled Now</a></div>';
        }

        ob_start();
        ?>
        <div class="sps-membership-status">
            <h3>Membership Status for <span style="color:#043788">"<?php echo esc_html($member->full_name); ?>"</span></h3>
            <ul>
                <li>Google Review: <?php echo $member->google_review ? '✅ Verified' : '❌ Not Verified'; ?></li>
                <li>Social Follow: <?php echo $member->social_follow ? '✅ Verified' : '❌ Not Verified'; ?></li>
                <li>Shared Contacts: <?php echo $member->shared_contacts ? '✅ Verified' : '❌ Not Verified'; ?></li>
                <li>Referred Patient: <?php echo $member->referred_patient ? '✅ Verified' : '❌ Not Verified'; ?></li>
            </ul>
            <p>Eligibility: <span class="sps-<?php echo $member->is_eligible ? 'success' : 'warning'; ?>">
                <?php
                    if ($member->is_eligible) {
                        echo 'Eligible <a href="https://wa.me/923000000000" target="_blank" style="background-color:#25D366;color:white!important;padding:10px 18px;border-radius:30px;text-decoration:none;margin-left:10px;">Contact On WhatsApp</a>';
                    } else {
                        echo 'Ineligible';
                    }
                ?>

            </span></p>
        </div>
        <?php
        return ob_get_clean();
    }

    public function add_admin_menu() {
        add_menu_page(
            'Membership Suite',
            'Membership Suite',
            'manage_options',
            'sps-membership-suite',
            [$this, 'render_admin_page'],
            'dashicons-groups',
            80
        );
    }

    public function render_admin_page() {
    global $wpdb;
    
    // Handle CSV export
    if (isset($_POST['sps_export_csv']) && check_admin_referer('sps_export_csv')) {
        $this->export_to_csv();
    }

    // Handle status updates
    if (isset($_POST['sps_update_status']) && check_admin_referer('sps_update_status')) {
        $this->update_member_status();
    }

    $filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : 'all';
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    
    // Build WHERE clause for filtering and searching
    $where_conditions = [];
    $where_values = [];
    
    // Add filter condition
    if ($filter === 'eligible') {
        $where_conditions[] = 'is_eligible = 1';
    } elseif ($filter === 'ineligible') {
        $where_conditions[] = 'is_eligible = 0';
    }
    
    // Add search condition
    if (!empty($search)) {
        $where_conditions[] = '(full_name LIKE %s OR email LIKE %s)';
        $where_values[] = '%' . $wpdb->esc_like($search) . '%';
        $where_values[] = '%' . $wpdb->esc_like($search) . '%';
    }
    
    // Combine WHERE conditions
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    // Prepare and execute query
    if (!empty($where_values)) {
        $query = $wpdb->prepare("SELECT * FROM $this->table_name $where_clause", $where_values);
    } else {
        $query = "SELECT * FROM $this->table_name $where_clause";
    }
    
    $members = $wpdb->get_results($query);
    ?>
    <div class="wrap">
        <h1>SmartPhysio Membership Suite</h1>
        
        <!-- Search and Filter Form -->
        <form method="get" class="sps-search-filter-form">
            <input type="hidden" name="page" value="sps-membership-suite">
            
            <div class="sps-search-filter-row">
                <div class="sps-search-box">
                    <input type="text" name="search" value="<?php echo esc_attr($search); ?>" placeholder="Search by name or email..." class="sps-search-input">
                    <button type="submit" class="button">Search</button>
                </div>
                
                <div class="sps-filter-box">
                    <select name="filter">
                        <option value="all" <?php selected($filter, 'all'); ?>>All Members</option>
                        <option value="eligible" <?php selected($filter, 'eligible'); ?>>Eligible</option>
                        <option value="ineligible" <?php selected($filter, 'ineligible'); ?>>Ineligible</option>
                    </select>
                    <button type="submit" class="button">Filter</button>
                </div>
                
                <?php if (!empty($search) || $filter !== 'all'): ?>
                    <div class="sps-clear-filters">
                        <a href="?page=sps-membership-suite" class="button">Clear All Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </form>
        
        <!-- Export Form -->
        <form method="post" style="margin: 10px 0;">
            <?php wp_nonce_field('sps_export_csv'); ?>
            <button type="submit" name="sps_export_csv" class="button">Export to CSV</button>
        </form>
        
        <!-- Results Summary -->
        <div class="sps-results-summary">
            <p>Showing <strong><?php echo count($members); ?></strong> member(s)
                <?php if (!empty($search)): ?>
                    for "<strong><?php echo esc_html($search); ?></strong>"
                <?php endif; ?>
                <?php if ($filter !== 'all'): ?>
                    (<?php echo ucfirst($filter); ?> only)
                <?php endif; ?>
            </p>
        </div>
        
        <!-- Main Update Form -->
        <form method="post" id="sps-update-form">
            <?php wp_nonce_field('sps_update_status'); ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Google Review</th>
                        <th>Social Follow</th>
                        <th>Shared Contacts</th>
                        <th>Referred Patient</th>
                        <th>Eligibility</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($members)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                                <p>No members found matching your search criteria.</p>
                                <p><a href="?page=sps-membership-suite">View all members</a></p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td><?php echo esc_html($member->full_name); ?></td>
                                <td><?php echo esc_html($member->email); ?></td>
                                <td><?php echo esc_html($member->phone); ?></td>
                                <td>
                                    <input type="checkbox" name="google_review[<?php echo $member->id; ?>]" <?php checked($member->google_review, 1); ?> style="vertical-align: middle;">
                                </td>
                                <td>
                                    <input type="checkbox" name="social_follow[<?php echo $member->id; ?>]" <?php checked($member->social_follow, 1); ?> style="vertical-align: middle;">
                                </td>
                                <td>
                                    <input type="checkbox" name="shared_contacts[<?php echo $member->id; ?>]" <?php checked($member->shared_contacts, 1); ?> style="vertical-align: middle;">
                                </td>
                                <td>
                                    <input type="checkbox" name="referred_patient[<?php echo $member->id; ?>]" <?php checked($member->referred_patient, 1); ?> style="vertical-align: middle;">
                                </td>
                                <td>
                                    <select name="is_eligible[<?php echo $member->id; ?>]" style="vertical-align: middle;">
                                        <option value="0" <?php selected($member->is_eligible, 0); ?>>Ineligible</option>
                                        <option value="1" <?php selected($member->is_eligible, 1); ?>>Eligible</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if (!empty($members)): ?>
                <div style="margin-top: 20px;">
                    <button type="submit" name="sps_update_status" class="button button-primary">Update All Changes</button>
                </div>
            <?php endif; ?>
        </form>
    </div>
    <?php
}

    private function update_member_status() {
        global $wpdb;
        if (!check_admin_referer('sps_update_status')) {
            return;
        }

        // Get all the form data arrays
        $google_reviews = isset($_POST['google_review']) ? $_POST['google_review'] : [];
        $social_follows = isset($_POST['social_follow']) ? $_POST['social_follow'] : [];
        $shared_contacts = isset($_POST['shared_contacts']) ? $_POST['shared_contacts'] : [];
        $referred_patients = isset($_POST['referred_patient']) ? $_POST['referred_patient'] : [];
        $is_eligibles = isset($_POST['is_eligible']) ? $_POST['is_eligible'] : [];

        // Process each member's data
        foreach ($google_reviews as $member_id => $value) {
            $member_id = absint($member_id);
            
            // Get current values for this member
            $current_member = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE id = %d",
                $member_id
            ));

            if (!$current_member) {
                continue;
            }

            // Prepare data for update
            $data = [
                'google_review' => isset($google_reviews[$member_id]) ? 1 : 0,
                'social_follow' => isset($social_follows[$member_id]) ? 1 : 0,
                'shared_contacts' => isset($shared_contacts[$member_id]) ? 1 : 0,
                'referred_patient' => isset($referred_patients[$member_id]) ? 1 : 0,
                'is_eligible' => isset($is_eligibles[$member_id]) ? absint($is_eligibles[$member_id]) : 0
            ];

            // Auto-calculate eligibility based on all conditions being met
            if ($data['google_review'] && $data['social_follow'] && $data['shared_contacts'] && $data['referred_patient']) {
                $data['is_eligible'] = 1;
            } else {
                $data['is_eligible'] = 0;
            }

            // Update the member
            $wpdb->update(
                $this->table_name,
                $data,
                ['id' => $member_id],
                ['%d', '%d', '%d', '%d', '%d'],
                ['%d']
            );
        }

        // Add success message
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>Member statuses updated successfully!</p></div>';
        });
    }

    private function export_to_csv() {
        global $wpdb;
        $members = $wpdb->get_results("SELECT * FROM $this->table_name");
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sps-membership-export.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Name', 'Email', 'Phone', 'Google Review', 'Social Follow', 'Shared Contacts', 'Referred Patient', 'Eligibility']);
        
        foreach ($members as $member) {
            fputcsv($output, [
                $member->full_name,
                $member->email,
                $member->phone,
                $member->google_review ? 'Yes' : 'No',
                $member->social_follow ? 'Yes' : 'No',
                $member->shared_contacts ? 'Yes' : 'No',
                $member->referred_patient ? 'Yes' : 'No',
                $member->is_eligible ? 'Eligible' : 'Ineligible'
            ]);
        }
        
        fclose($output);
        exit;
    }
}

new SmartPhysio_Membership_Suite();

add_action('wp_ajax_sps_submit_form', function() {
    check_ajax_referer('sps-membership-nonce', 'nonce');
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'sps_members';
    
    $data = [
        'full_name' => sanitize_text_field($_POST['sps_full_name']),
        'email' => sanitize_email($_POST['sps_email']),
        'phone' => sanitize_text_field($_POST['sps_phone']),
        'google_review' => isset($_POST['sps_google_review']) ? 1 : 0,
        'social_follow' => isset($_POST['sps_social_follow']) ? 1 : 0,
        'shared_contacts' => isset($_POST['sps_shared_contacts']) ? 1 : 0,
        'referred_patient' => 0 // Admin verification required
    ];

    if (!is_email($data['email'])) {
        wp_send_json_error(['message' => 'Invalid email address']);
    }

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE email = %s",
        $data['email']
    ));

    if ($exists) {
        wp_send_json_error(['message' => 'Email already registered']);
    }

    $result = $wpdb->insert(
        $table_name,
        $data,
        ['%s', '%s', '%s', '%d', '%d', '%d', '%d']
    );

    if ($result) {
        wp_send_json_success(['message' => 'Membership application submitted successfully']);
    } else {
        wp_send_json_error(['message' => 'Error submitting application']);
    }
});
