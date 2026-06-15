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
    wp_enqueue_style('gki-home', get_stylesheet_directory_uri() . '/assets/gki/gki-home.css', ['wp-theme-child-style'], '55.0.0');
    wp_enqueue_script('gki-home', get_stylesheet_directory_uri() . '/assets/gki/gki-home.js', [], '55.0.0', true);
}
add_action('wp_enqueue_scripts', 'gki_child_enqueue_home_assets', 60);
function gki_child_enqueue_editor_assets() {
    wp_enqueue_style('gki-home-editor', get_stylesheet_directory_uri() . '/assets/gki/gki-home.css', [], '55.0.0');
    wp_enqueue_style('gki-home-editor-fixes', get_stylesheet_directory_uri() . '/assets/gki/gki-editor.css', ['gki-home-editor'], '55.0.0');
}
add_action('enqueue_block_editor_assets', 'gki_child_enqueue_editor_assets', 60);



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
    $img = get_stylesheet_directory_uri() . '/assets/gki/about-kitchen-v34.avif';
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
            <label>Email<input type="email" name="email" placeholder="Email" required></label>
        </div>
        <div class="gki-form-row">
            <label>Phone<input type="text" name="phone" placeholder="Phone"></label>
            <label>Subject<input type="text" name="subject" placeholder="Subject"></label>
        </div>
        <label class="gki-field-full">Message<textarea name="message" placeholder="Message" rows="6" required></textarea></label>
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
    wp_enqueue_style('gki-home-v32', get_stylesheet_directory_uri() . '/assets/gki/gki-home.css', ['wp-theme-child-style'], '55.0.0');
    wp_dequeue_script('gki-home');
    wp_enqueue_script('gki-home-v32', get_stylesheet_directory_uri() . '/assets/gki/gki-home.js', [], '55.0.0', true);
}
add_action('wp_enqueue_scripts', 'gki_child_v32_assets', 100);

function gki_child_v32_refresh_home() {
    if (!current_user_can('manage_options')) { return; }
    if (get_option('gki_home_v43_final_refreshed')) { return; }
    if (function_exists('gki_child_create_home_page')) {
        delete_option('gki_home_page_installed_v38_visual_fix'); delete_option('gki_home_page_installed_v36_stable_preview_match'); delete_option('gki_home_page_installed_v40_final_all_fixes');
        gki_child_create_home_page();
        update_option('gki_home_v43_final_refreshed', time(), false);
    }
}
add_action('admin_init','gki_child_v32_refresh_home',5);
add_action('after_switch_theme','gki_child_v32_refresh_home',5);

/* GKI v43: use WP BBuilder hCaptcha settings when keys exist; no hardcoded site/secret keys. */
function gki_child_enable_wpbb_hcaptcha_if_configured() {
    $opts = get_option('wpbb_settings');
    if (!is_array($opts)) { return; }
    $site = trim((string)($opts['hcaptcha_site_key'] ?? ''));
    $secret = trim((string)($opts['hcaptcha_secret_key'] ?? ''));
    if ($site !== '' && $secret !== '' && empty($opts['hcaptcha_enabled'])) {
        $opts['hcaptcha_enabled'] = 1;
        update_option('wpbb_settings', $opts, false);
    }
}
add_action('admin_init', 'gki_child_enable_wpbb_hcaptcha_if_configured', 1);
add_action('after_switch_theme', 'gki_child_enable_wpbb_hcaptcha_if_configured', 1);

function gki_child_v43_refresh_home() {
    if (!current_user_can('manage_options')) { return; }
    if (get_option('gki_home_v43_final_refreshed_done')) { return; }
    delete_option('gki_home_page_installed_v39_final_all_fixes');
    delete_option('gki_home_page_installed_v40_final_all_fixes');
    delete_option('gki_home_page_installed_v43_final_all_fixes');
    delete_option('gki_home_v40_final_refreshed');
    if (function_exists('gki_child_create_home_page')) {
        gki_child_create_home_page();
        update_option('gki_home_v43_final_refreshed_done', time(), false);
    }
}
add_action('admin_init','gki_child_v43_refresh_home',1);
add_action('after_switch_theme','gki_child_v43_refresh_home',1);


