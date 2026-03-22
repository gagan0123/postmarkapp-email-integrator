<?php
/**
 * Plugin Name: Postmarkapp Email Integrator
 * Plugin URI: https://wordpress.org/plugins/postmarkapp-email-integrator/
 * Description: Overwrites wp_mail to send emails through Postmark. This plugin is a bug fixed edition of the official Postmarkapp plugin
 * Author: Gagan Deep Singh
 * Version: 2.5
 * Author URI: https://gagan.pro
 * Text Domain: postmarkapp-email-integrator
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define
if ( ! defined( 'POSTMARKAPP_ENDPOINT' ) ) {
	define( 'POSTMARKAPP_ENDPOINT', 'https://api.postmarkapp.com/email' );
}

// Admin Functionality
add_action( 'admin_menu', 'pma_admin_menu' );

/**
 * Imports the settings of the official postmark plugin to this plugin.
 */
function pma_import_settings() {
	$options = array(
		'postmarkapp_api_key'        => 'postmark_api_key',
		'postmarkapp_sender_address' => 'postmark_sender_address',
		'postmarkapp_force_html'     => 'postmark_force_html',
		'postmarkapp_trackopens'     => 'postmark_trackopens',
	);
	foreach ( $options as $here => $there ) {
		update_option( $here, get_option( $there ) );
	}
}

function pma_plugin_activate() {
	if ( get_option( 'postmarkapp_api_key' ) === false ) {
		pma_import_settings();
	}
}

register_activation_hook( __FILE__, 'pma_plugin_activate' );

function pma_admin_menu() {
	add_options_page( 'Postmarkapp', 'Postmarkapp', 'manage_options', 'pma_admin', 'pma_admin_options' );
}

