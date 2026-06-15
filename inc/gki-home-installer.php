<?php
if (!defined('ABSPATH')) { exit; }

function gki_child_asset_url($file) {
    return get_stylesheet_directory_uri() . '/assets/gki/' . ltrim($file, '/');
}

function gki_child_media_for_asset($file, $title = '') {
    $key = 'gki_media_' . sanitize_key(str_replace(['.', '/'], '_', $file));
    $existing = (int) get_option($key, 0);
    if ($existing && get_post($existing)) {
        $url = wp_get_attachment_url($existing);
        if ($url) { return ['id' => $existing, 'url' => $url]; }
    }
    $path = get_stylesheet_directory() . '/assets/gki/' . ltrim($file, '/');
    if (!file_exists($path)) {
        return ['id' => 0, 'url' => gki_child_asset_url($file)];
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $upload = wp_upload_bits(basename($file), null, file_get_contents($path));
    if (!empty($upload['error'])) {
        return ['id' => 0, 'url' => gki_child_asset_url($file)];
    }
    $type = wp_check_filetype($upload['file'], null);
    $attachment = [
        'post_mime_type' => $type['type'] ?: 'image/avif',
        'post_title' => $title ?: preg_replace('/\.[^.]+$/', '', basename($file)),
        'post_content' => '',
        'post_status' => 'inherit'
    ];
    $id = wp_insert_attachment($attachment, $upload['file']);
    if (!is_wp_error($id)) {
        if (strpos((string) $type['type'], 'svg') === false) {
            $meta = wp_generate_attachment_metadata($id, $upload['file']);
            wp_update_attachment_metadata($id, $meta);
        }
        update_option($key, (int) $id, false);
        return ['id' => (int) $id, 'url' => wp_get_attachment_url($id)];
    }
    return ['id' => 0, 'url' => gki_child_asset_url($file)];
}

function gki_child_img_block($asset, $alt, $class = '') {
    $id = (int) ($asset['id'] ?? 0);
    $url = esc_url($asset['url'] ?? '');
    $alt = esc_attr($alt);
    $figClass = $class ? ' ' . esc_attr($class) : '';
    $json = ['sizeSlug' => 'full', 'linkDestination' => 'none'];
    if ($id) { $json['id'] = $id; }
    if ($class) { $json['className'] = $class; }
    $imgClass = $id ? ' class="wp-image-' . $id . '"' : '';
    return '<!-- wp:image ' . wp_json_encode($json) . ' --><figure class="wp-block-image size-full' . $figClass . '"><img src="' . $url . '" alt="' . $alt . '"' . $imgClass . '/></figure><!-- /wp:image -->';
}


function gki_child_why_icon($name) {
    $icons = array(
        'professionals' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="7" r="3.5"/><path d="M4 21c1.4-4.7 4-7 8-7s6.6 2.3 8 7"/></svg>',
        'reliable' => '<svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-5"/></svg>',
        'detail' => '<svg viewBox="0 0 24 24"><path d="M8 4h8l2 3v13H6V7z"/><path d="M9 12l2 2 4-5"/><path d="M9 18h6"/></svg>',
        'partners' => '<svg viewBox="0 0 24 24"><path d="M8 12l3 3 5-6"/><path d="M3.5 13.5l3-3a3 3 0 0 1 4.2 0l.8.8.8-.8a3 3 0 0 1 4.2 0l4 4"/></svg>',
    );
    $svg = isset($icons[$name]) ? $icons[$name] : $icons['professionals'];
    return '<span class="gki-why-icon" aria-hidden="true">' . $svg . '</span>';
}

function gki_child_build_home_content() {
    $plumbing = gki_child_media_for_asset('icon-plumbing-v52.svg', 'Plumbing icon');
    $mechanical = gki_child_media_for_asset('icon-mechanical-v52.svg', 'Mechanical icon');
    $facility = gki_child_media_for_asset('icon-facilities-v52.svg', 'Facility icon');
    $welding = gki_child_media_for_asset('icon-welding-v52.svg', 'Welding icon');
    $fabrication = gki_child_media_for_asset('icon-fabrication-v52.svg', 'Fabrication icon');
    $hero = gki_child_media_for_asset('hero-kitchen-v52.avif', 'Commercial kitchen hero image');
    if (empty($hero['url'])) { $hero = gki_child_media_for_asset('hero-kitchen-v52.avif', 'Commercial kitchen hero image'); }
    $about = gki_child_media_for_asset('about-kitchen-v52.avif', 'Commercial kitchen stainless steel maintenance');
    $restaurant = gki_child_media_for_asset('project-restaurant-v52.avif', 'Restaurant maintenance project');
    $facilityImg = gki_child_media_for_asset('project-commercial-v52.avif', 'Commercial facility project');
    $weldingImg = gki_child_media_for_asset('project-welding-v52.avif', 'Stainless steel welding project');
    ob_start(); ?>
<!-- wp:group {"className":"gki-home-page","layout":{"type":"default"}} -->
<div class="wp-block-group gki-home-page" id="gki-site">

<!-- wp:group {"tagName":"section","className":"gki-hero","anchor":"home","layout":{"type":"default"}} -->
<section class="wp-block-group gki-hero" id="home"><!-- wp:columns {"className":"gki-container gki-hero-grid align-items-center"} -->
<div class="wp-block-columns gki-container gki-hero-grid align-items-center"><!-- wp:column {"width":"44%","className":"gki-hero-copy"} -->
<div class="wp-block-column gki-hero-copy" style="flex-basis:44%"><!-- wp:paragraph {"className":"gki-eyebrow"} --><p class="gki-eyebrow">GKI Engineering Ltd</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">Reliable maintenance, welding and fabrication solutions.</h1><!-- /wp:heading -->
<!-- wp:paragraph {"className":"gki-hero-lead"} --><p class="gki-hero-lead">Commercial kitchen maintenance, commercial facility support, stainless steel welding and bespoke stainless steel fabrication for restaurants, kitchens and commercial buildings.</p><!-- /wp:paragraph -->
<!-- wp:buttons {"className":"gki-actions"} --><div class="wp-block-buttons gki-actions"><!-- wp:button {"className":"gki-btn-primary"} --><div class="wp-block-button gki-btn-primary"><a class="wp-block-button__link wp-element-button" href="#services">Our Services</a></div><!-- /wp:button --><!-- wp:button {"className":"is-style-outline gki-btn-secondary"} --><div class="wp-block-button is-style-outline gki-btn-secondary"><a class="wp-block-button__link wp-element-button" href="#about">Learn More</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div><!-- /wp:column --><!-- wp:column {"width":"56%","className":"gki-hero-media"} -->
<div class="wp-block-column gki-hero-media" style="flex-basis:56%"><?php echo gki_child_img_block($hero, 'Commercial kitchen hero image working on site', 'hero-card'); ?></div><!-- /wp:column --></div><!-- /wp:columns --></section><!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"gki-section","anchor":"about","layout":{"type":"default"}} -->
<section class="wp-block-group gki-section" id="about"><!-- wp:columns {"className":"gki-container gki-about-grid align-items-center"} -->
<div class="wp-block-columns gki-container gki-about-grid align-items-center"><!-- wp:column {"width":"50%","className":"gki-about-media"} -->
<div class="wp-block-column gki-about-media" style="flex-basis:50%"><?php echo gki_child_img_block($about, 'Commercial kitchen stainless steel maintenance', 'gki-about-img'); ?></div><!-- /wp:column --><!-- wp:column {"width":"50%","className":"gki-about-copy"} -->
<div class="wp-block-column gki-about-copy" style="flex-basis:50%"><!-- wp:heading --><h2 class="wp-block-heading">About GKI Engineering</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>GKI Engineering’s roots date back to 2015, when it began as a venture with a passion for delivering high-quality maintenance works. After years of dedication and hard work, the company was formally registered as GKI Engineering Ltd in May 2022. Our mission has always been to provide exceptional maintenance services for restaurants, kitchens, and commercial buildings, allowing our clients to focus on their core business. We take pride in our attention to detail and commitment to quality, which has earned us a strong reputation in the industry.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>In 2023, we expanded our services to include stainless steel welding and bespoke stainless steel product fabrication, further solidifying our position as a trusted partner for businesses seeking reliable and skilled maintenance solutions. Over the past nine years we’ve proudly served McDonald’s restaurants and many other small businesses across the UK, building strong, long-term relationships in the industry.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>At GKI Engineering Ltd, we’re guided by a simple yet powerful principle: “Do others as you would like to be done to yourself.” This philosophy underpins everything we do, from our work ethic to our client relationships, as we remain committed to delivering exceptional service and building long-term partnerships with our clients.</p><!-- /wp:paragraph --></div><!-- /wp:column --></div><!-- /wp:columns --></section><!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"gki-section gki-section-soft","anchor":"services","layout":{"type":"default"}} -->
<section class="wp-block-group gki-section gki-section-soft" id="services"><!-- wp:group {"className":"gki-container","layout":{"type":"default"}} --><div class="wp-block-group gki-container"><!-- wp:heading --><h2 class="wp-block-heading">Our Services</h2><!-- /wp:heading -->
<!-- wp:columns {"className":"gki-services"} --><div class="wp-block-columns gki-services">
<?php foreach ([['Plumbing',$plumbing],['Mechanical',$mechanical],['Facilities',$facility],['Welding',$welding],['Fabrication',$fabrication]] as $svc): ?>
<!-- wp:column {"className":"gki-service-card"} --><div class="wp-block-column gki-service-card"><?php echo gki_child_img_block($svc[1], $svc[0] . ' icon', 'gki-service-icon'); ?><!-- wp:paragraph --><p><?php echo esc_html($svc[0]); ?></p><!-- /wp:paragraph --></div><!-- /wp:column -->
<?php endforeach; ?>
</div><!-- /wp:columns --></div><!-- /wp:group --></section><!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"gki-section","anchor":"projects","layout":{"type":"default"}} -->
<section class="wp-block-group gki-section" id="projects"><!-- wp:group {"className":"gki-container","layout":{"type":"default"}} --><div class="wp-block-group gki-container"><!-- wp:heading --><h2 class="wp-block-heading">Recent Projects</h2><!-- /wp:heading -->
<!-- wp:columns {"className":"gki-projects"} --><div class="wp-block-columns gki-projects">
<!-- wp:column {"className":"gki-project"} --><div class="wp-block-column gki-project"><?php echo gki_child_img_block($restaurant, 'Restaurant maintenance project', 'gki-project-img'); ?><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Restaurant Maintenance</h3><!-- /wp:heading --></div><!-- /wp:column -->
<!-- wp:column {"className":"gki-project"} --><div class="wp-block-column gki-project"><?php echo gki_child_img_block($facilityImg, 'Commercial facility project', 'gki-project-img gki-project-img-top'); ?><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Commercial Facility</h3><!-- /wp:heading --></div><!-- /wp:column -->
<!-- wp:column {"className":"gki-project"} --><div class="wp-block-column gki-project"><?php echo gki_child_img_block($weldingImg, 'Stainless steel fabrication project', 'gki-project-img'); ?><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Stainless Steel Fabrication</h3><!-- /wp:heading --></div><!-- /wp:column -->
</div><!-- /wp:columns --></div><!-- /wp:group --></section><!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"gki-section gki-dark-section","layout":{"type":"default"}} -->
<section class="wp-block-group gki-section gki-dark-section"><!-- wp:group {"className":"gki-container","layout":{"type":"default"}} --><div class="wp-block-group gki-container"><!-- wp:heading --><h2 class="wp-block-heading">Why Choose GKI</h2><!-- /wp:heading -->
<!-- wp:columns {"className":"gki-why"} --><div class="wp-block-columns gki-why">
<!-- wp:column {"className":"gki-why-item"} --><div class="wp-block-column gki-why-item"><!-- wp:html --><?php echo gki_child_why_icon('professionals'); ?><!-- /wp:html --><!-- wp:paragraph --><p><strong>Professionals</strong><br><span>Skilled and experienced engineers.</span></p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column {"className":"gki-why-item"} --><div class="wp-block-column gki-why-item"><!-- wp:html --><?php echo gki_child_why_icon('reliable'); ?><!-- /wp:html --><!-- wp:paragraph --><p><strong>Reliable Solutions</strong><br><span>Delivering dependable results every time.</span></p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column {"className":"gki-why-item"} --><div class="wp-block-column gki-why-item"><!-- wp:html --><?php echo gki_child_why_icon('detail'); ?><!-- /wp:html --><!-- wp:paragraph --><p><strong>Detail Driven</strong><br><span>Precision and attention in every project.</span></p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column {"className":"gki-why-item"} --><div class="wp-block-column gki-why-item"><!-- wp:html --><?php echo gki_child_why_icon('partners'); ?><!-- /wp:html --><!-- wp:paragraph --><p><strong>Long-Term Partners</strong><br><span>Building lasting relationships with our clients.</span></p><!-- /wp:paragraph --></div><!-- /wp:column -->
</div><!-- /wp:columns -->
<!-- wp:columns {"className":"gki-info-row"} --><div class="wp-block-columns gki-info-row">
<!-- wp:column {"width":"50%","className":"gki-info-card gki-testimonials"} --><div class="wp-block-column gki-info-card gki-testimonials" style="flex-basis:50%"><!-- wp:heading --><h2 class="wp-block-heading">Testimonials</h2><!-- /wp:heading --><!-- wp:group {"className":"gki-swiper","layout":{"type":"default"}} --><div class="wp-block-group gki-swiper"><!-- wp:group {"className":"gki-slides","layout":{"type":"default"}} --><div class="wp-block-group gki-slides">
<!-- wp:group {"className":"gki-testimonial-card","layout":{"type":"default"}} --><div class="wp-block-group gki-testimonial-card"><!-- wp:paragraph --><p>Excellent support for our kitchen maintenance needs. Fast response, tidy workmanship and clear communication from start to finish.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><cite>Restaurant Manager</cite></p><!-- /wp:paragraph --></div><!-- /wp:group -->
<!-- wp:group {"className":"gki-testimonial-card","layout":{"type":"default"}} --><div class="wp-block-group gki-testimonial-card"><!-- wp:paragraph --><p>GKI Engineering helped us with stainless steel fabrication and the finished product was clean, strong and exactly to specification.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><cite>Commercial Kitchen Client</cite></p><!-- /wp:paragraph --></div><!-- /wp:group -->
<!-- wp:group {"className":"gki-testimonial-card","layout":{"type":"default"}} --><div class="wp-block-group gki-testimonial-card"><!-- wp:paragraph --><p>A reliable maintenance partner for commercial building works. Professional, punctual and focused on long-term service quality.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><cite>Facilities Client</cite></p><!-- /wp:paragraph --></div><!-- /wp:group -->
</div><!-- /wp:group --></div><!-- /wp:group --><!-- wp:html --><div class="gki-slider-menu" aria-label="Testimonials slider menu"></div><!-- /wp:html --></div><!-- /wp:column -->
<!-- wp:column {"width":"50%","className":"gki-info-card gki-coverage-card"} --><div class="wp-block-column gki-info-card gki-coverage-card" style="flex-basis:50%"><!-- wp:heading --><h2 class="wp-block-heading">Coverage Services</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Based in Birmingham, GKI Engineering provides commercial kitchen maintenance, facility support, stainless steel welding and bespoke fabrication services across the Midlands and London.</p><!-- /wp:paragraph --><!-- wp:buttons {"className":"gki-coverage"} --><div class="wp-block-buttons gki-coverage"><!-- wp:button {"className":"gki-chip"} --><div class="wp-block-button gki-chip"><a class="wp-block-button__link wp-element-button">Midlands</a></div><!-- /wp:button --><!-- wp:button {"className":"gki-chip"} --><div class="wp-block-button gki-chip"><a class="wp-block-button__link wp-element-button">London</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:column -->
</div><!-- /wp:columns --></div><!-- /wp:group --></section><!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"gki-section","anchor":"contact","layout":{"type":"default"}} -->
<section class="wp-block-group gki-section" id="contact"><!-- wp:group {"className":"gki-container","layout":{"type":"default"}} --><div class="wp-block-group gki-container"><!-- wp:heading --><h2 class="wp-block-heading">Contact Us</h2><!-- /wp:heading --><!-- wp:columns {"className":"gki-contact-grid"} --><div class="wp-block-columns gki-contact-grid"><!-- wp:column {"className":"gki-form-card"} --><div class="wp-block-column gki-form-card"><!-- wp:wpbb/dynamic-form {"formTitle":"Contact Us","recipient":"guntis@gkiengineering.co.uk","emailSubject":"New GKI Engineering website enquiry","successMessage":"Thank you. Your message has been sent.","submitText":"Send Message","showTitle":false,"formClass":"gki-bbuilder-form","buttonClass":"btn btn-primary gki-btn gki-btn-primary","stylePreset":"default","labelPosition":"top","gap":3,"fieldsJson":"[{\"type\":\"text\",\"name\":\"name\",\"label\":\"Name\",\"required\":true,\"width\":6,\"placeholder\":\"Name\",\"step\":1},{\"type\":\"phone\",\"name\":\"phone\",\"label\":\"Phone\",\"required\":false,\"width\":6,\"placeholder\":\"Phone\",\"step\":1},{\"type\":\"email\",\"name\":\"email\",\"label\":\"Email\",\"required\":true,\"width\":12,\"placeholder\":\"Email\",\"step\":1},{\"type\":\"textarea\",\"name\":\"message\",\"label\":\"Message\",\"required\":true,\"width\":12,\"placeholder\":\"Message\",\"step\":1}]"} /--></div><!-- /wp:column --><!-- wp:column {"className":"gki-contact-card"} --><div class="wp-block-column gki-contact-card"><!-- wp:html --><div class="gki-map"><iframe src="https://www.google.com/maps?q=116+Yardley+Road,+Acocks+Green,+Birmingham,+B27+6LG&output=embed" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade" title="GKI Engineering Ltd Google Map"></iframe></div><!-- /wp:html --><!-- wp:group {"className":"gki-contact-details","layout":{"type":"default"}} --><div class="wp-block-group gki-contact-details"><!-- wp:paragraph --><p><strong>GKI Engineering Ltd</strong></p><!-- /wp:paragraph --><!-- wp:paragraph --><p>116 Yardley Road<br>Acocks Green<br>Birmingham, England<br>B27 6LG</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><a href="https://find-and-update.company-information.service.gov.uk/company/14112841" target="_blank" rel="noopener noreferrer">Registered in England &amp; Wales · Company No. 14112841</a></p><!-- /wp:paragraph --></div><!-- /wp:group --></div><!-- /wp:column --></div><!-- /wp:columns --></div><!-- /wp:group --></section><!-- /wp:group -->