/* GKI v44: force refresh generated Home page and keep WP BBuilder hCaptcha enabled when admin keys are present. */
function gki_child_v44_refresh_home() {
    if (!current_user_can('manage_options')) { return; }
    if (get_option('gki_home_v44_final_refreshed_done')) { return; }
    delete_option('gki_home_page_installed_v43_final_all_fixes');
    delete_option('gki_home_page_installed_v44_final_all_fixes');
    delete_option('gki_home_v43_final_refreshed_done');
    if (function_exists('gki_child_enable_wpbb_hcaptcha_if_configured')) { gki_child_enable_wpbb_hcaptcha_if_configured(); }
    if (function_exists('gki_child_create_home_page')) {
        gki_child_create_home_page();
        update_option('gki_home_v44_final_refreshed_done', time(), false);
    }
}
add_action('admin_init','gki_child_v44_refresh_home',0);
add_action('after_switch_theme','gki_child_v44_refresh_home',0);

function gki_child_v44_wpbb_settings_force_hcaptcha($value) {
    if (!is_array($value)) { return $value; }
    $site = trim((string)($value['hcaptcha_site_key'] ?? ''));
    $secret = trim((string)($value['hcaptcha_secret_key'] ?? ''));
    if ($site !== '' && $secret !== '') { $value['hcaptcha_enabled'] = 1; }
    return $value;
}
add_filter('option_wpbb_settings','gki_child_v44_wpbb_settings_force_hcaptcha', 5);


/* GKI v45: final refresh + BBuilder hCaptcha injection from admin settings only. */
function gki_child_v45_wpbb_setting($key, $default = '') {
    if (function_exists('wpbb_get_option')) { return wpbb_get_option($key, $default); }
    $settings = get_option('wpbb_settings', array());
    if (!is_array($settings)) { $settings = array(); }
    return array_key_exists($key, $settings) ? $settings[$key] : $default;
}
function gki_child_v45_hcaptcha_site_key() {
    return trim((string) gki_child_v45_wpbb_setting('hcaptcha_site_key', ''));
}
function gki_child_v45_hcaptcha_enabled() {
    return gki_child_v45_hcaptcha_site_key() !== '';
}
function gki_child_v45_hcaptcha_markup() {
    if (!gki_child_v45_hcaptcha_enabled()) { return ''; }
    $site_key = gki_child_v45_hcaptcha_site_key();
    return '<div class="gki-hcaptcha-field wpbb-field wpbb-field--captcha col-12"><div class="h-captcha" data-sitekey="' . esc_attr($site_key) . '"></div><input type="hidden" name="wpbb_captcha_enabled" value="1"><input type="hidden" name="wpbb_captcha_provider" value="hcaptcha"></div>';
}
function gki_child_v45_inject_hcaptcha_into_wpbb_form($html, $block = null) {
    if (!is_string($html) || $html === '' || !gki_child_v45_hcaptcha_enabled()) { return $html; }
    if (stripos($html, 'wpbb-dynamic-form') === false && stripos($html, 'gki-bbuilder-form') === false) { return $html; }
    if (stripos($html, 'h-captcha') !== false) {
        if (stripos($html, 'name="wpbb_captcha_provider"') === false) {
            $html = str_ireplace('</form>', '<input type="hidden" name="wpbb_captcha_enabled" value="1"><input type="hidden" name="wpbb_captcha_provider" value="hcaptcha"></form>', $html);
        }
        return $html;
    }
    $markup = gki_child_v45_hcaptcha_markup();
    foreach (array('<div class="wpbb-form-actions', '<button type="submit"', '</form>') as $target) {
        $pos = stripos($html, $target);
        if ($pos !== false) { return substr($html, 0, $pos) . $markup . substr($html, $pos); }
    }
    return $html;
}
function gki_child_v45_render_block_hcaptcha($block_content, $block) {
    if (is_array($block) && isset($block['blockName']) && $block['blockName'] === 'wpbb/dynamic-form') {
        return gki_child_v45_inject_hcaptcha_into_wpbb_form($block_content, $block);
    }
    return $block_content;
}
add_filter('render_block', 'gki_child_v45_render_block_hcaptcha', 20, 2);
function gki_child_v45_enqueue_hcaptcha_api() {
    if (gki_child_v45_hcaptcha_enabled() && !is_admin()) {
        wp_enqueue_script('gki-hcaptcha-api', 'https://js.hcaptcha.com/1/api.js?render=explicit', array(), null, true);
    }
}
add_action('wp_enqueue_scripts', 'gki_child_v45_enqueue_hcaptcha_api', 20);
function gki_child_v45_force_refresh_home() {
    if (!current_user_can('manage_options')) { return; }
    if (get_option('gki_home_v45_final_refreshed_done')) { return; }
    delete_option('gki_home_page_installed_v44_final_all_fixes');
    delete_option('gki_home_page_installed_v45_final_all_fixes');
    delete_option('gki_home_v44_final_refreshed_done');
    if (function_exists('gki_child_enable_wpbb_hcaptcha_if_configured')) { gki_child_enable_wpbb_hcaptcha_if_configured(); }
    if (function_exists('gki_child_create_home_page')) {
        gki_child_create_home_page();
        update_option('gki_home_v45_final_refreshed_done', time(), false);
    }
}
add_action('admin_init','gki_child_v45_force_refresh_home',0);
add_action('after_switch_theme','gki_child_v45_force_refresh_home',0);


