<?php declare(strict_types=1);

/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the MultiSafepay plugin
 * to newer versions in the future. If you wish to customize the plugin for your
 * needs please document your changes and make backups before you update.
 *
 * @category    MultiSafepay
 * @package     Connect
 * @author      TechSupport <integration@multisafepay.com>
 * @copyright   Copyright (c) MultiSafepay, Inc. (https://www.multisafepay.com)
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

namespace MultiSafepay\WooCommerce\Settings;

use MultiSafepay\WooCommerce\PaymentMethods\Gateways;

/**
 * The settings page controller.
 *
 * Defines all the functionalities needed on the settings page
 *
 * @since   4.0.0
 */
class SettingsController {

	/**
	 * The ID of this plugin.
	 *
	 * @var      string
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @var      string
	 */
	private $version;

    /**
     * The plugin dir url
     *
     * @var      string
     */
    private $plugin_dir_url;

    /**
     * The plugin dir path
     *
     * @var      string
     */
    private $plugin_dir_path;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param   string    $plugin_name       The name of this plugin.
	 * @param   string    $version           The version of this plugin.
     * @param   string    $plugin_dir_url    The plugin dir url.
     * @param   string    $plugin_dir_path   The plugin dir path.
	 */
	public function __construct( string $plugin_name, string $version, string $plugin_dir_url, string $plugin_dir_path) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_dir_url = $plugin_dir_url;
        $this->plugin_dir_path = $plugin_dir_path;
	}

	/**
	 * Register the stylesheets for the settings page.
     *
     * @see https://developer.wordpress.org/reference/functions/wp_enqueue_style/
     * @return void
	 */
	public function enqueue_styles(): void {
		wp_enqueue_style( $this->plugin_name, $this->plugin_dir_url . 'assets/admin/css/multisafepay-admin.css', array(), $this->version, 'all' );
	}

    /**
     * Register the JavaScript needed in the backend.
     *
     * @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/
     * @see https://developer.wordpress.org/reference/functions/wp_localize_script/
     * @return void
     */
    public function enqueue_scripts():void {
        $multisafepay_vars = array(
            'wp_ajax_url'               => admin_url('admin-ajax.php'),
            'multisafepay_settings_url' => admin_url('admin.php?page=multisafepay-settings&needs-setup=1'),
            'nonces'                    => array(
                'multisafepay_gateway_toggle'   => wp_create_nonce('multisafepay-toggle-payment-gateway-enabled')
            )
        );
        wp_register_script( $this->plugin_name . 'admin-js', $this->plugin_dir_url . 'assets/admin/js/multisafepay-admin.js', array( 'jquery' ), $this->version, false );
        wp_localize_script( $this->plugin_name . 'admin-js', 'multisafepay', $multisafepay_vars);
        wp_enqueue_script( $this->plugin_name . 'admin-js' );
    }

    /**
     * Register the common settings page in WooCommerce menu section.
     *
     * @see https://developer.wordpress.org/reference/functions/add_submenu_page/
     * @return void
     */
	public function register_common_settings_page(): void {
        add_submenu_page(
            'woocommerce',
            __('MultiSafepay Settings v.' . $this->version, $this->plugin_name),
            __('MultiSafepay Settings', $this->plugin_name),
            'manage_options',
            'multisafepay-settings',
            array($this, 'display_multisafepay_settings')
        );
    }

    /**
     * Display the common settings page view.
     *
     * @return void
     */
	public function display_multisafepay_settings(): void {
        $tab_active   = $this->get_tab_active();
        $needs_update = $this->needs_update();
        remove_query_arg('needs-setup');
        require_once($this->plugin_dir_path . 'templates/' . $this->plugin_name . '-settings-display.php');
    }

    /**
     * Display the common settings page view.
     *
     * @return void
     */
    public function display_multisafepay_support_section(): void {
        require_once($this->plugin_dir_path . 'templates/partials/' . $this->plugin_name . '-settings-support-display.php');
    }

    /**
     * Returns if the request has been redirected after
     * try to enable the payment method using the toggle button.
     *
     * @return  void
     */
    private function needs_update(): void {
        if(isset($_GET['needs-setup']) && $_GET['needs-setup'] === '1') {
            // Remove the parameter to avoid include it into _wp_http_referer
            $_SERVER['REQUEST_URI'] = remove_query_arg( 'needs-setup' );
            add_settings_error(
                '',
                '',
                __('You need to fill these settings, to be able to enable a MultiSafepay payment method', $this->plugin_name),
                'error'
            );
        }
    }

    /**
     * Returns active tab defined in get variable
     *
     * @return  string
     */
    private function get_tab_active(): string {
	    if(!isset($_GET['tab']) || $_GET['tab'] === '') {
	        $tab_active = 'general';
        }
        if(isset($_GET['tab']) && $_GET['tab'] !== '') {
            $tab_active = $_GET['tab'];
        }
        return $tab_active;
    }

    /**
     * Register general settings in common settings page
     *
     * @return void
     */
    public function register_common_settings(): void {
        $settings_fields = new SettingsFields( $this->plugin_name );
        $settings = $settings_fields->get_settings();
        foreach ( $settings as $tab_key => $section ) {
            $this->add_settings_section( $tab_key, $section['title'] );
            foreach ( $section['fields'] as $field ) {
                $this->register_setting( $field, $tab_key );
                $this->add_settings_field( $field, $tab_key );
            }
        }
    }

    /**
     * Add settings field
     *
     * @see https://developer.wordpress.org/reference/functions/add_settings_field/
     * @param   array   $field      The field
     * @param   string  $tab_key    The key of the tab
     * @return  void
     */
    private function add_settings_field( array $field, string $tab_key ): void {
        add_settings_field(
            $field['id'],
            $this->generate_label_for_settings_field($field),
            array( $this, 'display_field' ),
            'multisafepay-settings-' . $tab_key,
            $tab_key,
            array(
                'field'     => $field
            )
        );
    }

    /**
     * Return the label tag to be used in add_settings_field
     *
     * @param   array   $field  The settings field array
     * @return  string
     */
    private function generate_label_for_settings_field( array $field ): string {
        if($field['tooltip'] === '') {
            return sprintf('<label for="%s">%s</label>', $field['id'], $field['label']);
        }
        return sprintf('<label for="%s">%s %s</label>', $field['id'], $field['label'], wc_help_tip($field['tooltip']) );
    }

    /**
     * Filter which set the settings page and adds a screen options of WooCommerce
     *
     * @see http://hookr.io/filters/woocommerce_screen_ids/
     * @param   array   $screen
     * @return  array
     */
    public function set_wc_screen_options_in_common_settings_page( array $screen ): array {
        $screen[] = 'woocommerce_page_multisafepay-settings';
        return $screen;
    }

    /**
     * Register setting
     *
     * @see https://developer.wordpress.org/reference/functions/register_setting/
     * @param   array    $field
     * @param   string   $tab_key
     * @return  void
     */
    private function register_setting( array $field, string $tab_key): void {
        register_setting(
            'multisafepay-settings-' . $tab_key,
            $field['id'],
            array(
                'type'                  => $field['setting_type'],
                'show_in_rest'          => false,
                'sanitize_callback'     => $field['callback']
            )
        );
    }

    /**
     * Add settings section
     *
     * @see https://developer.wordpress.org/reference/functions/add_settings_section/
     *
     * @param   string  $section_key
     * @param   string  $section_title
     * @return  void
     */
    private function add_settings_section( string $section_key, string $section_title ): void {
        add_settings_section(
            $section_key,
            $section_title,
            array($this, 'display_intro_section'),
            'multisafepay-settings-' . $section_key
        );
    }

    /**
     * Callback to display the title on each settings sections
     *
     * @see https://developer.wordpress.org/reference/functions/add_settings_section/
     * @param   array    $args
     * @return  void
     */
    public function display_intro_section( array $args ): void {
        $settings_fields = new SettingsFields( $this->plugin_name );
        $settings = $settings_fields->get_settings();
        if(!empty($settings[$args['id']]['intro'])) {
            printf( '<p>%s</p>', $settings[$args['id']]['intro']);
        }
    }

    /**
     * Return the HTML view by field.
     * Is the callback function in add_settings_field
     *
     * @param   array    $args
     * @return  void
     */
    public function display_field( $args ): void {
        $field      = $args['field'];
        $settings_field_display = new SettingsFieldsDisplay($this->plugin_name, $field);
        $settings_field_display->display();
    }

    /**
     * This function intervene WooCommerce AJAX call for the action woocommerce_toggle_gateway_enabled
     * Check if API key is set up for the selected environment. If is not, end the request.
     *
     * @return  void
     */
    public function before_ajax_toggle_gateway_enabled(): void {
        $multisafepay_gateways = $this->get_gateways_ids();
        if(
            defined('DOING_AJAX') && DOING_AJAX &&
            isset( $_POST['gateway_id'] ) && in_array( $_POST['gateway_id'], $multisafepay_gateways, true ) &&
            isset( $_POST['action'] ) && $_POST['action'] === 'woocommerce_toggle_gateway_enabled'
        ) {
            check_ajax_referer( 'woocommerce-toggle-payment-gateway-enabled', 'security' );
            $is_there_api_key = $this->is_there_api_key();
            if(!$is_there_api_key) {
                wp_die();
            }
        }
    }

    /**
     * This function is called by an AJAX action woocommerce_multisafepay_toggle_gateway_enabled
     * Check if API key is set up for the selected environment. If is not, return error for redirect the user to settings page.
     *
     * @return  void
     */
    public function multisafepay_ajax_toggle_gateway_enabled(): void {
        $multisafepay_gateways = $this->get_gateways_ids();
        if(
            defined('DOING_AJAX') && DOING_AJAX &&
            isset( $_POST['gateway_id'] )  && in_array( $_POST['gateway_id'], $multisafepay_gateways, true ) &&
            isset( $_POST['action'] ) && $_POST['action'] === 'woocommerce_multisafepay_toggle_gateway_enabled'
        ) {
            check_ajax_referer( 'multisafepay-toggle-payment-gateway-enabled', 'security' );
            $is_there_api_key = $this->is_there_api_key();
            if (!$is_there_api_key) {
                wp_send_json_error('needs_setup');
                wp_die();
            }
            wp_send_json(array("success" => true));
            wp_die();
        }
    }

    /**
     * Get gateways ids.
     *
     * @return  array
     */
    private function get_gateways_ids(): array {
        $gateways = new Gateways();
        $gateways_ids = $gateways->get_gateways_ids();
        return $gateways_ids;
    }

    /**
     * Check if there is an api key to check before enable the MultiSafepay
     * payment method before enable using the toggle in payment methods lists.
     *
     * @return  boolean
     */
    private function is_there_api_key(): bool {
        $test_environment = get_option( 'multisafepay_environment' );
        if( $test_environment ) {
            $api_key = get_option( 'multisafepay_sandbox_api_key' );
        }
        if( !$test_environment ) {
            $api_key = get_option( 'multisafepay_api_key' );
        }
        if(!$api_key) {
            return false;
        }
        return true;
    }

    /**
     * Filter the settings field and return sort by sort_order key.
     *
     * @param   array   $settings
     * @return  array
     */
    public function filter_multisafepay_common_settings_fields( array $settings ): array {
        foreach ( $settings as $key => $section ) {
            $sort_order = array();
            foreach ( $section['fields'] as $field_key => $field ) {
                $sort_order[$field_key] = $field['sort_order'];
            }
            array_multisort($sort_order, SORT_ASC, $settings[$key]['fields']);
        }
        return $settings;
    }

}