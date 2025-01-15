<?php
namespace StripeIntegration\Helpers;

class ConfigHelper {
    public function get_stripe_public_key() {
        $options = get_option('wp_stripe_integration_options');
        return $options['stripe_public_key'] ?? '';
    }

    public function get_stripe_secret_key() {
        $options = get_option('wp_stripe_integration_options');
        return $options['stripe_secret_key'] ?? '';
    }
} 