/* GKI v47: force refresh generated Home page for latest hero/icon/project/form/map updates. */
function gki_child_v47_force_refresh_home() {
    if (!current_user_can('manage_options')) { return; }
    if (get_option('gki_home_v47_final_refreshed_done')) { return; }
    delete_option('gki_home_page_installed_v46_user_final');
    delete_option('gki_home_page_installed_v47_final_user_updates');
    delete_option('gki_home_v45_final_refreshed_done');
    if (function_exists('gki_child_enable_wpbb_hcaptcha_if_configured')) { gki_child_enable_wpbb_hcaptcha_if_configured(); }
    if (function_exists('gki_child_create_home_page')) {
        gki_child_create_home_page();
        update_option('gki_home_v47_final_refreshed_done', time(), false);
    }
}
add_action('admin_init', 'gki_child_v47_force_refresh_home', 0);
add_action('after_switch_theme', 'gki_child_v47_force_refresh_home', 0);


/* GKI v48: force refresh generated Home page for logo/footer/card color fixes. */
function gki_child_v48_force_refresh_home() {
    if (!current_user_can('manage_options')) { return; }
    if (get_option('gki_home_v48_final_refreshed_done')) { return; }
    delete_option('gki_home_page_installed_v47_final_user_updates');
    delete_option('gki_home_page_installed_v48_footer_logo_cards');
    delete_option('gki_home_v47_final_refreshed_done');
    if (function_exists('gki_child_enable_wpbb_hcaptcha_if_configured')) { gki_child_enable_wpbb_hcaptcha_if_configured(); }
    if (function_exists('gki_child_create_home_page')) {
        gki_child_create_home_page();
        update_option('gki_home_v48_final_refreshed_done', time(), false);
    }
}
add_action('admin_init', 'gki_child_v48_force_refresh_home', 0);
add_action('after_switch_theme', 'gki_child_v48_force_refresh_home', 0);