function pma_admin_action_links( $links, $file ) {
	static $pma_plugin;
	if ( ! $pma_plugin ) {
		$pma_plugin = plugin_basename( __FILE__ );
	}
	if ( $file === $pma_plugin ) {
		$settings_link = '<a href="options-general.php?page=pma_admin">' . esc_html__( 'Settings', 'postmarkapp-email-integrator' ) . '</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}

add_filter( 'plugin_action_links', 'pma_admin_action_links', 10, 2 );

/**
 * Enqueue admin scripts for the Postmarkapp settings page.
 *
 * @param string $hook_suffix The current admin page hook suffix.
 */
function pma_admin_enqueue_scripts( $hook_suffix ) {
	if ( 'settings_page_pma_admin' !== $hook_suffix ) {
		return;
	}
	wp_enqueue_script(
		'pma-admin-js',
		plugin_dir_url( __FILE__ ) . 'js/pma-admin.js',
		array( 'jquery' ),
		'2.5',
		true
	);
	wp_localize_script(
		'pma-admin-js',
		'pmaAdmin',
		array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'testNonce'   => wp_create_nonce( 'pma_test_email' ),
			'importNonce' => wp_create_nonce( 'pma_import_settings' ),
		)
	);
}

add_action( 'admin_enqueue_scripts', 'pma_admin_enqueue_scripts' );

function pma_admin_options() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'postmarkapp-email-integrator' ) );
	}

	$msg_updated = '';

	if ( isset( $_POST['submit'] ) && 'Save' === $_POST['submit'] ) {

		if ( ! isset( $_POST['pma_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pma_settings_nonce'] ) ), 'pma_save_settings' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'postmarkapp-email-integrator' ) );
		}

		$pma_enabled = ( isset( $_POST['pma_enabled'] ) && $_POST['pma_enabled'] ) ? 1 : 0;

		$api_key      = isset( $_POST['pma_api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['pma_api_key'] ) ) : '';
		$sender_email = isset( $_POST['pma_sender_address'] ) ? sanitize_email( wp_unslash( $_POST['pma_sender_address'] ) ) : '';

		if ( ! empty( $sender_email ) && ! is_email( $sender_email ) ) {
			$sender_email = '';
		}

		$pma_forcehtml = ( isset( $_POST['pma_forcehtml'] ) && $_POST['pma_forcehtml'] ) ? 1 : 0;

		$pma_trackopens = ( isset( $_POST['pma_trackopens'] ) && $_POST['pma_trackopens'] ) ? 1 : 0;
		if ( $pma_trackopens ) {
			$pma_forcehtml = 1;
		}

		update_option( 'postmarkapp_enabled', $pma_enabled );
		update_option( 'postmarkapp_api_key', $api_key );
		update_option( 'postmarkapp_sender_address', $sender_email );
		update_option( 'postmarkapp_force_html', $pma_forcehtml );
		update_option( 'postmarkapp_trackopens', $pma_trackopens );

		$msg_updated = __( 'Postmarkapp settings have been saved.', 'postmarkapp-email-integrator' );
	}
	?>

	<div class="wrap">

		<?php if ( ! empty( $msg_updated ) ) : ?>
			<div class="updated"><p><?php echo esc_html( $msg_updated ); ?></p></div>
			<?php
	endif;
		?>

		<div id="icon-tools" class="icon32"></div>
		<h2><?php esc_html_e( 'Postmarkapp Settings', 'postmarkapp-email-integrator' ); ?></h2>
		<h3><?php esc_html_e( 'What is Postmark?', 'postmarkapp-email-integrator' ); ?></h3>
		<p><?php esc_html_e( 'This plugin enables WordPress blogs of any size to deliver and track WordPress notification emails reliably, with minimal setup time and zero maintenance.', 'postmarkapp-email-integrator' ); ?></p>
		<p>
			<?php
			printf(
			/* translators: %s: Postmark sign up link */
				esc_html__( 'If you don\'t already have a free Postmark account, %s. Every account comes with thousands of free sends.', 'postmarkapp-email-integrator' ),
				'<a href="https://postmarkapp.com/sign_up">' . esc_html__( 'you can get one in minutes', 'postmarkapp-email-integrator' ) . '</a>'
			);
			?>
		</p>

		<br />

		<h3><?php esc_html_e( 'Your Postmark Settings', 'postmarkapp-email-integrator' ); ?></h3>
		<form method="post" action="options-general.php?page=pma_admin">
			<?php wp_nonce_field( 'pma_save_settings', 'pma_settings_nonce' ); ?>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="pma_enabled"><?php esc_html_e( 'Send using Postmark', 'postmarkapp-email-integrator' ); ?></label></th>
						<td><input name="pma_enabled" id="pma_enabled" type="checkbox" value="1"<?php checked( get_option( 'postmarkapp_enabled' ), 1 ); ?>/> <span class="description"><?php esc_html_e( 'Sends emails sent using wp_mail via Postmark.', 'postmarkapp-email-integrator' ); ?></span></td>
					</tr>
					<tr>
						<th><label for="pma_api_key"><?php esc_html_e( 'Postmark API Key', 'postmarkapp-email-integrator' ); ?></label></th>
						<td><input name="pma_api_key" id="pma_api_key" type="text" value="<?php echo esc_attr( get_option( 'postmarkapp_api_key' ) ); ?>" class="regular-text"/> <br/><span class="description">
						<?php
						printf(
						/* translators: %s: Link to create a Postmark server */
							esc_html__( 'Your API key is available in the credentials screen of your Postmark server. %s.', 'postmarkapp-email-integrator' ),
							'<a href="https://postmarkapp.com/servers/">' . esc_html__( 'Create a new server in Postmark', 'postmarkapp-email-integrator' ) . '</a>'
						);
						?>
																							</span></td>
					</tr>
					<tr>
						<th><label for="pma_sender_address"><?php esc_html_e( 'Sender Email Address', 'postmarkapp-email-integrator' ); ?></label></th>
						<td><input name="pma_sender_address" id="pma_sender_address" type="email" value="<?php echo esc_attr( get_option( 'postmarkapp_sender_address' ) ); ?>" class="regular-text"/> <br/><span class="description">
						<?php
						printf(
						/* translators: %s: Link to set up sender signatures */
							esc_html__( 'This email needs to be one of your verified sender signatures. It will appear as the "from" email on all outbound messages. %s.', 'postmarkapp-email-integrator' ),
							'<a href="https://postmarkapp.com/signatures">' . esc_html__( 'Set one up in Postmark', 'postmarkapp-email-integrator' ) . '</a>'
						);
						?>
																											</span></td>
					</tr>
					<tr>
						<th><label for="pma_forcehtml"><?php esc_html_e( 'Force HTML', 'postmarkapp-email-integrator' ); ?></label></th>
						<td><input name="pma_forcehtml" id="pma_forcehtml" type="checkbox" value="1"<?php checked( get_option( 'postmarkapp_force_html' ), 1 ); ?>/> <span class="description"><?php esc_html_e( 'Force all emails to be sent as HTML.', 'postmarkapp-email-integrator' ); ?></span></td>
					</tr>
					<tr>
						<th><label for="pma_trackopens"><?php esc_html_e( 'Track Opens', 'postmarkapp-email-integrator' ); ?></label></th>
						<td><input name="pma_trackopens" id="pma_trackopens" type="checkbox" value="1"<?php checked( get_option( 'postmarkapp_trackopens' ), 1 ); ?>/> <span class="description"><?php esc_html_e( 'Use Postmark\'s Open Tracking feature to capture open events. (Forces Html option to be turned on)', 'postmarkapp-email-integrator' ); ?></span></td>
					</tr>
				</tbody>
			</table>
			<div class="submit">
				<input type="submit" name="submit" value="<?php esc_attr_e( 'Save', 'postmarkapp-email-integrator' ); ?>" class="button-primary" />
			</div>
		</form>

		<br />

		<h3><?php esc_html_e( 'Test Postmark Sending', 'postmarkapp-email-integrator' ); ?></h3>
		<form method="post" id="test-form" action="pma_admin_test">
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="pma_test_address"><?php esc_html_e( 'Send a Test Email To', 'postmarkapp-email-integrator' ); ?></label></th>
						<td><input name="pma_test_address" id="pma_test_address" type="email" value="<?php echo esc_attr( get_option( 'postmarkapp_sender_address' ) ); ?>" class="regular-text"/></td>
					</tr>
				</tbody>
			</table>
			<div class="submit">
				<input type="submit" name="submit" value="<?php esc_attr_e( 'Send Test Email', 'postmarkapp-email-integrator' ); ?>" class="button-primary" />
			</div>
			<div class="submit">
				<input id="pma_import_button" type="button" value="<?php esc_attr_e( 'Import Settings', 'postmarkapp-email-integrator' ); ?>" class="button-secondary" />
			</div>
		</form>

	</div>

	<?php
}

