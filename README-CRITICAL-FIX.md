GKI v32 Critical Fix

This build removes the WP BBuilder dynamic-form block from the generated Home page because that block has repeatedly caused critical errors and broken admin rendering on the live site.

The contact form now uses a safe shortcode block: [gki_contact_form]
- sends to guntis@gkiengineering.co.uk
- includes Subject field
- includes hCaptcha with the provided site/secret keys
- avoids the WP BBuilder form block PHP warnings/fatal errors

After installing: activate the child theme, open the Home page once in admin to refresh the generated content, then clear cache/CDN.