/* GKI v49: exact services + WP BBuilder hCaptcha compatibility. No keys are hardcoded. */
function gki_child_v49_hcaptcha_site_key() {
    $settings = get_option('wpbb_settings', array());
    if (!is_array($settings)) { $settings = array(); }
    if (function_exists('wpbb_get_option')) {
        $key = trim((string) wpbb_get_option('hcaptcha_site_key', ''));
    } else {
        $key = trim((string) ($settings['hcaptcha_site_key'] ?? ''));
    }
    return $key;
}
function gki_child_v49_hcaptcha_markup() {
    $site_key = gki_child_v49_hcaptcha_site_key();
    if ($site_key === '') { return ''; }
    return '<div class="gki-hcaptcha-field wpbb-field wpbb-field--captcha col-12"><div class="h-captcha" data-sitekey="' . esc_attr($site_key) . '"></div><input type="hidden" name="wpbb_captcha_enabled" value="1"><input type="hidden" name="wpbb_captcha_provider" value="hcaptcha"></div>';
}
function gki_child_v49_inject_hcaptcha($html, $block = null) {
    if (!is_string($html) || $html === '' || gki_child_v49_hcaptcha_site_key() === '') { return $html; }
    if (stripos($html, 'wpbb-dynamic-form') === false && stripos($html, 'gki-bbuilder-form') === false) { return $html; }
    if (stripos($html, 'h-captcha') !== false) {
        if (stripos($html, 'name="wpbb_captcha_provider"') === false) {
            $html = str_ireplace('</form>', '<input type="hidden" name="wpbb_captcha_enabled" value="1"><input type="hidden" name="wpbb_captcha_provider" value="hcaptcha"></form>', $html);
        }
        return $html;
    }
    $markup = gki_child_v49_hcaptcha_markup();
    foreach (array('<div class="wpbb-form-actions', '<button type="submit"', '</form>') as $target) {
        $pos = stripos($html, $target);
        if ($pos !== false) { return substr($html, 0, $pos) . $markup . substr($html, $pos); }
    }
    return $html;
}
function gki_child_v49_render_block_hcaptcha($block_content, $block) {
    if (is_array($block) && isset($block['blockName']) && $block['blockName'] === 'wpbb/dynamic-form') {
        return gki_child_v49_inject_hcaptcha($block_content, $block);
    }
    return $block_content;
}
add_filter('render_block', 'gki_child_v49_render_block_hcaptcha', 30, 2);
function gki_child_v49_hcaptcha_script() {
    if (gki_child_v49_hcaptcha_site_key() !== '' && !is_admin()) {
        wp_enqueue_script('gki-hcaptcha-api-v49', 'https://js.hcaptcha.com/1/api.js', array(), null, true);
    }
}
add_action('wp_enqueue_scripts', 'gki_child_v49_hcaptcha_script', 25);
function gki_child_v49_force_refresh_home() {
    if (!current_user_can('manage_options')) { return; }
    if (get_option('gki_home_v49_final_refreshed_done')) { return; }
    delete_option('gki_home_page_installed_v48_footer_logo_cards');
    delete_option('gki_home_page_installed_v49_services_hcaptcha_year');
    delete_option('gki_home_v48_final_refreshed_done');
    if (function_exists('gki_child_enable_wpbb_hcaptcha_if_configured')) { gki_child_enable_wpbb_hcaptcha_if_configured(); }
    if (function_exists('gki_child_create_home_page')) {
        gki_child_create_home_page();
        update_option('gki_home_v49_final_refreshed_done', time(), false);
    }
}
add_action('admin_init', 'gki_child_v49_force_refresh_home', 0);
add_action('after_switch_theme', 'gki_child_v49_force_refresh_home', 0);


/* GKI v50: remove legacy auto-refreshers so the Home page remains editable after this update. */
function gki_child_v50_remove_legacy_hooks() {
    remove_action('admin_init', 'gki_child_v32_refresh_home', 5);
    remove_action('admin_init', 'gki_child_v43_refresh_home', 1);
    remove_action('admin_init', 'gki_child_v44_refresh_home', 0);
    remove_action('admin_init', 'gki_child_v45_force_refresh_home', 0);
    remove_action('admin_init', 'gki_child_v47_force_refresh_home', 0);
    remove_action('admin_init', 'gki_child_v48_force_refresh_home', 0);
    remove_action('admin_init', 'gki_child_v49_force_refresh_home', 0);
    remove_action('admin_init', 'gki_child_create_home_page');
    remove_filter('render_block', 'gki_child_v45_render_block_hcaptcha', 20);
    remove_filter('render_block', 'gki_child_v49_render_block_hcaptcha', 30);
    remove_action('wp_enqueue_scripts', 'gki_child_v45_enqueue_hcaptcha_api', 20);
    remove_action('wp_enqueue_scripts', 'gki_child_v49_hcaptcha_script', 25);
}
add_action('init', 'gki_child_v50_remove_legacy_hooks', 0);

function gki_child_v50_wpbb_hcaptcha_language($requested = '') {
    $lang = sanitize_key($requested);
    if ($lang === '' || $lang === 'auto') {
        $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
        $lang = strpos((string) $locale, 'lv') === 0 ? 'lv' : 'en';
    }
    return in_array($lang, array('lv', 'en'), true) ? $lang : 'en';
}

function gki_child_v50_wpbb_setting($key, $default = '') {
    if (function_exists('wpbb_get_option')) { return wpbb_get_option($key, $default); }
    $settings = get_option('wpbb_settings', array());
    if (!is_array($settings)) { $settings = array(); }
    return array_key_exists($key, $settings) ? $settings[$key] : $default;
}