add_action( 'wp_ajax_pma_admin_test', 'pma_admin_test_ajax' );

function pma_admin_test_ajax() {
	check_ajax_referer( 'pma_test_email', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'postmarkapp-email-integrator' ) );
	}

	$response = pma_send_test();
	echo esc_html( $response );
	wp_die();
}

// Override wp_mail() if postmark enabled
if ( get_option( 'postmarkapp_enabled' ) == 1 ) {
	if ( ! function_exists( 'wp_mail' ) ) {

		function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {

			$atts = apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );

			$to          = $atts['to'];
			$subject     = $atts['subject'];
			$message     = $atts['message'];
			$headers     = $atts['headers'];
			$attachments = $atts['attachments'];

			$recognized_headers = pma_parse_headers( $headers );

			// Adding the filter thats executed in the wp_mail function so that plugins using those filters will not clash
			$recognized_headers['Content-Type'] = apply_filters( 'wp_mail_content_type', isset( $recognized_headers['Content-Type'] ) ? $recognized_headers['Content-Type'] : 'text/plain' );

			if ( isset( $recognized_headers['Content-Type'] ) && stripos( $recognized_headers['Content-Type'], 'text/html' ) !== false ) {
				$current_email_type = 'HTML';
			} else {
				$current_email_type = 'PLAINTEXT';
			}

			// Define Headers
			$postmark_headers = array(
				'Accept'                  => 'application/json',
				'Content-Type'            => 'application/json',
				'X-Postmark-Server-Token' => get_option( 'postmarkapp_api_key' ),
			);

			// Send Email
			if ( is_array( $to ) ) {
				$recipients = implode( ',', $to );
			} else {
				$recipients = $to;
			}

			// Construct Message
			$email             = array();
			$email['To']       = $recipients;
			$email['From']     = get_option( 'postmarkapp_sender_address' );
			$email['Subject']  = $subject;
			$email['TextBody'] = $message;

			if ( isset( $recognized_headers['Cc'] ) && ! empty( $recognized_headers['Cc'] ) ) {
				$email['Cc'] = $recognized_headers['Cc'];
			}

			if ( isset( $recognized_headers['Bcc'] ) && ! empty( $recognized_headers['Bcc'] ) ) {
				$email['Bcc'] = $recognized_headers['Bcc'];
			}

			if ( isset( $recognized_headers['Reply-To'] ) && ! empty( $recognized_headers['Reply-To'] ) ) {
				$email['ReplyTo'] = $recognized_headers['Reply-To'];
			}

			if ( 'HTML' === $current_email_type ) {
				$email['HtmlBody'] = $message;
			} elseif ( get_option( 'postmarkapp_force_html' ) == 1 || get_option( 'postmarkapp_trackopens' ) == 1 ) {
				$email['HtmlBody'] = pma_convert_plaintext_to_html( $message );
			}

			if ( get_option( 'postmarkapp_trackopens' ) == 1 ) {
				$email['TrackOpens'] = 'true';
			}

			$response = pma_send_mail( $postmark_headers, $email );

			if ( is_wp_error( $response ) ) {
				return false;
			}
			return true;
		}
	}
}