</div>
<!-- /wp:group -->
<!-- wp:html --><script id="gki-current-year-template-v50">document.querySelectorAll(".gki-current-year").forEach(function(el){el.textContent=(new Date()).getFullYear();});</script><!-- /wp:html -->
<?php
    return trim(ob_get_clean());
}

function gki_child_find_home_page() {
    $pages = get_posts([
        'post_type' => 'page',
        'post_status' => 'any',
        'posts_per_page' => 10,
        'orderby' => 'ID',
        'order' => 'DESC',
        's' => 'Home',
    ]);
    foreach ($pages as $p) {
        if (strtolower($p->post_title) === 'home' || get_post_meta($p->ID, '_gki_home_generated', true)) { return $p; }
    }
    return null;
}

function gki_child_create_home_page() {
    if (!current_user_can('manage_options')) { return; }
    $version_key = 'gki_home_page_installed_v53_editor_polish';
    if (get_option($version_key)) { return; }
    // v53 refreshes generated Home page with editor-valid Why icon blocks, 700 H1, location coverage chips, safer editor styling, and WP BBuilder hCaptcha support.
    $content = gki_child_build_home_content();
    $existing = gki_child_find_home_page();
    if ($existing) {
        wp_update_post(['ID' => $existing->ID, 'post_content' => $content, 'post_status' => 'publish']);
        $page_id = $existing->ID;
    } else {
        $page_id = wp_insert_post(['post_title' => 'Home', 'post_name' => 'home', 'post_type' => 'page', 'post_status' => 'publish', 'post_content' => $content]);
    }
    if ($page_id && !is_wp_error($page_id)) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', (int) $page_id);
        update_post_meta((int) $page_id, '_gki_home_generated', '1');
        update_option($version_key, time(), false);
    }
}
add_action('after_switch_theme', 'gki_child_create_home_page');
add_action('admin_init', 'gki_child_create_home_page');