function gki_child_v50_wpbb_captcha_config($requested_provider = '') {
    $config = array('provider' => '', 'site_key' => '', 'language' => gki_child_v50_wpbb_hcaptcha_language());
    $h_enabled = (bool) gki_child_v50_wpbb_setting('hcaptcha_enabled', 0);
    $h_site = $h_enabled ? trim((string) gki_child_v50_wpbb_setting('hcaptcha_site_key', '')) : '';
    $h_secret = $h_enabled ? trim((string) gki_child_v50_wpbb_setting('hcaptcha_secret_key', '')) : '';
    if ($h_site !== '') {
        $config['provider'] = 'hcaptcha';
        $config['site_key'] = $h_site;
        $config['secret_available'] = $h_secret !== '';
        return $config;
    }
    return $config;
}

function gki_child_v50_enqueue_wpbb_form_assets($captcha_config = array()) {
    if (wp_script_is('wpbb-form-view', 'registered')) { wp_enqueue_script('wpbb-form-view'); }
    $bridge_path = get_stylesheet_directory() . '/assets/gki/wpbb-form-bridge.js';
    if (file_exists($bridge_path)) {
        wp_enqueue_script('gki-wpbb-form-bridge', get_stylesheet_directory_uri() . '/assets/gki/wpbb-form-bridge.js', array(), filemtime($bridge_path), true);
        wp_localize_script('gki-wpbb-form-bridge', 'gkiWpbbFormBridge', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpbb_form_nonce'),
            'error' => function_exists('wpbb_get_option') ? wpbb_get_option('default_error_message', 'Something went wrong. Please try again.') : 'Something went wrong. Please try again.',
            'validationText' => function_exists('wpbb_get_option') ? wpbb_get_option('default_validation_text', 'Please fill in all required fields.') : 'Please fill in all required fields.',
        ));
    }
    if (!empty($captcha_config['provider']) && $captcha_config['provider'] === 'hcaptcha' && !empty($captcha_config['site_key'])) {
        $lang = !empty($captcha_config['language']) ? $captcha_config['language'] : gki_child_v50_wpbb_hcaptcha_language();
        wp_enqueue_script('hcaptcha-api', 'https://js.hcaptcha.com/1/api.js?render=explicit&hl=' . rawurlencode($lang), array(), null, true);
    }
}

function gki_child_v50_hcaptcha_script_language($src, $handle) {
    if (!in_array($handle, array('hcaptcha-api', 'hcaptcha'), true) || strpos((string) $src, 'hcaptcha.com/1/api.js') === false) { return $src; }
    if (strpos((string) $src, 'hl=') !== false) { return $src; }
    return add_query_arg('hl', gki_child_v50_wpbb_hcaptcha_language(), $src);
}
add_filter('script_loader_src', 'gki_child_v50_hcaptcha_script_language', 20, 2);

function gki_child_v50_wpbb_dynamic_form_hcaptcha($block_content, $block) {
    if (empty($block['blockName']) || $block['blockName'] !== 'wpbb/dynamic-form') { return $block_content; }
    $captcha = gki_child_v50_wpbb_captcha_config('hcaptcha');
    if (empty($captcha['provider']) || $captcha['provider'] !== 'hcaptcha' || empty($captcha['site_key'])) { return $block_content; }
    gki_child_v50_enqueue_wpbb_form_assets($captcha);
    $lang = !empty($captcha['language']) ? $captcha['language'] : gki_child_v50_wpbb_hcaptcha_language();
    if (strpos($block_content, 'h-captcha') !== false) {
        if (strpos($block_content, 'data-hl=') === false) {
            $block_content = preg_replace('/(<div\s+[^>]*class=["\'][^"\']*h-captcha[^"\']*["\'][^>]*)(>)/i', '$1 data-hl="' . esc_attr($lang) . '"$2', $block_content);
        }
        return $block_content;
    }
    $captcha_html = '<div class="col-12 gki-hcaptcha-col"><div class="wpbb-field wpbb-field--captcha gki-hcaptcha-wrap"><div class="h-captcha gki-hcaptcha" data-sitekey="' . esc_attr($captcha['site_key']) . '" data-hl="' . esc_attr($lang) . '"></div><input type="hidden" name="wpbb_captcha_enabled" value="1"><input type="hidden" name="wpbb_captcha_provider" value="hcaptcha"></div></div>';
    if (strpos($block_content, 'wpbb-form-message') !== false) {
        return preg_replace('/(<div[^>]*class=["\'][^"\']*wpbb-form-message[^"\']*["\'][^>]*>)/i', $captcha_html . '$1', $block_content, 1);
    }
    if (strpos($block_content, 'wpbb-form-actions') !== false) {
        return preg_replace('/(<div[^>]*class=["\'][^"\']*wpbb-form-actions[^"\']*["\'][^>]*>)/i', $captcha_html . '$1', $block_content, 1);
    }
    return str_replace('</form>', $captcha_html . '</form>', $block_content);
}
add_filter('render_block', 'gki_child_v50_wpbb_dynamic_form_hcaptcha', 40, 2);

