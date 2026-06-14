<?php
if (!defined('ABSPATH')) {
    exit;
}

function wp_theme_child_enqueue_assets() {
    $theme = wp_get_theme();
    wp_enqueue_style('wp-theme-child-style', get_stylesheet_uri(), ['wp-theme-style'], $theme->get('Version'));

    $manifest = get_stylesheet_directory() . '/dist/.vite/manifest.json';
    if (file_exists($manifest)) {
        $data = json_decode((string) file_get_contents($manifest), true);
        if (is_array($data)) {
            if (!empty($data['src/scss/public.scss']['file'])) {
                wp_enqueue_style('wp-theme-child-dist', get_stylesheet_directory_uri() . '/dist/' . ltrim($data['src/scss/public.scss']['file'], '/'), ['wp-theme-child-style'], null);
            }
            if (!empty($data['src/js/main.js']['file'])) {
                wp_enqueue_script('wp-theme-child-app', get_stylesheet_directory_uri() . '/dist/' . ltrim($data['src/js/main.js']['file'], '/'), ['jquery'], null, true);
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'wp_theme_child_enqueue_assets', 30);

function wp_theme_child_acf_value($key, $default = '') {
    if (!function_exists('get_field')) {
        return $default;
    }
    $value = get_field($key, 'option');
    return ($value !== null && $value !== false && $value !== '') ? $value : $default;
}

function wp_theme_child_dynamic_css() {
    $brand = wp_theme_child_acf_value('brand_color', '#1d4ed8');
    $accent = wp_theme_child_acf_value('accent_color', '#0f172a');
    $surface = wp_theme_child_acf_value('surface_color', '#f8fafc');
    $content_width = wp_theme_child_acf_value('content_width', '840px');
    $wide_width = wp_theme_child_acf_value('wide_width', '1280px');
    $button_radius = wp_theme_child_acf_value('button_radius', '999px');
    $css = ':root{' .
        '--wp-theme-primary:' . sanitize_hex_color($brand ?: '#1d4ed8') . ';' .
        '--wp-theme-text:' . sanitize_hex_color($accent ?: '#0f172a') . ';' .
        '--wp-theme-surface:' . sanitize_hex_color($surface ?: '#f8fafc') . ';' .
        '--wp-theme-content-width:' . preg_replace('/[^0-9a-zA-Z.%\-]/', '', (string) $content_width) . ';' .
        '--wp-theme-wide-width:' . preg_replace('/[^0-9a-zA-Z.%\-]/', '', (string) $wide_width) . ';' .
        '--wp-theme-radius:' . preg_replace('/[^0-9a-zA-Z.%\-]/', '', (string) $button_radius) . ';' .
    '}';
    wp_add_inline_style('wp-theme-child-style', $css);
}
add_action('wp_enqueue_scripts', 'wp_theme_child_dynamic_css', 40);

function wp_theme_child_maybe_load_aos() {
    if (!wp_theme_child_acf_value('enable_animations', false) || !wp_theme_child_acf_value('enable_aos_cdn', false)) {
        return;
    }
    wp_enqueue_style('aos', 'https://unpkg.com/aos@2.3.4/dist/aos.css', [], '2.3.4');
    wp_enqueue_script('aos', 'https://unpkg.com/aos@2.3.4/dist/aos.js', [], '2.3.4', true);
    wp_add_inline_script('aos', 'document.addEventListener("DOMContentLoaded",function(){if(window.AOS){AOS.init({once:true,duration:600});}});');
}
add_action('wp_enqueue_scripts', 'wp_theme_child_maybe_load_aos', 50);

function wp_theme_child_seo_meta() {
    if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION')) {
        return;
    }
    if (is_singular()) {
        $description = wp_theme_child_acf_value('default_meta_description', '');
        if (!$description) {
            $description = wp_trim_words(wp_strip_all_tags(get_post_field('post_content', get_the_ID())), 28, '…');
        }
        if ($description) {
            echo '<meta name="description" content="' . esc_attr($description) . '">';
        }
        $og = wp_theme_child_acf_value('default_og_image', '');
        if (is_array($og)) {
            $og = $og['url'] ?? '';
        }
        if ($og) {
            echo '<meta property="og:image" content="' . esc_url($og) . '">';
        }
    }
}
add_action('wp_head', 'wp_theme_child_seo_meta', 5);


function gki_child_enqueue_home_assets() {
    wp_enqueue_style('gki-home', get_stylesheet_directory_uri() . '/assets/gki/gki-home.css', ['wp-theme-child-style'], '36.0.0');
    wp_enqueue_script('gki-home', get_stylesheet_directory_uri() . '/assets/gki/gki-home.js', ['hcaptcha-api'], '36.0.0', true);
}
add_action('wp_enqueue_scripts', 'gki_child_enqueue_home_assets', 60);
function gki_child_enqueue_editor_assets() {
    wp_enqueue_style('gki-home-editor', get_stylesheet_directory_uri() . '/assets/gki/gki-home.css', [], '36.0.0');
}
add_action('enqueue_block_editor_assets', 'gki_child_enqueue_editor_assets', 60);


function gki_child_hcaptcha_site_key() {
    return defined('GKI_HCAPTCHA_SITE_KEY') && GKI_HCAPTCHA_SITE_KEY ? GKI_HCAPTCHA_SITE_KEY : '3ab4ede6-536c-4d0c-b832-775c4e9cb94c';
}
function gki_child_hcaptcha_secret_key() {
    return defined('GKI_HCAPTCHA_SECRET_KEY') && GKI_HCAPTCHA_SECRET_KEY ? GKI_HCAPTCHA_SECRET_KEY : 'ES_3d69dad959cd41929744904cdc8c9954';
}
function gki_child_enqueue_hcaptcha() {
    wp_enqueue_script('hcaptcha-api', 'https://js.hcaptcha.com/1/api.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'gki_child_enqueue_hcaptcha', 70);

function gki_child_ensure_wpbb_hcaptcha_settings() {
    $settings = get_option('wpbb_settings', []);
    if (!is_array($settings)) { $settings = []; }
    $changed = false;
    $desired = [
        'hcaptcha_enabled' => 1,
        'hcaptcha_site_key' => gki_child_hcaptcha_site_key(),
        'hcaptcha_secret_key' => gki_child_hcaptcha_secret_key(),
        'default_recipient_email' => 'guntis@gkiengineering.co.uk',
        'default_success_message' => 'Thank you. Your message has been sent.',
        'default_error_message' => 'Message could not be sent. Please try again or email guntis@gkiengineering.co.uk directly.',
        'default_validation_text' => 'Please fill in all required fields correctly.',
        'form_honeypot_enabled' => 1,
        'form_min_submit_time' => '2',
        'button_class' => 'btn btn-primary gki-btn gki-btn-primary',
        'form_class' => 'gki-bbuilder-form'
    ];
    foreach ($desired as $key => $value) {
        if (!isset($settings[$key]) || $settings[$key] === '' || $key === 'hcaptcha_enabled' || $key === 'default_recipient_email' || $key === 'form_honeypot_enabled') {
            if (($settings[$key] ?? null) !== $value) {
                $settings[$key] = $value;
                $changed = true;
            }
        }
    }
    if ($changed) { update_option('wpbb_settings', $settings, false); }
}
add_action('after_switch_theme', 'gki_child_ensure_wpbb_hcaptcha_settings', 5);
add_action('admin_init', 'gki_child_ensure_wpbb_hcaptcha_settings', 5);

function gki_child_mail_from($email) {
    return is_email('noreply@gkiengineering.co.uk') ? 'noreply@gkiengineering.co.uk' : $email;
}
function gki_child_mail_from_name($name) { return 'GKI Engineering Ltd'; }
add_filter('wp_mail_from', 'gki_child_mail_from');
add_filter('wp_mail_from_name', 'gki_child_mail_from_name');


function gki_child_seo_social_meta(){
    if (!is_front_page() && !is_home()) { return; }
    $title = 'GKI Engineering Ltd | Commercial Kitchen Maintenance, Welding & Fabrication';
    $desc = 'GKI Engineering Ltd provides commercial kitchen maintenance, facility support, stainless steel welding and bespoke fabrication across the Midlands and London.';
    $url = home_url('/');
    $img = get_stylesheet_directory_uri() . '/assets/gki/hero-preview-approved-v36.jpg';
    echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($img) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url($img) . '">' . "\n";
    echo '<script type="application/ld+json">' . wp_json_encode([
        '@context'=>'https://schema.org','@type'=>'LocalBusiness','name'=>'GKI Engineering Ltd','url'=>$url,'image'=>$img,
        'telephone'=>'+44 (0) 7760660983',
        'address'=>['@type'=>'PostalAddress','streetAddress'=>'116 Yardley Road','addressLocality'=>'Acocks Green','addressRegion'=>'Birmingham','postalCode'=>'B27 6LG','addressCountry'=>'GB'],
        'areaServed'=>['Midlands','London'],
        'sameAs'=>['https://find-and-update.company-information.service.gov.uk/company/14112841']
    ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
add_action('wp_head','gki_child_seo_social_meta',6);

require_once get_stylesheet_directory() . '/inc/gki-home-installer.php';

function gki_child_contact_form_handler() {
    $redirect = wp_get_referer() ? wp_get_referer() : home_url('/');
    if (!isset($_POST['gki_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gki_nonce'])), 'gki_contact_form')) {
        wp_safe_redirect(add_query_arg('gki_status', 'security', $redirect . '#contact'));
        exit;
    }
    if (!empty($_POST['website'])) {
        wp_safe_redirect(add_query_arg('gki_status', 'sent', $redirect . '#contact'));
        exit;
    }
    $hcaptcha_response = isset($_POST['h-captcha-response']) ? sanitize_text_field(wp_unslash($_POST['h-captcha-response'])) : '';
    if (!$hcaptcha_response) {
        wp_safe_redirect(add_query_arg('gki_status', 'captcha', $redirect . '#contact'));
        exit;
    }
    $verify = wp_remote_post('https://api.hcaptcha.com/siteverify', [
        'timeout' => 10,
        'body' => [
            'secret' => gki_child_hcaptcha_secret_key(),
            'response' => $hcaptcha_response,
            'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
            'sitekey' => gki_child_hcaptcha_site_key(),
        ],
    ]);
    $captcha_ok = false;
    if (!is_wp_error($verify)) {
        $data = json_decode((string) wp_remote_retrieve_body($verify), true);
        $captcha_ok = !empty($data['success']);
    }
    if (!$captcha_ok) {
        wp_safe_redirect(add_query_arg('gki_status', 'captcha', $redirect . '#contact'));
        exit;
    }
    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $subject = isset($_POST['subject']) ? sanitize_text_field(wp_unslash($_POST['subject'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    if (!$name || !$email || !$message || !is_email($email)) {
        wp_safe_redirect(add_query_arg('gki_status', 'missing', $redirect . '#contact'));
        exit;
    }
    $body = "New website enquiry from GKI Engineering website\n\n";
    $body .= "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\nSubject: {$subject}\n\nMessage:\n{$message}\n";
    $headers = ['Reply-To: ' . $name . ' <' . $email . '>'];
    $sent = wp_mail('guntis@gkiengineering.co.uk', $subject ? 'GKI website enquiry: ' . $subject : 'New GKI Engineering website enquiry', $body, $headers);
    wp_safe_redirect(add_query_arg('gki_status', $sent ? 'sent' : 'mailfail', $redirect . '#contact'));
    exit;
}
add_action('admin_post_gki_contact_form', 'gki_child_contact_form_handler');
add_action('admin_post_nopriv_gki_contact_form', 'gki_child_contact_form_handler');

function gki_child_contact_form_shortcode() {
    $status = isset($_GET['gki_status']) ? sanitize_key(wp_unslash($_GET['gki_status'])) : '';
    $messages = [
        'sent' => 'Thank you. Your message has been sent.',
        'captcha' => 'Please complete the hCaptcha protection and try again.',
        'missing' => 'Please complete the required fields and try again.',
        'mailfail' => 'Message could not be sent from the server. Please email guntis@gkiengineering.co.uk directly.',
        'security' => 'Security check failed. Please refresh the page and try again.'
    ];
    ob_start();
    if ($status && isset($messages[$status])) {
        echo '<div class="gki-form-status gki-status-' . esc_attr($status) . '">' . esc_html($messages[$status]) . '</div>';
    }
    ?>
    <form class="gki-contact-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="gki_contact_form">
        <input type="hidden" name="gki_nonce" value="<?php echo esc_attr(wp_create_nonce('gki_contact_form')); ?>">
        <div class="gki-form-row">
            <label>Name<input type="text" name="name" placeholder="Name" required></label>
            <label>Phone<input type="text" name="phone" placeholder="Phone"></label>
        </div>
        <label>Email<input type="email" name="email" placeholder="Email" required></label>
        <label>Subject<input type="text" name="subject" placeholder="Subject"></label>
        <label class="gki-field-full">Message<textarea name="message" placeholder="Message" rows="6" required></textarea></label>
        <div class="gki-hcaptcha-wrap"><div class="h-captcha" data-sitekey="<?php echo esc_attr(gki_child_hcaptcha_site_key()); ?>"></div></div>
        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="gki-hp" aria-hidden="true">
        <button type="submit" class="gki-btn gki-btn-primary">Send Message</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('gki_contact_form', 'gki_child_contact_form_shortcode');




/* GKI v32 SAFE: final frontend polish without WPBB fatal filters. */
function gki_child_v32_assets() {
    wp_dequeue_style('gki-home');
    wp_enqueue_style('gki-home-v32', get_stylesheet_directory_uri() . '/assets/gki/gki-home.css', ['wp-theme-child-style'], '36.0.0');
    wp_dequeue_script('gki-home');
    wp_enqueue_script('gki-home-v32', get_stylesheet_directory_uri() . '/assets/gki/gki-home.js', [], '36.0.0', true);
}
add_action('wp_enqueue_scripts', 'gki_child_v32_assets', 100);

function gki_child_v32_refresh_home() {
    if (!current_user_can('manage_options')) { return; }
    if (get_option('gki_home_v36_safe_refreshed')) { return; }
    if (function_exists('gki_child_create_home_page')) {
        delete_option('gki_home_page_installed_v36_stable_preview_match');
        gki_child_create_home_page();
        update_option('gki_home_v36_safe_refreshed', time(), false);
    }
}
add_action('admin_init','gki_child_v32_refresh_home',5);
add_action('after_switch_theme','gki_child_v32_refresh_home',5);
