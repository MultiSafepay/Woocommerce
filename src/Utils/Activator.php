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

namespace MultiSafepay\WooCommerce\Utils;

use MultiSafepay\WooCommerce\Exceptions\MissingDependencyException;


/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since    4.0.0
 * @see      https://developer.wordpress.org/reference/functions/register_activation_hook/
 * @todo     Weird things could happen when a new blog is activate and the plugin has been activated in all network
 * @todo     Check if user can not activate plugin user_can
 * @todo     For some reason, if activate plugin network wide, and woocommerce is not active on one of the sites, return the error, but no the message
 *
 */
class Activator {

    /**
     * Fired during plugin activation according if is multisite or not.
     *
     * @param  bool $network_wide
     * @return  void
     */
	public function activate( bool $network_wide ): void {
        if ( ( !is_multisite() ) || ( is_multisite() && !$network_wide) ) {
            $this->activate_plugin_single_site();
        }
        if ( is_multisite() && $network_wide ) {
            $this->activate_plugin_all_sites();
        }
    }

    /**
     * Check if dependencies are not active and return fatal error
     * for a single site.
     *
     * @return  void
     */
    private function activate_plugin_single_site(): void {
        try {
            $dependencyChecker = new DependencyChecker();
            $dependencyChecker->check();
        } catch (MissingDependencyException $exception) {
            $message = sprintf(
                __('Missing dependencies: %s <br>Please install these extensions to use the Multisafepay WooCommerce plugin', 'multisafepay'),
                implode(', ', $exception->get_missing_plugin_names())
            );
            die($message);
        }
    }

    /**
     * Check if dependencies are not active and return fatal error
     * for a network.
     *
     * @return  void
     */
    private function activate_plugin_all_sites(): void {
        $blog_ids = $this->get_blogs_ids();
        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            $this->activate_plugin_single_site();
            restore_current_blog();
        }
    }

    /**
     * Return all sites ids
     *
     * @return array
     */
	private function get_blogs_ids(): array {
        $args = array(
            'fields' => 'ids'
        );
        $blogs_ids = get_sites($args);
        return $blogs_ids;
	}

}