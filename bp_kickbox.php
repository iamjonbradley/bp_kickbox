<?php

/**
 * Plugin Name: Brand Partners - Kickbox
 * Plugin URI: https://bp.agency
 * Description: Plugin to enable kickbox email verification
 * Version: 0.1
 * Author: Jonathan Bradley
 * Author URI: https://bp.agency
 **/

function bp_kickbox_wp_admin_style($hook)
{
    // $hook is string value given add_menu_page function.
    if ($hook != 'toplevel_page_bp_kickbox/bp_kickbox') {
        return;
    }
    wp_enqueue_style('custom_wp_admin_css', plugins_url('css/admin.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'bp_kickbox_wp_admin_style');

function bp_kickbox_register_plugin_script()
{
    wp_enqueue_script('registered-script', plugins_url('js/scripts.js', __FILE__), array('jquery'), '1.1', true);
}
add_action('wp_enqueue_scripts', 'bp_kickbox_register_plugin_script');

function bp_kickbox_create_menu()
{
    add_menu_page('BP Kickbox Settings', 'BP Kickbox', 'administrator', __FILE__, 'bp_kickbox_settings_page', plugins_url('/images/icon.png', __FILE__));
    add_action('admin_init', 'register_bp_kickbox_settings');
}
add_action('admin_menu', 'bp_kickbox_create_menu');


function register_bp_kickbox_settings()
{
    register_setting('bp_kickbox-settings-group', 'bp_kickbox_api_key');
}

function bp_kickbox_settings_page()
{
?>
    <div class="wrap">
        <h1>Brand Partners Kickbox Settings</h1>

        <form method="post" action="options.php">
            <?php settings_fields('bp_kickbox-settings-group'); ?>
            <?php do_settings_sections('bp_kickbox-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Kickbox Api Key</th>
                    <td><input type="text" name="bp_kickbox_api_key" value="<?php echo esc_attr(get_option('bp_kickbox_api_key')); ?>" class="regular-text bp_kickbox_api_key" /></td>
                </tr>
            </table>

            <?php submit_button(); ?>

        </form>
    </div>
<?php

}

function bp_kickbox_verify_email($email)
{
    $url = 'https://api.kickbox.com/v2/verify?%s';
    $params = [
        'email' => $email,
        'apikey' => esc_attr(get_option('bp_kickbox_api_key'))
    ];

    $endpoint = sprintf($url, http_build_query($params));

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ]);
    $response = curl_exec($curl);
    $jsonFormatted = json_decode($response, true);

    curl_close($curl);

    return ($jsonFormatted['result'] === 'deliverable') ? 'success' : 'fail';
}


add_action('wp_ajax_bp_kickbox_validate_email', 'bp_kickbox_validate_email');
add_action('wp_ajax_nopriv_bp_kickbox_validate_email', 'bp_kickbox_validate_email');

function bp_kickbox_validate_email()
{
    global $wpdb; // this is how you get access to the database

    $result = [
        'status' => 'fail'
    ];

    if (isset($_POST['data'])) {
        $data = $_POST['data'];
        $email = isset($data['email']) ? $data['email'] : '';
        $response = bp_kickbox_verify_email($email);

        $result = [
            'status' => $response
        ];
    }

    wp_send_json($result);

    wp_die(); // this is required to terminate immediately and return a proper response
}
