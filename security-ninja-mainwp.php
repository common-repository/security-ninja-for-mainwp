<?php

/**
 * Plugin Name: Security Ninja for MainWP
 * Plugin URI: https://wpsecurityninja.com/mainwp/
 * Description: This extension integrates WP Security Ninja with MainWP
 * Author: WP Security Ninja
 * Version: 2.0.10
 * Text Domain: security-ninja-mainwp
 * Domain Path: /languages
 * Author URI: https://wpsecurityninja.com/
 * License: GPL2
 */
namespace WPSecurityNinja\Plugin;

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'snmwp_fs' ) ) {
    snmwp_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'snmwp_fs' ) ) {
        // Create a helper function for easy SDK access.
        function snmwp_fs() {
            global $snmwp_fs;
            if ( !isset( $snmwp_fs ) ) {
                // Activate multisite network integration.
                if ( !defined( 'WP_FS__PRODUCT_14707_MULTISITE' ) ) {
                    define( 'WP_FS__PRODUCT_14707_MULTISITE', true );
                }
                // Include Freemius SDK.
                if ( file_exists( dirname( __DIR__ ) . '/security-ninja/freemius/start.php' ) ) {
                    // Try to load SDK from parent plugin folder.
                    require_once dirname( __DIR__ ) . '/security-ninja/freemius/start.php';
                } elseif ( file_exists( dirname( __DIR__ ) . '/security-ninja-premium/freemius/start.php' ) ) {
                    // Try to load SDK from premium parent plugin folder.
                    require_once dirname( __DIR__ ) . '/security-ninja-premium/freemius/start.php';
                } else {
                    require_once __DIR__ . '/freemius/start.php';
                }
                $snmwp_fs = fs_dynamic_init( array(
                    'id'             => '14707',
                    'slug'           => 'security-ninja-mainwp',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_dc276a6539b113235914826d4b0f2',
                    'is_premium'     => false,
                    'premium_suffix' => 'Pro',
                    'has_paid_plans' => true,
                    'trial'          => array(
                        'days'               => 30,
                        'is_require_payment' => true,
                    ),
                    'parent'         => array(
                        'id'         => '3690',
                        'slug'       => 'security-ninja',
                        'public_key' => 'pk_f990ec18700a90c02db544f1aa986',
                        'name'       => 'Security Ninja',
                    ),
                    'menu'           => array(
                        'slug'       => 'wf-sn-mainwp',
                        'first-path' => 'admin.php?page=wf-sn-mainwp&welcome-message=true',
                        'support'    => false,
                    ),
                    'is_live'        => true,
                ) );
            }
            return $snmwp_fs;
        }

    }
    class Security_Ninja_Mainwp {
        public static $mainwp_main_activated = false;

        public static $child_enabled = false;

        public static $child_key = false;

        public static $plugin_handle = 'secnin-mainwp-extension';

        public static $child_file;

        public static $plugin_url;

        public static $plugin_slug;

        public static $security_ninja_db_version = '1.0';

        /**
         * __construct.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Thursday, March 10th, 2022.
         * @version v1.0.1  Monday, April 4th, 2022.
         * @access  public
         * @return  boolean
         */
        public function __construct() {
            self::$child_file = __FILE__;
            self::$plugin_url = plugin_dir_url( __FILE__ );
            self::$plugin_slug = plugin_basename( __FILE__ );
            self::$security_ninja_db_version = get_site_option( 'security_ninja_db_version' );
            register_activation_hook( __FILE__, array(__CLASS__, 'security_ninja_mainwp_activate') );
            add_action( 'init', array(__CLASS__, 'do_init') );
            add_filter( 'mainwp_getextensions', array(__CLASS__, 'get_this_extension') );
            add_filter(
                'mainwp_plugins_install_checks',
                array(__CLASS__, 'wpsn_mainwp_plugins_install_checks'),
                10,
                1
            );
            self::$mainwp_main_activated = apply_filters( 'mainwp_activated_check', false );
            if ( false !== self::$mainwp_main_activated ) {
                self::activate_this_plugin();
            } else {
                //Because sometimes our main plugin is activated after the extension plugin is activated we also have a second step,
                //listening to the 'mainwp-activated' action. This action is triggered by MainWP after initialisation.
                add_action( 'mainwp_activated', array(__CLASS__, 'activate_this_plugin') );
            }
            add_action( 'secninmwp_prune_history', array(__CLASS__, 'do_action_secninmwp_prune_history') );
            add_action( 'admin_init', array(__CLASS__, 'check_security_ninja_plugins') );
            add_action( 'current_screen', array(__CLASS__, 'show_security_ninja_warning_on_plugins_page') );
            add_action( 'admin_init', array(__CLASS__, 'admin_init') );
            add_action( 'admin_notices', array(__CLASS__, 'mainwp_error_notice') );
            // To warn of missing parent plugin - WP Security Ninja and show message
            add_action( 'admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts') );
            add_filter(
                'mainwp_getsubpages_sites',
                array(__CLASS__, 'managesites_subpage'),
                10,
                1
            );
            add_filter( 'mainwp_sitestable_getcolumns', array(__CLASS__, 'sitestable_getcolumns'), 10 );
            add_filter( 'mainwp_sitestable_item', array(__CLASS__, 'sitestable_item'), 10 );
            add_action( 'wp_ajax_secnin_run_remote_security_tests', array(__CLASS__, 'ajax_run_remote_security_tests') );
            add_action( 'wp_ajax_secnin_get_latest_events', array(__CLASS__, 'ajax_get_latest_events__premium_only') );
            add_action( 'wp_ajax_secnin_run_update_white_label_module', array(__CLASS__, 'ajax_run_update_white_label_module__premium_only') );
            add_action( 'mainwp_header_left', array(__CLASS__, 'custom_page_title') );
            add_filter(
                'mainwp_main_menu',
                array(__CLASS__, 'do_filter_mainwp_main_menu'),
                10,
                1
            );
            add_filter(
                'mainwp_pro_reports_custom_tokens',
                array(__CLASS__, 'secnin_mainwp_pro_reports_custom_tokens'),
                10,
                3
            );
            add_filter(
                'mainwp_managesites_bulk_actions',
                array(__CLASS__, 'do_filter_mainwp_managesites_bulk_actions'),
                10,
                1
            );
            add_filter(
                'mainwp_sync_others_data',
                array(__CLASS__, 'handle_custom_mainwp_sync_others_data'),
                10,
                2
            );
            add_filter(
                'mainwp_site_synced',
                array(__CLASS__, 'handle_custom_mainwp_site_synced'),
                10,
                2
            );
            add_action( 'admin_menu', array(__CLASS__, 'add_admin_menu') );
            add_action( 'init', array(__CLASS__, 'this_addon_init') );
        }

        /**
         * do_init.
         *
         * @author	Lars Koudal
         * @since	v0.0.1
         * @version	v1.0.0	Wednesday, October 30th, 2024.
         * @access	public static
         * @return	void
         */
        public static function do_init() {
            load_plugin_textdomain( 'security-ninja-mainwp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        }

        /**
         * this_addon_init.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Sunday, April 28th, 2024.
         * @access  public static
         * @global
         * @return  void
         */
        public static function this_addon_init() {
            if ( !wp_next_scheduled( 'secninmwp_prune_history' ) ) {
                wp_schedule_event( time() + 15, 'twicedaily', 'secninmwp_prune_history' );
            }
            if ( self::snmwp_fs_is_parent_active_and_loaded() ) {
                // If parent already included, init add-on.
                self::snmwp_fs_init();
            } elseif ( self::snmwp_fs_is_parent_active() ) {
                // Init add-on only after the parent is loaded.
                add_action( 'secnin_fs_loaded', array(__CLASS__, 'snmwp_fs_init') );
            } else {
                // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
                self::snmwp_fs_init();
            }
        }

        /**
         * snmwp_fs_is_parent_active_and_loaded.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Sunday, April 28th, 2024.
         * @access  public static
         * @global
         * @return  mixed
         */
        public static function snmwp_fs_is_parent_active_and_loaded() {
            // Check if the parent's init SDK method exists.
            return function_exists( '\\WPSecurityNinja\\Plugin\\secnin_fs' );
        }

        /**
         * snmwp_fs_is_parent_active.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Sunday, April 28th, 2024.
         * @access  public static
         * @return  boolean
         */
        public static function snmwp_fs_is_parent_active() {
            $active_plugins = get_option( 'active_plugins', array() );
            if ( is_multisite() ) {
                $network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
                $active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
            }
            foreach ( $active_plugins as $basename ) {
                if ( 0 === strpos( $basename, 'security-ninja/' ) || 0 === strpos( $basename, 'security-ninja-premium/' ) ) {
                    return true;
                }
            }
            return false;
        }

        /**
         * snmwp_fs_init.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Sunday, April 28th, 2024.
         * @access  public static
         * @return  void
         */
        public static function snmwp_fs_init() {
            if ( self::snmwp_fs_is_parent_active_and_loaded() ) {
                snmwp_fs();
                do_action( 'snmwp_fs_loaded' );
            } else {
            }
        }

        /**
         * do_action_secninmwp_prune_history.
         *
         * @author	Lars Koudal
         * @since	v0.0.1
         * @version	v1.0.0	Monday, July 8th, 2024.
         * @access	public static
         * @global
         * @return	void
         */
        public static function do_action_secninmwp_prune_history() {
            global $wpdb;
            $events_table = $wpdb->prefix . 'mainwp_security_ninja_events';
            // Count the total entries first
            $total_entries = $wpdb->get_var( "SELECT COUNT(*) FROM {$events_table}" );
            // If more than 10,000 entries, delete the oldest ones until 10,000 remain
            if ( $total_entries > 10000 ) {
                $entries_to_delete = $total_entries - 10000;
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$events_table} WHERE `id` IN (\n                SELECT `id` FROM (\n                    SELECT `id` FROM {$events_table} ORDER BY `timestamp` ASC LIMIT %d\n                ) as subquery\n            )", $entries_to_delete ) );
            }
            // Now check and delete entries older than 30 days if total entries <= 10000
            $total_entries_after_pruning = $wpdb->get_var( "SELECT COUNT(*) FROM {$events_table}" );
            if ( $total_entries_after_pruning <= 10000 ) {
                $date_30_days_ago = date( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$events_table} WHERE `timestamp` < %s", $date_30_days_ago ) );
            }
        }

        /**
         * check_security_ninja_plugins.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Tuesday, May 28th, 2024.
         * @access  public static
         * @return  void
         */
        public static function check_security_ninja_plugins() {
            $plugin_1 = 'security-ninja/security-ninja.php';
            $plugin_2 = 'security-ninja-premium/security-ninja.php';
            if ( self::is_plugin_active( $plugin_1 ) || self::is_plugin_active( $plugin_2 ) ) {
                add_action(
                    'after_plugin_row_security-ninja/security-ninja.php',
                    array(__CLASS__, 'security_ninja_warning_row'),
                    10,
                    3
                );
                add_action(
                    'after_plugin_row_security-ninja-premium/security-ninja.php',
                    array(__CLASS__, 'security_ninja_warning_row'),
                    10,
                    3
                );
            }
        }

        /**
         * is_plugin_active.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Tuesday, May 28th, 2024.
         * @access  private static
         * @param   mixed   $plugin
         * @return  mixed
         */
        private static function is_plugin_active( $plugin ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
            return is_plugin_active( $plugin );
        }

        /**
         * security_ninja_warning_row.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Tuesday, May 28th, 2024.
         * @access  public static
         * @param   mixed   $plugin_file
         * @param   mixed   $plugin_data
         * @param   mixed   $status
         * @return  void
         */
        public static function security_ninja_warning_row( $plugin_file, $plugin_data, $status ) {
            ?>
			<tr class="plugin-update-tr active" id="security-ninja-warning" data-slug="security-ninja" data-plugin="<?php 
            echo esc_attr( $plugin_file );
            ?>">
				<td colspan="4" class="plugin-update colspanchange">
					<div class="update-message notice inline notice-error notice-alt">
						<?php 
            echo '<p>' . sprintf( 
                /* translators: %1$s: Plugin name, %2$s: Addon name */
                esc_html__( 'Important: The %1$s plugin is required to keep the %2$s running. Please do not deactivate it. If you have any questions, please contact support.', 'security-ninja-mainwp' ),
                '<strong>Security Ninja</strong>',
                '<strong>Security Ninja for MainWP Addon</strong>'
             ) . '</p>';
            ?>
					</div>
				</td>
			</tr>
		<?php 
        }

        /**
         * show_security_ninja_warning_on_plugins_page.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Tuesday, May 28th, 2024.
         * @access  public static
         * @return  void
         */
        public static function show_security_ninja_warning_on_plugins_page() {
            $current_screen = get_current_screen();
            if ( 'plugins' === $current_screen->id ) {
                self::check_security_ninja_plugins();
            }
        }

        /**
         * add_admin_menu.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Friday, April 12th, 2024.
         * @access  public static
         * @return  void
         */
        public static function add_admin_menu() {
            // Ensure the current user has the capability to view this submenu
            if ( !current_user_can( 'manage_options' ) ) {
                return;
            }
            $icon_url = self::get_icon_svg();
            add_menu_page(
                __( 'Security Ninja for MainWP', 'security-ninja-mainwp' ),
                'SN4MWP',
                'manage_options',
                'wf-sn-mainwp',
                array(__NAMESPACE__ . '\\security_ninja_mainwp', 'mainwp_submenu_page'),
                $icon_url
            );
        }

        /**
         * Helper function to generate tagged links
         *
         * @param  string $placement [description]
         * @param  string $page      [description]
         * @param  array  $params    [description]
         * @return string            Full URL with utm_ parameters added
         */
        public static function generate_sn_web_link( $placement = '', $page = '/', $params = array() ) {
            $base_url = 'https://wpsecurityninja.com';
            if ( '/' !== $page ) {
                $page = '/' . trim( $page, '/' ) . '/';
            }
            $utm_source = 'sn4mwp_free';
            $plugin_data = get_plugin_data( __FILE__ );
            $plugin_version = $plugin_data['Version'];
            $parts = array_merge( array(
                'utm_source'   => esc_attr( $utm_source ),
                'utm_medium'   => 'plugin',
                'utm_content'  => esc_attr( $placement ),
                'utm_campaign' => esc_attr( 'sn4mwp_v' . esc_attr( $plugin_version ) ),
            ), $params );
            $out = $base_url . $page . '?' . http_build_query( $parts, '', '&amp;' );
            return $out;
        }

        /**
         * Function to display the content of MainWP related subpage
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Friday, April 12th, 2024.
         * @access  public static
         * @global
         * @return  void
         */
        public static function mainwp_submenu_page() {
            ?>
			<div class="wrap">
				<div class="ui">
					<?php 
            $show_pro_intro = true;
            $help_link = self::generate_sn_web_link( 'mwp_welcome_notice', '/help/' );
            $docs_link = self::generate_sn_web_link( 'mwp_welcome_notice', '/docs/mainwp/' );
            // if the GET param welcome-message=true is set, show a welcome message for new users
            if ( isset( $_GET['welcome-message'] ) && 'true' === sanitize_key( $_GET['welcome-message'] ) ) {
                // @todo - detect license
                ?>

						<div class="notice notice-success">
							<p><strong><?php 
                esc_html_e( 'AppSumo users!', 'security-ninja-mainwp' );
                ?></strong> <?php 
                esc_html_e( 'Thank you for supporting us on AppSumo! We promised free keys for MainWP. If you have purchased via our AppSumo launch please reach out to our support and we will create a free lifetime MainWP Extension license key.', 'security-ninja-mainwp' );
                ?></p>
							<p><a href="<?php 
                echo esc_url( $help_link );
                ?>" target="_blank"><?php 
                esc_html_e( 'Contact our support here', 'security-ninja-mainwp' );
                ?></a>, <?php 
                esc_html_e( 'remember to mention you purchased via AppSumo.', 'security-ninja-mainwp' );
                ?></p>
						</div>
					<?php 
            }
            ?>
					<div class="card">
						<h2><?php 
            esc_html_e( 'Security Ninja for MainWP', 'security-ninja-mainwp' );
            ?></h2>

						<p><?php 
            esc_html_e( 'This plugin enables you to remotely monitor and manage Security Ninja on each website under your control.', 'security-ninja-mainwp' );
            ?></p>

						<p><strong><?php 
            esc_html_e( "Welcome New User! We're glad to have you on board. Enjoy using Security Ninja for MainWP!", 'security-ninja-mainwp' );
            ?></strong></p>

						<?php 
            $docs_link = self::generate_sn_web_link( 'welcome_notice', '/docs/mainwp/' );
            ?>
						<p><?php 
            echo esc_html__( 'Getting stuck? Check out the documentation here:', 'security-ninja-mainwp' );
            ?> <a href="<?php 
            echo esc_url( $docs_link );
            ?>" target="_blank" rel="noopener"><?php 
            esc_html_e( 'Documentation', 'security-ninja-mainwp' );
            ?></a></p>

						<p><a href="<?php 
            echo esc_url( $help_link );
            ?>" target="_blank" rel="noopener"><?php 
            esc_html_e( 'Contact our support here', 'security-ninja-mainwp' );
            ?>.</a></p>

						<?php 
            if ( $show_pro_intro ) {
                ?>
							<p><strong><?php 
                esc_html_e( 'To access comprehensive details from websites using Security Ninja Premium, the Pro version of this MainWP Extension is required.', 'security-ninja-mainwp' );
                ?></strong></p>

							<div class="snwrap">
								<div class="card">
									<h3 class="title"><?php 
                esc_html_e( 'Free Features', 'security-ninja-mainwp' );
                ?></h3>
									<div class="inside">
										<ul>
											<li>&check; <?php 
                esc_html_e( 'View vulnerabilities and results of security tests for all sites', 'security-ninja-mainwp' );
                ?></li>
											<li>&check; <?php 
                esc_html_e( 'Check out the test scores on individual site pages', 'security-ninja-mainwp' );
                ?></li>
											<li>&check; <?php 
                esc_html_e( 'See vulnerability details on individual site pages', 'security-ninja-mainwp' );
                ?></li>
											<li>&check; <?php 
                esc_html_e( 'Initiate security tests on selected sites from a distance', 'security-ninja-mainwp' );
                ?></li>
										</ul>
									</div>
								</div>

								<div class="card">
									<h3 class="title"><?php 
                esc_html_e( 'Pro Features', 'security-ninja-mainwp' );
                ?></h3>
									<div class="inside">
										<ul>
											<li>&check; <?php 
                esc_html_e( 'All free features', 'security-ninja-mainwp' );
                ?></li>
											<li>&check; <?php 
                esc_html_e( 'See results of malware scans in the site overview', 'security-ninja-mainwp' );
                ?></li>
											<li>&check; <?php 
                esc_html_e( 'Examine detailed malware scans on individual site pages', 'security-ninja-mainwp' );
                ?></li>
											<li>&check; <?php 
                esc_html_e( 'Get a combined overview of all event logs from all websites to monitor activities', 'security-ninja-mainwp' );
                ?></li>
											<li>&check; <?php 
                esc_html_e( 'Start malware scans remotely', 'security-ninja-mainwp' );
                ?></li>
										</ul>
										<?php 
                if ( snmwp_fs()->is_not_paying() ) {
                    $url = snmwp_fs()->get_upgrade_url();
                    $label = __( 'Upgrade Now!', 'security-ninja-mainwp' );
                    printf( '<a href="%s" class="button button-primary">%s</a>', esc_url( $url ), esc_html( $label ) );
                }
                ?>

									</div>
								</div>
							</div>
						<?php 
            }
            ?>
					</div>

				</div>
			</div>
			<style>
				.snwrap {
					display: flex;
					justify-content: flex-start;
				}

				.snwrap .card ul {
					margin-left: 0px;
					padding-left: 0px;
				}

				.card {
					width: 48%;
					/* Adjusts the width of each card to fit side by side */
					margin: 10px;
					box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
					/* Adds a subtle sh adow */
				}

				.inside {
					padding: 20px 20px 20px 0px;
				}

				.title {
					margin-bottom: 15px;
				}
			</style>


			<?php 
        }

        /**
         * ajax_run_remote_security_tests.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Sunday, December 10th, 2023.
         * @access  public static
         * @global
         * @return  void
         */
        public static function ajax_run_remote_security_tests() {
            check_ajax_referer( 'secnin_nonce' );
            if ( !current_user_can( 'manage_options' ) ) {
                wp_send_json_error( esc_html__( 'You do not have sufficient permissions to perform this action.', 'security-ninja-mainwp' ) );
                return;
            }
            if ( isset( $_POST['site_ids'] ) ) {
                if ( is_array( $_POST['site_ids'] ) ) {
                    $site_ids = array_map( 'intval', $_POST['site_ids'] );
                } else {
                    $site_ids = array(intval( $_POST['site_ids'] ));
                }
            } else {
                $site_ids = array();
            }
            if ( !$site_ids ) {
                wp_send_json_error( esc_html__( 'Site ID not provided.', 'security-ninja-mainwp' ) );
                return;
            }
            foreach ( $site_ids as $site_id ) {
                $information = apply_filters(
                    'mainwp_fetchurlauthed',
                    __FILE__,
                    self::$child_key,
                    $site_id,
                    'extra_execution',
                    array(
                        'action' => 'run_all_tests',
                    )
                );
            }
            return true;
        }

        /**
         * Function to sanitize white label settings
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Wednesday, May 29th, 2024.
         * @access  private static
         * @param   mixed   $settings
         * @return  mixed
         */
        private static function sanitize_white_label_settings( $settings ) {
            // Sanitize each setting
            $sanitized_settings = array(
                'wl_active'         => ( isset( $settings['wl_active'] ) && '1' === $settings['wl_active'] ? '1' : '0' ),
                'wl_newname'        => ( isset( $settings['wl_newname'] ) ? sanitize_text_field( $settings['wl_newname'] ) : '' ),
                'wl_newdesc'        => ( isset( $settings['wl_newdesc'] ) ? sanitize_text_field( $settings['wl_newdesc'] ) : '' ),
                'wl_newauthor'      => ( isset( $settings['wl_newauthor'] ) ? sanitize_text_field( $settings['wl_newauthor'] ) : '' ),
                'wl_newurl'         => ( isset( $settings['wl_newurl'] ) ? esc_url_raw( $settings['wl_newurl'] ) : '' ),
                'wl_newiconurl'     => ( isset( $settings['wl_newiconurl'] ) ? esc_url_raw( $settings['wl_newiconurl'] ) : '' ),
                'wl_newmenuiconurl' => ( isset( $settings['wl_newmenuiconurl'] ) ? esc_url_raw( $settings['wl_newmenuiconurl'] ) : '' ),
            );
            return $sanitized_settings;
        }

        /**
         * do_filter_mainwp_managesites_bulk_actions.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Sunday, December 10th, 2023.
         * @access  public static
         * @global
         * @param   mixed   $actions
         * @return  mixed
         */
        public static function do_filter_mainwp_managesites_bulk_actions( $actions ) {
            $actions['secnin_runtests'] = esc_html__( 'Run Security Tests (Security Ninja)', 'security-ninja-mainwp' );
            return $actions;
        }

        /**
         * Returns the Security Ninja icon in SVG format.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Wednesday, December 6th, 2023.
         * @param   boolean $base64 Default: true
         * @param   string  $color  Default: '82878c'
         * @return  mixed
         */
        public static function get_icon_svg( $base64 = true, $color = '82878c' ) {
            // Validate color with simpler logic (if needed)
            $color = ( ctype_xdigit( $color ) && strlen( $color ) === 6 ? $color : '82878c' );
            // SVG template with reduced indentation
            $svg_template = '<svg viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg"><g fill="#%s"><path d="m171.117 262.277c14.583-.142 25.832 20.664 25.921 35.25.094 15.265-11.418 37.682-26.678 37.227-14.687-.438-23.797-22.605-23.494-37.296.295-14.24 10.095-35.044 24.25-35.181zm151.27.753c14.584-.142 25.832 20.664 25.922 35.25.093 15.265-11.419 37.681-26.679 37.227-14.686-.438-23.797-22.606-23.493-37.296.294-14.24 10.094-35.044 24.25-35.182z"/><path d="m331.348 26.203c0-.107 98.038-7.914 98.038-7.914s-9.219 91.716-10.104 96.592c1.277-3.3 22.717-46.002 22.818-46.002.105 0 53.047 69.799 53.047 69.799l-46.63 42.993c26.6 30.762 41.632 67.951 41.724 107.653.239 103.748-110.253 191.827-245.68 191.091-130.352-.706-239.977-86.977-240.475-188.91-.5-102.38 105.089-191.741 239.663-192.095 38.677-.1 74.34 6.068 105.82 17.154-3.241-16.067-18.22-90.265-18.22-90.36zm-85.421 157.959c-74.098-1.337-161.3 41.627-161.054 105.87.247 63.88 87.825 103.981 160.683 104.125 78.85.154 164.156-41.58 163.722-106.614-.428-64.436-86.566-101.996-163.351-103.381z"/></g></svg>';
            $svg = sprintf( $svg_template, $color );
            if ( $base64 ) {
                return 'data:image/svg+xml;base64,' . base64_encode( $svg );
            }
            return $svg;
        }

        /**
         * Add left menu item
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Sunday, April 7th, 2024.
         * @access  public static
         * @param   mixed   $mwp_left_menu
         * @return  mixed
         */
        public static function do_filter_mainwp_main_menu( $mwp_left_menu ) {
            if ( !is_array( $mwp_left_menu ) || !isset( $mwp_left_menu['leftbar'] ) ) {
                return $mwp_left_menu;
            }
            $url = esc_url( admin_url( 'admin.php?page=Extensions-Security-Ninja-For-Mainwp-Premium' ) );
            $new_menu_item = array(
                'Security Ninja',
                'Extensions-Security-Ninja-Mainwp-Premium',
                $url,
                '',
                '<img src="' . esc_attr( self::get_icon_svg( true, 'e8eef7' ) ) . '" class="secnin-menu-icon" style="margin-bottom:5px;">'
            );
            $position = 3;
            array_splice(
                $mwp_left_menu['leftbar'],
                $position,
                0,
                array($new_menu_item)
            );
            return $mwp_left_menu;
        }

        /**
         * handle_custom_mainwp_site_synced.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Wednesday, December 6th, 2023.
         * @access  public static
         * @param   mixed   $website
         * @param   mixed   $information
         * @return  void
         */
        public static function handle_custom_mainwp_site_synced( $website, $information ) {
            if ( isset( $information['SecNin_get_details'] ) ) {
                $incoming_details = $information['SecNin_get_details'];
                $site_details = array();
                if ( isset( $incoming_details['plan'] ) ) {
                    $site_details['plan'] = $incoming_details['plan'];
                }
                if ( isset( $incoming_details['ver'] ) ) {
                    $site_details['ver'] = $incoming_details['ver'];
                }
                if ( isset( $incoming_details['secret_access'] ) ) {
                    $site_details['secret_access'] = $incoming_details['secret_access'];
                }
                if ( isset( $incoming_details['vulns'] ) ) {
                    $site_details['vulns'] = $incoming_details['vulns'];
                }
                if ( isset( $incoming_details['vulndetails'] ) ) {
                    $site_details['vulndetails'] = $incoming_details['vulndetails'];
                }
                if ( isset( $incoming_details['test_results'] ) ) {
                    $site_details['test_results'] = $incoming_details['test_results'];
                }
                if ( isset( $incoming_details['tests'] ) ) {
                    $site_details['tests'] = $incoming_details['tests'];
                }
                if ( isset( $incoming_details['options'] ) ) {
                    $site_details['options'] = $incoming_details['options'];
                }
                apply_filters(
                    'mainwp_updatewebsiteoptions',
                    false,
                    $website,
                    'secnin_site_details',
                    maybe_serialize( $site_details )
                );
                // *** Saving events locally
                if ( isset( $incoming_details['last_events'] ) && is_array( $incoming_details['last_events'] ) ) {
                    global $wpdb;
                    foreach ( $incoming_details['last_events'] as $lastevent ) {
                        $wpdb->query( $wpdb->prepare(
                            "INSERT IGNORE INTO {$wpdb->prefix}mainwp_security_ninja_events(site_id, timestamp, ip, module, action, user_agent, user_id, description) VALUES (%d, %s, %s, %s, %s, %s, %s, %s)",
                            $website->id,
                            $lastevent['timestamp'],
                            $lastevent['ip'],
                            $lastevent['module'],
                            $lastevent['action'],
                            $lastevent['user_agent'],
                            $lastevent['user_id'],
                            $lastevent['description']
                        ) );
                    }
                }
            }
        }

        /**
         * Ask the sites to include data from Security Ninja
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Wednesday, December 6th, 2023.
         * @access  public static
         * @global
         * @param   mixed   $data
         * @param   mixed   $pWebsite   Default: null
         * @return  mixed
         */
        public static function handle_custom_mainwp_sync_others_data( $data, $pWebsite = null ) {
            if ( !is_array( $data ) ) {
                $data = array();
            }
            $data['SecNin_get_details'] = 1;
            return $data;
        }

        /**
         * activate.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Wednesday, December 6th, 2023.
         * @access  public static
         * @return  void
         */
        public static function security_ninja_mainwp_activate() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            $sql = array();
            $tbl = 'CREATE TABLE `' . $wpdb->prefix . 'mainwp_security_ninja` (
								`id` int(11) NOT NULL AUTO_INCREMENT,
								`site_id` int(11) NOT NULL,
								`site_status` longtext NOT NULL DEFAULT "",
								`settings` longtext NOT NULL DEFAULT "",
								`override` tinyint(1) NOT NULL DEFAULT 0,
								PRIMARY KEY  (`id`)  ';
            $tbl .= ') ' . $charset_collate;
            $sql[] = $tbl;
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            foreach ( $sql as $query ) {
                dbDelta( $query );
            }
            update_option( 'security_ninja_db_version', self::$security_ninja_db_version );
        }

        /**
         * sitestable_getcolumns.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Wednesday, November 29th, 2023.
         * @access  public
         * @param   mixed   $columns
         * @return  mixed
         */
        public static function sitestable_getcolumns( $columns ) {
            $columns['secnin_quickview'] = 'Security Ninja';
            return $columns;
        }

        /**
         * sitestable_item.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Wednesday, November 29th, 2023.
         * @version v1.0.1  Sunday, April 7th, 2024.
         * @access  public static
         * @param   mixed   $item
         * @return  mixed
         */
        public static function sitestable_item( $item ) {
            $site_id = $item['id'];
            $site_details = apply_filters(
                'mainwp_getwebsiteoptions',
                false,
                $site_id,
                'secnin_site_details'
            );
            $site_details = maybe_unserialize( $site_details );
            $output = '';
            if ( $site_details ) {
                if ( isset( $site_details['tests']['output'] ) ) {
                    $output .= esc_html__( 'Test score', 'security-ninja-mainwp' ) . ': ' . esc_html( $site_details['tests']['score'] );
                }
                $vulns_count = intval( $site_details['vulns'] );
                if ( 0 < $vulns_count ) {
                    // translators:
                    $vulns_text = _n(
                        '%s vulnerability',
                        '%s vulnerabilities',
                        $vulns_count,
                        'security-ninja-mainwp'
                    );
                    if ( $output ) {
                        $output .= '<br>';
                    }
                    $output .= '<strong>' . sprintf( esc_attr( $vulns_text ), esc_html( $vulns_count ) ) . '</strong>';
                }
            } else {
                $output .= 'n/a';
            }
            $item['secnin_quickview'] = '<a href="admin.php?page=ManageSitesSecurityNinja&id=' . intval( $item['id'] ) . '" data-tooltip="' . esc_attr__( 'Open Security Ninja Tab', 'security-ninja-mainwp' ) . '" data-position="right center"  data-inverted="">' . wp_kses_post( $output ) . '</a><i class="ui active inline loader tiny" style="display:none"></i><span id="site-status-' . esc_attr( $item['id'] ) . '" class="status hidden"></span>';
            return $item;
        }

        /**
         * wpsn_mainwp_plugins_install_checks.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Wednesday, November 29th, 2023.
         * @access  static
         * @param   mixed   $plugins
         * @return  mixed
         */
        public static function wpsn_mainwp_plugins_install_checks( $plugins ) {
            $plugins[] = array(
                'page' => 'Extensions-Security-Ninja-For-Mainwp',
                'slug' => 'security-ninja-premium/security-ninja.php',
                'name' => 'WP Security Ninja',
            );
            return $plugins;
        }

        /**
         *
         * @param array $sub_page Subpages array.
         *
         * @return array $sub_page Updated subpages array.
         */
        public static function managesites_subpage( $sub_page ) {
            $sub_page[] = array(
                'title'       => 'Security Ninja',
                'slug'        => 'SecurityNinja',
                'sitetab'     => true,
                'menu_hidden' => true,
                'callback'    => array(__CLASS__, 'render_report'),
            );
            return $sub_page;
        }

        /**
         * display_malscan_results.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Saturday, December 9th, 2023.
         * @access  public static
         * @param   mixed   $scan_data
         * @return  mixed
         */
        public static function display_malscan_results( $scan_data ) {
            if ( empty( $scan_data ) ) {
                return '<div class="scan-results">' . __( 'No data available.', 'security-ninja-mainwp' ) . '</div>';
            }
            $items = array();
            $items[] = '<div class="scan-results settings-container">';
            $items[] = '<h3>' . __( 'Scan results', 'security-ninja-mainwp' ) . '</h3>';
            // Handle 'last_run' separately at the top
            if ( !empty( $scan_data['last_run'] ) ) {
                $last_run_formatted = esc_html( gmdate( 'Y-m-d H:i:s', $scan_data['last_run'] ) );
                $time_diff = human_time_diff( $scan_data['last_run'] );
                $items[] = "<div class=\"last-run\"><strong>" . __( 'Last Run', 'security-ninja-mainwp' ) . ':' . "</strong> {$last_run_formatted} ({$time_diff} ago)</div>";
            }
            foreach ( $scan_data['results']['files'] as $file ) {
                // Constructing HTML
                $html = "<div class='settings-section'>";
                $html .= "<h3 class='section-title collapsible file-path'>{$file['path']}</h3>";
                $html .= "<div class='section-content'>";
                $html .= "<ul class='file-details'>";
                $html .= "<li class='file-pattern'>" . __( 'Mached pattern', 'security-ninja-mainwp' ) . ' ' . "<code>{$file['pattern']}</code></li>";
                $html .= "<li class='file-line'>" . __( 'Linenumber', 'security-ninja-mainwp' ) . ": {$file['line']}</li>";
                $html .= "<li class='file-comment'>" . __( 'Comment', 'security-ninja-mainwp' ) . ": {$file['comment']}</li>";
                $html .= "<li><code class='file-matchedline'>{$file['matchedline']}</code></li>";
                $html .= '</ul></div></div>';
                $items[] = $html;
            }
            $items[] = '</div>';
            // End of scan-results
            return implode( '', $items );
        }

        /**
         * display_settings.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Wednesday, April 3rd, 2024.
         * @access  public static
         * @global
         * @param   mixed   $scan_data
         * @return  void
         */
        public static function display_settings( $settings ) {
            $output = '<div class="settings-container">';
            // Updated array for boolean interpretation.
            $boolean_interpretation = array(
                'firewall'   => array(
                    'active',
                    'trackvisits',
                    'globalbannetwork',
                    'global',
                    'usecloud',
                    'protect_login_form',
                    'hide_login_errors',
                    'blockadminlogin',
                    'change_login_url',
                    '2fa_enabled',
                    '2fa_required_roles',
                    '2fa_methods',
                    '2fa_grace_period',
                    '2fa_backup_codes_enabled',
                    '2fa_intro',
                    '2fa_enter_code'
                ),
                'vulns'      => array(
                    'enable_vulns',
                    'enable_admin_notification',
                    'enable_outdated',
                    'enable_email_notice'
                ),
                'whitelabel' => array('wl_active'),
                'fixes'      => array(
                    'hide_wp',
                    'hide_wlw',
                    'hide_php_ver',
                    'hide_server',
                    'disable_editors',
                    'disable_wp_debug',
                    'enable_xcto',
                    'enable_xfo',
                    'enable_xxp',
                    'enable_sts',
                    'enable_rp',
                    'enable_fp',
                    'enable_csp',
                    'disable_wp_sitemaps',
                    'disable_username_enumeration',
                    'hide_wp_debug',
                    'application_passwords',
                    'remove_unwanted_files',
                    'secure_cookies'
                ),
            );
            foreach ( $settings as $section => $values ) {
                $output .= '<div class="settings-section">';
                $output .= '<h2 class="section-title collapsible">' . esc_html( $section ) . '</h2>';
                $output .= '<div class="section-content" style="display:none;">';
                if ( is_array( $values ) ) {
                    $output .= '<ul class="settings-list">';
                    foreach ( $values as $key => $value ) {
                        $output .= '<li class="setting"><strong>' . esc_html( $key ) . ':</strong> ';
                        if ( in_array( $key, $boolean_interpretation[$section] ?? array(), true ) ) {
                            $value = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
                            $icon = ( $value ? '<span class="status-icon enabled-icon">✔</span>' : '<span class="status-icon disabled-icon">✖</span>' );
                            $output .= $icon;
                        } else {
                            $output .= ( is_array( $value ) ? esc_html( implode( ', ', $value ) ) : esc_html( $value ) );
                        }
                        $output .= '</li>';
                    }
                    $output .= '</ul>';
                } else {
                    $output .= esc_html( $values );
                }
                $output .= '</div></div>';
                // End section-content and settings-section.
            }
            $output .= '</div>';
            // End settings-container.
            return $output;
        }

        /**
         * display_scan_results.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Thursday, December 21st, 2023.
         * @access  public static
         * @param   mixed   $settings
         * @return  mixed
         */
        public static function display_scan_results( $scan_data ) {
            if ( empty( $scan_data ) ) {
                return '<div class="scan-results">' . esc_html__( 'No data available.', 'security-ninja-mainwp' ) . '</div>';
            }
            $output = '<div class="scan-results">';
            if ( isset( $scan_data['last_run'] ) ) {
                $last_run_formatted = esc_html( gmdate( 'Y-m-d H:i:s', $scan_data['last_run'] ) );
                $time_diff = human_time_diff( $scan_data['last_run'] );
                $output .= '<div class="last-run"><strong>' . esc_html__( 'Last Run:', 'security-ninja-mainwp' ) . '</strong> ' . $last_run_formatted . ' (' . sprintf( esc_html__( '%s ago', 'security-ninja-mainwp' ), $time_diff ) . ')</div>';
            }
            foreach ( $scan_data as $key => $value ) {
                if ( 'last_run' === $key ) {
                    continue;
                }
                $output .= '<div class="scan-item">';
                $output .= '<strong class="scan-key">' . esc_html( $key ) . ':</strong> ';
                if ( is_array( $value ) ) {
                    $output .= '<ul class="scan-array">';
                    foreach ( $value as $item ) {
                        $output .= '<li>' . esc_html( (string) $item ) . '</li>';
                    }
                    $output .= '</ul>';
                } else {
                    $output .= '<span class="scan-value">' . esc_html( (string) $value ) . '</span>';
                }
                $output .= '</div>';
            }
            $output .= '</div>';
            return $output;
        }

        /**
         * render_report.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Saturday, December 9th, 2023.
         * @access  public static
         * @return  void
         */
        public static function render_report() {
            do_action( 'mainwp_pageheader_sites', 'SecurityNinja' );
            $site_id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
            $site_id = ( false !== $site_id ? $site_id : 0 );
            global $security_ninja_mainwp;
            if ( $site_id ) {
                $website = apply_filters(
                    'mainwp_getsites',
                    $security_ninja_mainwp->get_child_file(),
                    $security_ninja_mainwp->get_child_key(),
                    $site_id
                );
                if ( $website && is_array( $website ) ) {
                    $website = current( $website );
                }
                $site_details = apply_filters(
                    'mainwp_getwebsiteoptions',
                    false,
                    $site_id,
                    'secnin_site_details'
                );
                $site_details = maybe_unserialize( $site_details );
            }
            if ( !$website ) {
                esc_html_e( 'Site not found.', 'security-ninja-mainwp' );
            } else {
                ?>
				<div class="ui segment">
					<div class="ui hidden divider"></div>
					<div class="ui grid">
						<div class="one wide column">
							<img src="<?php 
                echo self::get_icon_svg( true, '000000' );
                ?>" style="margin-bottom:2px;max-width:55px;">
						</div>
						<div class="twelve wide middle aligned column">
							<h2 class="ui header">WP Security Ninja</h2>
						</div>
						<div class="three wide left aligned middle aligned column">
							<?php 
                if ( $site_details && isset( $site_details['ver'] ) && isset( $site_details['plan'] ) ) {
                    // translators:
                    $format_string = __( 'This website reports using Security Ninja %1$s version %2$s', 'security-ninja-mainwp' );
                    printf( esc_html( $format_string ), esc_html( $site_details['plan'] ), esc_html( $site_details['ver'] ) );
                }
                ?>
						</div>
					</div>
					<div class="ui relaxed divided list">
						<?php 
                echo '<h3>' . esc_html__( 'Vulnerabilities', 'security-ninja-mainwp' ) . '</h3>';
                $vulndetails = false;
                if ( $site_details && isset( $site_details['vulndetails'] ) ) {
                    $vulndetails = $site_details['vulndetails'];
                    if ( is_array( $vulndetails ) && (!empty( $vulndetails['plugins'] ) || !empty( $vulndetails['themes'] )) ) {
                        ?>
								<div class="settings-container">
									<?php 
                        if ( isset( $vulndetails['plugins'] ) ) {
                            echo '<h3>' . esc_html__( 'Plugin Vulnerabilities', 'security-ninja-mainwp' ) . '</h3>';
                            foreach ( $vulndetails['plugins'] as $pluginvuln ) {
                                ?>
											<div class="settings-section">
												<h2 class="section-title collapsible red">
													<?php 
                                echo sprintf( 
                                    /* translators: 1: Plugin name, 2: Version number */
                                    esc_html__( '%1$s <small>v.%2$s</small>', 'security-ninja-mainwp' ),
                                    esc_html( $pluginvuln['name'] ),
                                    esc_attr( $pluginvuln['installedVersion'] )
                                 );
                                ?>
												</h2>
												<div class="section-content"><?php 
                                echo esc_html( $pluginvuln['desc'] );
                                ?></div>
											</div>
										<?php 
                            }
                        }
                        if ( isset( $vulndetails['themes'] ) ) {
                            echo '<h3>' . esc_html__( 'Theme Vulnerabilities', 'security-ninja-mainwp' ) . '</h3>';
                            foreach ( $vulndetails['themes'] as $themevuln ) {
                                ?>

											<div class="item">
												<div class="content">
													<h2 class="section-title collapsible red">
														<?php 
                                echo sprintf( 
                                    /* translators: 1: Theme name, 2: Version number */
                                    esc_html__( '%1$s <small>v.%2$s</small>', 'security-ninja-mainwp' ),
                                    esc_html( $themevuln['name'] ),
                                    esc_attr( $themevuln['installedVersion'] )
                                 );
                                ?>
													</h2>
													<div class="section-content">
														<?php 
                                echo esc_html( $themevuln['desc'] );
                                ?>
													</div>
												</div>
											</div>
									<?php 
                            }
                        }
                        ?>
								</div>
						<?php 
                    } else {
                        echo '<div class="scan-results">' . esc_html__( 'No vulnerabilities found.', 'security-ninja-mainwp' ) . '</div>';
                    }
                }
                ?>
						<div class="settings-container">
							<?php 
                echo '<h3>' . esc_html__( 'Security Tests', 'security-ninja-mainwp' ) . '</h3>';
                if ( isset( $site_details['test_results']['test'] ) && is_array( $site_details['test_results']['test'] ) ) {
                    echo '<table>';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>' . esc_html__( 'Status', 'security-ninja-mainwp' ) . '</th>';
                    echo '<th>' . esc_html__( 'Title', 'security-ninja-mainwp' ) . '</th>';
                    echo '<th>' . esc_html__( 'Timestamp', 'security-ninja-mainwp' ) . '</th>';
                    echo '<th>' . esc_html__( 'Message', 'security-ninja-mainwp' ) . '</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    if ( isset( $site_details['test_results']['test'] ) ) {
                        foreach ( $site_details['test_results']['test'] as $test ) {
                            // Calculate time since the test was run
                            $time_since = human_time_diff( strtotime( $test['timestamp'] ), current_time( 'timestamp' ) ) . ' ago';
                            // Check if timestamp is '0000-00-00 00:00:00', then set $time_since to 'N/A'
                            if ( '0000-00-00 00:00:00' === $test['timestamp'] ) {
                                $time_since = 'N/A';
                            }
                            // Determine row color based on status
                            $row_color = '#ffffff';
                            // default to white
                            if ( '10' === $test['status'] ) {
                                $row_color = '#d4edda';
                                // green for passed tests
                            } elseif ( '0' === $test['status'] && '0000-00-00 00:00:00' === $test['timestamp'] ) {
                                $row_color = '#dcdcdc';
                                // for tests that have never run
                            } elseif ( '0' === $test['status'] ) {
                                $row_color = '#f8d7da';
                                // red for failed tests
                            } elseif ( '5' === $test['status'] ) {
                                $row_color = '#fff3cd';
                                // yellow for warnings
                            }
                            echo '<tr style="background-color: ' . esc_attr( $row_color ) . ';">';
                            echo '<td>' . esc_html( $test['status'] ) . '</td>';
                            echo '<td>' . esc_html( $test['title'] ) . '</td>';
                            echo '<td>' . wp_kses_post( $time_since . '</br><small>' . $test['timestamp'] . '</small>' ) . '</td>';
                            echo '<td>' . esc_html( $test['msg'] ) . '</td>';
                            echo '</tr>';
                        }
                    }
                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<p>' . esc_html__( 'No test results found', 'security-ninja-mainwp' ) . '</p>';
                }
                ?>


						</div>


					</div>
				<?php 
            }
            do_action( 'mainwp_pagefooter_sites', 'SecurityNinja' );
        }

        /**
         * secnin_mainwp_pro_reports_custom_tokens.
         *
         * @author   Lars Koudal
         * @since    v0.0.1
         * @version  v1.0.0  Friday, April 8th, 2022.
         * @param    mixed   $tokensValues
         * @param    mixed   $report
         * @param    mixed   $website
         * @return   mixed
         */
        public static function secnin_mainwp_pro_reports_custom_tokens( $tokens_values, $report, $website ) {
            $information = apply_filters(
                'mainwp_fetchurlauthed',
                __FILE__,
                self::$child_key,
                $website,
                'extra_execution',
                array(
                    'action' => 'get_test_results',
                )
            );
            if ( !$information ) {
                return $tokens_values;
            }
            if ( is_array( $tokens_values ) && isset( $tokens_values['[securityninja.score]'] ) ) {
                $tokens_values['[securityninja.score]'] = $information['score'];
            }
            if ( is_array( $tokens_values ) && isset( $tokens_values['[securityninja.vulnerabilities]'] ) ) {
                $tokens_values['[securityninja.vulnerabilities]'] = $information['vulns'];
            }
            return $tokens_values;
        }

        /**
         * enqueue_scripts.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Thursday, March 10th, 2022.
         * @access  public
         * @return  void
         */
        public static function enqueue_scripts() {
            wp_register_script(
                'security-ninja-mainwp-extension',
                self::$plugin_url . 'js/security-ninja-mainwp-min.js',
                array('jquery', 'datatables'),
                '2.0',
                true
            );
            $js_vars = array(
                'nonce_secnin' => wp_create_nonce( 'secnin_nonce' ),
                'texts'        => array(
                    'no_events'                      => esc_html__( 'It looks boring here, right? Please synchronize some websites with Security Ninja Premium installed.', 'security-ninja-mainwp' ),
                    'loading'                        => esc_html__( 'Loading...', 'security-ninja-mainwp' ),
                    'headline'                       => esc_html__( 'Change white label settings', 'security-ninja-mainwp' ),
                    'subline'                        => esc_html__( 'Customize the white label settings for Security Ninja Premium websites', 'security-ninja-mainwp' ),
                    'enableWhiteLabel'               => esc_html__( 'Enable white label', 'security-ninja-mainwp' ),
                    'disableWhiteLabel'              => esc_html__( 'Disable white label', 'security-ninja-mainwp' ),
                    'pluginName'                     => __( 'Plugin Name*', 'security-ninja-mainwp' ),
                    'enterPluginName'                => __( 'Enter plugin name*', 'security-ninja-mainwp' ),
                    'pluginDescription'              => __( 'Plugin Description*', 'security-ninja-mainwp' ),
                    'enterPluginDescription'         => __( 'Enter plugin description*', 'security-ninja-mainwp' ),
                    'authorName'                     => __( 'Author Name*', 'security-ninja-mainwp' ),
                    'enterAuthorName'                => __( 'Enter author name*', 'security-ninja-mainwp' ),
                    'authorURL'                      => __( 'Author URL*', 'security-ninja-mainwp' ),
                    'enterAuthorURL'                 => __( 'Enter author URL*', 'security-ninja-mainwp' ),
                    'pluginIconURL'                  => __( 'Plugin Icon URL', 'security-ninja-mainwp' ),
                    'enterPluginIconURL'             => __( 'Enter plugin icon URL', 'security-ninja-mainwp' ),
                    'pluginMenuIconURL'              => __( 'Plugin Menu Icon URL', 'security-ninja-mainwp' ),
                    'enterPluginMenuIconURL'         => __( 'Enter plugin menu icon URL', 'security-ninja-mainwp' ),
                    'close'                          => __( 'Close', 'security-ninja-mainwp' ),
                    'warningDisable'                 => __( 'Warning: Disabling white label will remove custom branding from all selected sites.', 'security-ninja-mainwp' ),
                    'sendToSelectedSites'            => __( 'Send to selected sites', 'security-ninja-mainwp' ),
                    'pleaseFillInAllRequiredFields'  => __( 'Please fill in all required fields.', 'security-ninja-mainwp' ),
                    'settingsUpdatedSuccessfully'    => __( 'Settings updated successfully.', 'security-ninja-mainwp' ),
                    'anErrorOccurred'                => __( 'An error occurred. Please try again later.', 'security-ninja-mainwp' ),
                    'runAllSecurityTests'            => __( 'You are about to run all security tests on the selected sites?', 'security-ninja-mainwp' ),
                    'runMalwareScansOnSelectedSites' => __( 'You are about to run malware scans on the selected sites?', 'security-ninja-mainwp' ),
                ),
            );
            wp_localize_script( 'security-ninja-mainwp-extension', 'secninja_mainwp', $js_vars );
            wp_enqueue_script( 'security-ninja-mainwp-extension' );
            wp_enqueue_style(
                'security-ninja-mainwp-extension',
                self::$plugin_url . 'css/security-ninja-mainwp.css',
                array(),
                '1.0'
            );
        }

        /**
         * custom_page_title.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Saturday, April 2nd, 2022.
         * @version v1.0.1  Wednesday, April 24th, 2024.
         * @access  public static
         * @param   mixed   $title
         * @return  mixed
         */
        public static function custom_page_title( $title ) {
            $page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS );
            if ( 'Extensions-Security-Ninja-For-Mainwp' === $page ) {
                $logo = '<img src="' . esc_url( self::$plugin_url . 'images/sn-logo.svg' ) . '" height="40" alt="Visit wpsecurityninja.com" class="logoleft"></a> ';
                $prod_title = '<span>' . esc_html__( 'Security Ninja for MainWP', 'security-ninja-mainwp' ) . '</span>';
                $title = $logo . $prod_title;
            }
            return $title;
        }

        /**
         * admin_init.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Thursday, March 10th, 2022.
         * @return  void
         */
        public static function admin_init() {
            if ( 'yes' === get_option( 'security_ninja_mainwp_activated' ) ) {
                delete_option( 'security_ninja_mainwp_activated' );
                wp_safe_redirect( admin_url( 'admin.php?page=Extensions' ) );
                exit;
            }
        }

        /**
         * mainwp_extension_autoload.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Thursday, March 10th, 2022.
         * @param   mixed   $class_name
         * @return  void
         */
        public static function mainwp_extension_autoload( $class_name ) {
            $allowed_loading_types = array('class', 'page');
            foreach ( $allowed_loading_types as $allowed_loading_type ) {
                $class_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) . $allowed_loading_type . DIRECTORY_SEPARATOR . $class_name . '.' . $allowed_loading_type . '.php';
                if ( file_exists( $class_file ) ) {
                    require_once $class_file;
                }
            }
        }

        /**
         * get_this_extension.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Thursday, March 10th, 2022.
         * @param   mixed   $p_array
         * @return  mixed
         */
        public static function get_this_extension( $p_array ) {
            $p_array[] = array(
                'plugin'     => __FILE__,
                'mainwp'     => false,
                'apiManager' => false,
                'callback'   => array(__CLASS__, 'settings'),
            );
            return $p_array;
        }

        /**
         * settings.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Thursday, March 10th, 2022.
         * @return  void
         */
        public static function settings() {
            do_action( 'mainwp_pageheader_extensions', __FILE__ );
            if ( self::$child_enabled ) {
                self::mainwp_extension_autoload( 'SecurityNinjaMainWPExtension' );
                SecurityNinjaMainWPExtension::render_page();
            }
            do_action( 'mainwp_pagefooter_extensions', __FILE__ );
        }

        /**
         * Activation Routines
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Thursday, March 10th, 2022.
         * @return  boolean
         */
        public static function activate_this_plugin() {
            // self::security_ninja_mainwp_activate(); // should run only on activation, not on every page load
            //Checking if the MainWP plugin is enabled. This filter will return true if the main plugin is activated.
            self::$mainwp_main_activated = apply_filters( 'mainwp_activated_check', self::$mainwp_main_activated );
            // The 'mainwp_extension_enabled_check' hook. If the plugin is not enabled this will return false,
            // if the plugin is enabled, an array will be returned containing a key.
            // This key is used for some data requests to our main
            self::$child_enabled = apply_filters( 'mainwp_extension_enabled_check', __FILE__ );
            if ( !self::$child_enabled ) {
                return;
            }
            self::$child_key = self::$child_enabled['key'];
        }

        /**
         * mainwp_error_notice.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Thursday, March 10th, 2022.
         * @version v1.0.1  Wednesday, December 6th, 2023.
         * @access  public static
         * @return  void
         */
        public static function mainwp_error_notice() {
            global $current_screen;
            if ( 'plugins' === $current_screen->parent_base && false === self::$mainwp_main_activated ) {
                ?>
					<div class="error">
						<p><?php 
                echo sprintf( 
                    /* translators: %s: URL for the MainWP plugin */
                    esc_html__( 'This extension requires the %sMainWP%s Plugin to be activated in order to work.', 'security-ninja-mainwp' ),
                    '<a href="https://mainwp.com/" target="_blank" rel="noopener">',
                    '</a>'
                 );
                ?></p>
					</div>
	<?php 
            }
        }

        /**
         * get_child_key.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Thursday, March 10th, 2022.
         * @access  public
         * @return  mixed
         */
        public static function get_child_key() {
            return self::$child_key;
        }

        /**
         * get_child_file.
         *
         * @author  Lars Koudal
         * @since   v0.0.1
         * @version v1.0.0  Thursday, March 10th, 2022.
         * @access  public
         * @return  mixed
         */
        public static function get_child_file() {
            return self::$child_file;
        }

    }

}
$security_ninja_mainwp = new security_ninja_mainwp();
register_activation_hook( __FILE__, array(__NAMESPACE__ . '\\security_ninja_mainwp', 'security_ninja_mainwp_activate') );