/* Run the v50 home refresh once, then leave the page editable in Pages > Home. */
function gki_child_v50_force_refresh_home() {
    if (!current_user_can('manage_options')) { return; }
    if (get_option('gki_home_v50_final_refreshed_done')) { return; }
    delete_option('gki_home_page_installed_v49_services_hcaptcha_year');
    delete_option('gki_home_page_installed_v50_editor_hcaptcha_icons');
    if (function_exists('gki_child_enable_wpbb_hcaptcha_if_configured')) { gki_child_enable_wpbb_hcaptcha_if_configured(); }
    if (function_exists('gki_child_create_home_page')) {
        gki_child_create_home_page();
        update_option('gki_home_v50_final_refreshed_done', time(), false);
    }
}
add_action('admin_init', 'gki_child_v50_force_refresh_home', 0);
add_action('after_switch_theme', 'gki_child_v50_force_refresh_home', 0);


/* GKI v52: restore v50-approved frontend proportions while keeping editor-safe blocks and WP BBuilder hCaptcha. */
function gki_child_v52_remove_legacy_refresh_hooks() {
    remove_action('admin_init', 'gki_child_v32_refresh_home', 5);
    remove_action('admin_init', 'gki_child_v43_refresh_home', 1);
    remove_action('admin_init', 'gki_child_v44_refresh_home', 0);
    remove_action('admin_init', 'gki_child_v45_force_refresh_home', 0);
    remove_action('admin_init', 'gki_child_v47_force_refresh_home', 0);
    remove_action('admin_init', 'gki_child_v48_force_refresh_home', 0);
    remove_action('admin_init', 'gki_child_v49_force_refresh_home', 0);
    remove_action('admin_init', 'gki_child_v50_force_refresh_home', 0);
}
add_action('init', 'gki_child_v52_remove_legacy_refresh_hooks', 1);

function gki_child_v52_force_refresh_home() {
    if (!current_user_can('manage_options')) { return; }
    if (get_option('gki_home_v52_restored_design_done')) { return; }
    delete_option('gki_home_page_installed_v50_editor_hcaptcha_icons');
    delete_option('gki_home_page_installed_v52_restored_design');
    delete_option('gki_home_v50_final_refreshed_done');
    if (function_exists('gki_child_enable_wpbb_hcaptcha_if_configured')) { gki_child_enable_wpbb_hcaptcha_if_configured(); }
    if (function_exists('gki_child_create_home_page')) {
        gki_child_create_home_page();
        update_option('gki_home_v52_restored_design_done', time(), false);
    }
}
add_action('admin_init', 'gki_child_v52_force_refresh_home', 0);
add_action('after_switch_theme', 'gki_child_v52_force_refresh_home', 0);


/* GKI v53: refresh Home once with editor-valid Why blocks, coverage location chips, and updated editor styling. */
function gki_child_v53_remove_legacy_refresh_hooks() {
    remove_action('admin_init', 'gki_child_v52_force_refresh_home', 0);
}
add_action('init', 'gki_child_v53_remove_legacy_refresh_hooks', 2);

function gki_child_v53_force_refresh_home() {
    if (!current_user_can('manage_options')) { return; }
    if (get_option('gki_home_v53_editor_polish_done')) { return; }
    delete_option('gki_home_page_installed_v52_restored_design');
    delete_option('gki_home_page_installed_v53_editor_polish');
    delete_option('gki_home_v52_restored_design_done');
    if (function_exists('gki_child_enable_wpbb_hcaptcha_if_configured')) { gki_child_enable_wpbb_hcaptcha_if_configured(); }
    if (function_exists('gki_child_create_home_page')) {
        gki_child_create_home_page();
        update_option('gki_home_v53_editor_polish_done', time(), false);
    }
}
add_action('admin_init', 'gki_child_v53_force_refresh_home', 0);
add_action('after_switch_theme', 'gki_child_v53_force_refresh_home', 0);
