<?php

namespace WPSecurityNinja\Plugin;

class SecurityNinjaMainWPExtension {
    public function __construct() {
        add_action( 'init', array($this, 'init') );
        add_action( 'admin_init', array($this, 'admin_init') );
    }

    /**
     * render_page.
     *
     * @author    Lars Koudal
     * @since v0.0.1
     * @version   v1.0.0  Thursday, April 4th, 2024.
     * @access    public static
     * @return    void
     */
    public static function render_page() {
        global $Security_Ninja_Mainwp;
        $freeversion = true;
        if ( $freeversion ) {
            $readmore_link = Security_Ninja_Mainwp::generate_sn_web_link( 'welcome_notice', '/mainwp/' );
            ?>
			<div class="ui segment">

				<div class="ui info message">
					<i class="close icon mainwp-notice-dismiss" notice-id="secnin-pro-upgrade"></i>
					<h3><?php 
            esc_html_e( 'Security Ninja for MainWP Pro', 'security-ninja-mainwp' );
            ?></h3>
					<p><?php 
            esc_html_e( 'To enhance your security management capabilities, consider upgrading to the Pro version of Security Ninja. With Pro, you gain access to a combined event log across all your websites equipped with Security Ninja Pro.', 'security-ninja-mainwp' );
            ?></p>
					<p><?php 
            esc_html_e( 'Additionally, the Sites overview in the Security Ninja column will display more detailed insights, including malware scan reports and specific settings for each website.', 'security-ninja-mainwp' );
            ?></p>
					<p><?php 
            echo wp_kses( sprintf( __( 'Read more here: <a href="%s" target="_blank">Security Ninja for MainWP Pro</a>', 'security-ninja-mainwp' ), esc_url( $readmore_link ) ), array(
                'a' => array(
                    'href'   => array(),
                    'target' => array(),
                ),
            ) );
            ?></p>

					</p>
				</div>
			</div>

			<?php 
        }
    }

    /**
     * get_childkey.
     *
     * @author    Lars Koudal
     * @since v0.0.1
     * @version   v1.0.0  Thursday, April 4th, 2024.
     * @access    public static
     * @return    mixed
     */
    public static function get_childkey() {
        global $child_enabled;
        $child_enabled = apply_filters( 'mainwp_extension_enabled_check', __FILE__ );
        if ( !$child_enabled ) {
            // Handle error or return a default/fallback value.
            return null;
        }
        $child_key = $child_enabled['key'];
        return $child_key;
    }

}
