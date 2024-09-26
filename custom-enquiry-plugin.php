<?php
/*
Plugin Name: Custom Enquiry Plugin
Description: A plugin to submit and view enquiries.
Version: 1.0
Author: Your Name
*/

// Hook to create custom table upon plugin activation
register_activation_hook(__FILE__, 'cep_create_custom_table');

function cep_create_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'enquiries';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(20) NOT NULL,
        message text NOT NULL,
        looking_for varchar(255) NOT NULL,
        submitted_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Shortcode to display enquiry form
add_shortcode('enquiry_form', 'cep_display_enquiry_form');

function cep_display_enquiry_form() {
    ob_start();

    if (isset($_POST['submit_enquiry'])) {
        cep_handle_enquiry_submission();
    }
    ?>

    <style>
        .enquiry-form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .enquiry-form p {
            margin-bottom: 15px;
        }
        .enquiry-form label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .enquiry-form input[type="text"],
        .enquiry-form input[type="email"],
        .enquiry-form textarea,
        .enquiry-form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .enquiry-form button {
            padding: 10px 15px;
            background-color: #0073aa;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .enquiry-form button:hover {
            background-color: #005a87;
        }
    </style>

    <form class="enquiry-form" method="POST" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
        <p>
            <label for="name">Name</label>
            <input type="text" name="name" required>
        </p>
        <p>
            <label for="email">Email</label>
            <input type="email" name="email" required>
        </p>
        <p>
            <label for="phone">Phone</label>
            <input type="text" name="phone" required>
        </p>
        <p>
            <label for="message">Message</label>
            <textarea name="message" required></textarea>
        </p>
        <p>
            <label for="looking_for">Looking for?</label>
            <select name="looking_for" required>
                <option value="Option 1">Option 1</option>
                <option value="Option 2">Option 2</option>
                <option value="Option 3">Option 3</option>
            </select>
        </p>
        <p>
            <button type="submit" name="submit_enquiry">Submit Enquiry</button>
        </p>
    </form>

    <?php
    return ob_get_clean();
}

// Function to handle form submission
function cep_handle_enquiry_submission() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'enquiries';

    // Sanitize and validate input fields
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $message = sanitize_textarea_field($_POST['message']);
    $looking_for = sanitize_text_field($_POST['looking_for']);

    // Insert data into custom table
    $wpdb->insert($table_name, array(
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message,
        'looking_for' => $looking_for,
    ));

    // Display success message after submission
    echo '<p>Thank you for your enquiry! We will get back to you soon.</p>';
}

// Shortcode to display all enquiries in tabular format
add_shortcode('view_enquiries', 'cep_display_enquiries');

function cep_display_enquiries() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'enquiries';
    $enquiries = $wpdb->get_results("SELECT * FROM $table_name");

    if (empty($enquiries)) {
        return '<p>No enquiries found.</p>';
    }

    ob_start(); ?>
    
    <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Message</th>
                <th>Looking For</th>
                <th>Submitted At</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($enquiries as $enquiry) : ?>
            <tr>
                <td><?php echo esc_html($enquiry->name); ?></td>
                <td><?php echo esc_html($enquiry->email); ?></td>
                <td><?php echo esc_html($enquiry->phone); ?></td>
                <td><?php echo esc_html($enquiry->message); ?></td>
                <td><?php echo esc_html($enquiry->looking_for); ?></td>
                <td><?php echo esc_html($enquiry->submitted_at); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php
    return ob_get_clean();
}
