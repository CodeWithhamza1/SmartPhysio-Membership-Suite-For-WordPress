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
    // Add any admin-specific JavaScript here
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
    $where = $filter === 'eligible' ? 'WHERE is_eligible = 1' : ($filter === 'ineligible' ? 'WHERE is_eligible = 0' : '');
    
    $members = $wpdb->get_results("SELECT * FROM $this->table_name $where");
    ?>
    <div class="wrap">
        <h1>SmartPhysio Membership Suite</h1>
        <form method="get">
            <input type="hidden" name="page" value="sps-membership-suite">
            <select name="filter">
                <option value="all" <?php selected($filter, 'all'); ?>>All Members</option>
                <option value="eligible" <?php selected($filter, 'eligible'); ?>>Eligible</option>
                <option value="ineligible" <?php selected($filter, 'ineligible'); ?>>Ineligible</option>
            </select>
            <button type="submit" class="button">Filter</button>
        </form>
        <form method="post" style="margin: 10px 0;">
            <?php wp_nonce_field('sps_export_csv'); ?>
            <button type="submit" name="sps_export_csv" class="button">Export to CSV</button>
        </form>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?php echo esc_html($member->full_name); ?></td>
                        <td><?php echo esc_html($member->email); ?></td>
                        <td><?php echo esc_html($member->phone); ?></td>
                        <td>
                            <form method="post" style="display: inline;">
                                <?php wp_nonce_field('sps_update_status'); ?>
                                <input type="hidden" name="member_id" value="<?php echo $member->id; ?>">
                                <input type="checkbox" name="google_review" <?php checked($member->google_review); ?> style="vertical-align: middle;">
                            </form>
                        </td>
                        <td>
                            <form method="post" style="display: inline;">
                                <?php wp_nonce_field('sps_update_status'); ?>
                                <input type="hidden" name="member_id" value="<?php echo $member->id; ?>">
                                <input type="checkbox" name="social_follow" <?php checked($member->social_follow); ?> style="vertical-align: middle;">
                            </form>
                        </td>
                        <td>
                            <form method="post" style="display: inline;">
                                <?php wp_nonce_field('sps_update_status'); ?>
                                <input type="hidden" name="member_id" value="<?php echo $member->id; ?>">
                                <input type="checkbox" name="shared_contacts" <?php checked($member->shared_contacts); ?> style="vertical-align: middle;">
                            </form>
                        </td>
                        <td>
                            <form method="post" style="display: inline;">
                                <?php wp_nonce_field('sps_update_status'); ?>
                                <input type="hidden" name="member_id" value="<?php echo $member->id; ?>">
                                <input type="checkbox" name="referred_patient" <?php checked($member->referred_patient); ?> style="vertical-align: middle;">
                            </form>
                        </td>
                        <td>
                            <form method="post" style="display: inline;">
                                <?php wp_nonce_field('sps_update_status'); ?>
                                <input type="hidden" name="member_id" value="<?php echo $member->id; ?>">
                                <select name="is_eligible" style="vertical-align: middle;">
                                    <option value="0" <?php selected($member->is_eligible, 0); ?>>Ineligible</option>
                                    <option value="1" <?php selected($member->is_eligible, 1); ?>>Eligible</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <form method="post" style="display: inline;">
                                <?php wp_nonce_field('sps_update_status'); ?>
                                <input type="hidden" name="member_id" value="<?php echo $member->id; ?>">
                                <button type="submit" name="sps_update_status" class="button">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

    private function update_member_status() {
        global $wpdb;
        if (!isset($_POST['member_id']) || !check_admin_referer('sps_update_status')) {
            return;
        }

        $member_id = absint($_POST['member_id']);
        $data = [
            'google_review' => isset($_POST['google_review']) ? 1 : 0,
            'social_follow' => isset($_POST['social_follow']) ? 1 : 0,
            'shared_contacts' => isset($_POST['shared_contacts']) ? 1 : 0,
            'referred_patient' => isset($_POST['referred_patient']) ? 1 : 0,
            'is_eligible' => isset($_POST['is_eligible']) ? absint($_POST['is_eligible']) : 0
        ];

        // Ensure eligibility requires all conditions
        if ($data['google_review'] && $data['social_follow'] && $data['shared_contacts'] && $data['referred_patient']) {
            $data['is_eligible'] = 1;
        } else {
            $data['is_eligible'] = 0;
        }

        $wpdb->update(
            $this->table_name,
            $data,
            ['id' => $member_id],
            ['%d', '%d', '%d', '%d', '%d'],
            ['%d']
        );
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