function pma_convert_plaintext_to_html( $message ) {
	return nl2br( htmlspecialchars( $message ) );
}

/**
 * Parses the $headers string or array and creates a recognizable headers array.
 *
 * @param string|array $headers Email headers.
 * @return array Parsed headers.
 */
function pma_parse_headers( $headers ) {
	if ( ! is_array( $headers ) ) {
		if ( stripos( $headers, "\r\n" ) !== false ) {
			$headers = explode( "\r\n", $headers );
		} else {
			$headers = explode( "\n", $headers );
		}
	}
	$recognized_headers     = array();
	$headers_list           = array(
		'Content-Type' => array(),
		'Bcc'          => array(),
		'Cc'           => array(),
		'Reply-To'     => array(),
	);
	$headers_list_lowercase = array_change_key_case( $headers_list, CASE_LOWER );
	if ( ! empty( $headers ) ) {
		foreach ( $headers as $key => $header ) {
			$key = strtolower( $key );
			if ( array_key_exists( $key, $headers_list_lowercase ) ) {
				$header_key = $key;
				$header_val = $header;
				$segments   = explode( ':', $header );
				if ( count( $segments ) === 2 ) {
					if ( array_key_exists( strtolower( $segments[0] ), $headers_list_lowercase ) ) {
						list($header_key, $header_val) = $segments;
						$header_key                    = strtolower( $header_key );
					}
				}
			} else {
				$segments = explode( ':', $header );
				if ( count( $segments ) === 2 ) {
					if ( array_key_exists( strtolower( $segments[0] ), $headers_list_lowercase ) ) {
						list($header_key, $header_val) = $segments;
						$header_key                    = strtolower( $header_key );
					}
				}
			}
			if ( isset( $header_key ) && isset( $header_val ) ) {
				if ( stripos( $header_val, ',' ) === false ) {
					$headers_list_lowercase[ $header_key ][] = trim( $header_val );
				} else {
					$vals = explode( ',', $header_val );
					foreach ( $vals as $val ) {
						$headers_list_lowercase[ $header_key ][] = trim( $val );
					}
				}
				unset( $header_key );
				unset( $header_val );
			}
		}

		foreach ( $headers_list as $key => $value ) {
			$value = $headers_list_lowercase[ strtolower( $key ) ];
			if ( count( $value ) > 0 ) {
				$recognized_headers[ $key ] = implode( ', ', $value );
			}
		}
	}
	return $recognized_headers;
}

