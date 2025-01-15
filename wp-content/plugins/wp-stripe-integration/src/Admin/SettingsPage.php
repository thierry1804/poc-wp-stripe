<?php
namespace StripeIntegration\Admin;

class SettingsPage {
    public function init() {
        error_log('Stripe Menu Init');
        add_action('admin_menu', [$this, 'add_stripe_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_stripe_menu() {
        error_log('Adding Stripe Menu');
        add_menu_page(
            __('Stripe Integration', 'wp-stripe-integration'),
            __('Stripe', 'wp-stripe-integration'),
            'manage_options',
            'wp-stripe-integration',
            [$this, 'render_settings_page'],
            'dashicons-money-alt',
            25
        );

        add_submenu_page(
            'wp-stripe-integration',
            __('Paramètres', 'wp-stripe-integration'),
            __('Paramètres', 'wp-stripe-integration'),
            'manage_options',
            'wp-stripe-integration',
            [$this, 'render_settings_page']
        );

        add_submenu_page(
            'wp-stripe-integration',
            __('Transactions', 'wp-stripe-integration'),
            __('Transactions', 'wp-stripe-integration'),
            'manage_options',
            'wp-stripe-integration-transactions',
            [$this, 'render_transactions_page']
        );

        add_submenu_page(
            'wp-stripe-integration',
            __('Produits', 'wp-stripe-integration'),
            __('Produits', 'wp-stripe-integration'),
            'manage_options',
            'wp-stripe-integration-products',
            [$this, 'render_products_page']
        );
    }

    public function register_settings() {
        register_setting('wp_stripe_integration_options', 'wp_stripe_integration_options');

        add_settings_section(
            'wp_stripe_integration_main',
            __('Stripe Configuration', 'wp-stripe-integration'),
            [$this, 'section_text'],
            'wp-stripe-integration'
        );

        add_settings_field(
            'stripe_public_key',
            __('Stripe Public Key', 'wp-stripe-integration'),
            [$this, 'stripe_public_key_input'],
            'wp-stripe-integration',
            'wp_stripe_integration_main'
        );

        add_settings_field(
            'stripe_secret_key',
            __('Stripe Secret Key', 'wp-stripe-integration'),
            [$this, 'stripe_secret_key_input'],
            'wp-stripe-integration',
            'wp_stripe_integration_main'
        );
    }

    public function section_text() {
        echo '<p>' . __('Enter your Stripe API keys here.', 'wp-stripe-integration') . '</p>';
    }

    public function stripe_public_key_input() {
        $options = get_option('wp_stripe_integration_options');
        echo "<input id='stripe_public_key' name='wp_stripe_integration_options[stripe_public_key]' type='text' value='" . esc_attr($options['stripe_public_key'] ?? '') . "' />";
    }

    public function stripe_secret_key_input() {
        $options = get_option('wp_stripe_integration_options');
        echo "<input id='stripe_secret_key' name='wp_stripe_integration_options[stripe_secret_key]' type='password' value='" . esc_attr($options['stripe_secret_key'] ?? '') . "' />";
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('wp_stripe_integration_options');
                do_settings_sections('wp-stripe-integration');
                submit_button(__('Save Settings', 'wp-stripe-integration'));
                ?>
            </form>
        </div>
        <?php
    }
} 