<?php
namespace StripeIntegration\Core;

use Stripe\Stripe;
use StripeIntegration\Helpers\ConfigHelper;

class StripeIntegration {
    private $config;

    public function init() {
        $this->load_config();
        $this->setup_stripe_api();
        
        // Ajoutez ici vos hooks et actions spécifiques à Stripe
    }

    private function load_config() {
        $this->config = new ConfigHelper();
    }

    private function setup_stripe_api() {
        $secret_key = $this->config->get_stripe_secret_key();
        
        if ($secret_key) {
            Stripe::setApiKey($secret_key);
        }
    }

    // Méthodes pour les paiements, webhooks, etc.
} 