function pma_send_test() {
	if ( ! isset( $_POST['email'] ) ) {
		return __( 'No email address provided.', 'postmarkapp-email-integrator' );
	}

	$email_address = sanitize_email( wp_unslash( $_POST['email'] ) );

	if ( ! is_email( $email_address ) ) {
		return __( 'Invalid email address.', 'postmarkapp-email-integrator' );
	}

	// Define Headers
	$postmark_headers = array(
		'Accept'                  => 'application/json',
		'Content-Type'            => 'application/json',
		'X-Postmark-Server-Token' => get_option( 'postmarkapp_api_key' ),
	);

	$message      = 'This is a test email sent via Postmark from ' . get_bloginfo( 'name' ) . '.';
	$html_message = 'This is a test email sent via <strong>Postmark</strong> from ' . esc_html( get_bloginfo( 'name' ) ) . '.';

	$email             = array();
	$email['To']       = $email_address;
	$email['From']     = get_option( 'postmarkapp_sender_address' );
	$email['Subject']  = get_bloginfo( 'name' ) . ' Postmark Test';
	$email['TextBody'] = $message;

	if ( get_option( 'postmarkapp_force_html' ) == 1 ) {
		$email['HtmlBody'] = $html_message;
	}

	if ( get_option( 'postmarkapp_trackopens' ) == 1 ) {
		$email['TrackOpens'] = 'true';
	}

	$response = pma_send_mail( $postmark_headers, $email );

	if ( is_wp_error( $response ) ) {
		/* translators: %s: Error message from Postmark API */
		return sprintf( __( 'Test Failed with Error "%s"', 'postmarkapp-email-integrator' ), $response->get_error_message() );
	}

	return __( 'Test Sent', 'postmarkapp-email-integrator' );
}

function pma_send_mail( $headers, $email ) {
	$args = array(
		'headers' => $headers,
		'body'    => wp_json_encode( $email ),
	);
	do_action( 'before_wp_mail' );

	$response = wp_remote_post( POSTMARKAPP_ENDPOINT, apply_filters( 'pma_mail_args', $args ) );

	do_action( 'after_wp_mail' );

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'CONNECTION_TIMEOUT', __( 'Connection Timeout', 'postmarkapp-email-integrator' ) );
	} elseif ( isset( $response['response']['code'] ) ) {
		if ( 200 === $response['response']['code'] ) {
			return true;
		} elseif ( isset( $response['body'] ) ) {
				$error = json_decode( $response['body'], true );
			if ( isset( $error['ErrorCode'] ) ) {
				$error_code = $error['ErrorCode'];
			} else {
				$error_code = '000';
			}
			if ( isset( $error['Message'] ) ) {
				$error_message = $error['Message'];
			} else {
				$error_message = __( 'Unknown Error', 'postmarkapp-email-integrator' );
			}
				return new WP_Error( $error_code, $error_message );
		}
	}
	return new WP_Error( 'NO_RESPONSE', __( 'No Response from the PostMark Server', 'postmarkapp-email-integrator' ) );
}

/**
 * Changes the default http request timeout of WordPress from 5 seconds to 60
 * seconds so that the request to the Postmark API can be successfully executed.
 *
 * @param int $current_timeout Current timeout value.
 * @return int Modified timeout value.
 */
function pma_filter_http_request_timeout( $current_timeout ) {
	if ( intval( $current_timeout ) < 60 ) {
		return 60;
	}
	return intval( $current_timeout );
}

/**
 * Adds timeout filter so that mail function can get enough time to contact the
 * Postmark API servers.
 */
function pma_add_timeout_filter() {
	add_filter( 'http_request_timeout', 'pma_filter_http_request_timeout' );
}

add_action( 'before_wp_mail', 'pma_add_timeout_filter' );

/**
 * Removes the timeout filter after the mail function has been successfully
 * executed.
 */
function pma_remove_timeout_filter() {
	remove_filter( 'http_request_timeout', 'pma_filter_http_request_timeout' );
}

add_action( 'after_wp_mail', 'pma_remove_timeout_filter' );

/**
 * Imports the settings of the Postmark Approved WordPress plugin via AJAX.
 */
function pma_admin_import_settings() {
	check_ajax_referer( 'pma_import_settings', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'postmarkapp-email-integrator' ) );
	}

	pma_import_settings();
	echo esc_html__( 'Settings Imported', 'postmarkapp-email-integrator' );
	wp_die();
}

add_action( 'wp_ajax_pma_import_settings', 'pma_admin_import_settings' );