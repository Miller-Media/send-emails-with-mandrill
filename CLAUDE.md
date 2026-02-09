# Send Emails with Mandrill

## Overview
Replaces WordPress's `wp_mail()` function with Mandrill (Mailchimp Transactional Email) API. Forked from the now-unsupported wpMandrill plugin. Includes reporting dashboard, template support, and tracking.

## Architecture

```
wpmandrill.php                 # Entry point, defines constants
lib/
├── wpMandrill.class.php       # wpMandrill - main plugin class (static)
├── mandrill.class.php         # Mandrill - API wrapper
└── pluginActivation.class.php # Activation hooks
legacy/
└── function.wp_mail.php       # Legacy wp_mail fallback
stats.php                      # Stats display page
how-tos.php                    # Documentation page
```

## Key Classes

### wpMandrill (lib/wpMandrill.class.php)
Main plugin class. All static methods.

- `on_load()` - Plugin initialization. Defines `wp_mail()` function if no conflict.
- `isConfigured()` - Checks if API key is set
- `mail($to, $subject, $message, $headers, $attachments)` - Sends email via Mandrill API
- `evaluate_response($response)` - Validates API response status (`sent`/`queued`)
- `sendTestEmail($to)` - Test email functionality
- `getAPIKey()` - Returns stored API key
- Settings field renderers: `askAPIKey()`, `askFromName()`, `askFromEmail()`, `askReplyTo()`, etc.

### Mandrill (lib/mandrill.class.php)
API wrapper for Mandrill's REST API.

- `__construct($api)` - Validates API key via `users/ping2`
- `request($method, $args, $http, $output)` - Master request method
- `http_request($url, $fields, $method)` - Low-level HTTP using `wp_remote_request()`
- `messages_send($message)` - Send email
- `messages_send_template($template_name, $template_content, $message)` - Send with template
- `users_ping()`, `users_info()`, `users_senders()` - User API methods
- `tags_list()`, `templates_list()`, `webhooks_list()` - Management methods
- `getAttachmentStruct($path)` - Static: builds attachment structure from file path

## wp_mail() Override
The plugin defines a custom `wp_mail()` function in `on_load()` if:
1. No other plugin has already defined `wp_mail()`
2. The plugin is configured with an API key

On failure, falls back to native `wp_mail_native` action.

## Settings (wp_options)
- `wpmandrill` - All configuration: API key, from name/email, reply-to, sub-account, template, tracking options, tags, nl2br settings

## API
- **Endpoint:** `https://mandrillapp.com/api/1.0/`
- **Auth:** API key passed in request body
- Uses `wp_remote_request()` for all HTTP calls

## Hooks & Filters
- `wp_mail_native` - Fallback action for failures
- `mandrill_response_received` - Fires after successful send
- `mandrill_nl2br` - Controls nl2br processing
- `wpmandrill_enable_reports` - Toggle reports display

## Testing
Tests are in `../tests/unit/send-emails-with-mandrill/`. Run with:
```bash
make test-plugin PLUGIN=send-emails-with-mandrill
```

## Known Issues
- `lib/mandrill.class.php` line 28 has a PHP 4 constructor (`function Mandrill()`) that triggers a deprecation notice on PHP 8.0+ and will error on future PHP versions. It should be removed.
- WooCommerce nl2br compatibility fix is present in the mail method
