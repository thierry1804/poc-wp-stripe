<?php
/**
 * Plugin Name: WP Stripe Integration
 * Plugin URI: https://votre-site.com
 * Description: Plugin pour intégrer Stripe dans WordPress
 * Version: 1.0.0
 * Author: RANDRIANTIANA Thierry
 * License: GPL-2.0+
 * Text Domain: wp-stripe-integration
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Composer autoload
require_once __DIR__ . '/vendor/autoload.php';

use StripeIntegration\Admin\SettingsPage;
use StripeIntegration\Core\StripeIntegration;

class WPStripeIntegrationPlugin {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init();
    }

    private function init() {
        // Debug
        error_log('WP Stripe Integration Plugin Init');
        
        // Initialisation des composants
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        
        // Modification importante ici : on initialise directement la page de paramètres
        $settings_page = new SettingsPage();
        $settings_page->init();
        
        // Initialisation de l'intégration Stripe
        $stripe_integration = new StripeIntegration();
        $stripe_integration->init();
    }

    public function load_textdomain() {
        load_plugin_textdomain('wp-stripe-integration', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function activate() {
        // Actions lors de l'activation du plugin
        // Par exemple : vérifier les prérequis PHP, Stripe, etc.
    }

    public function deactivate() {
        // Actions lors de la désactivation du plugin
    }

    public function enqueue_admin_scripts($hook) {
        // Vérifier si nous sommes sur une page de notre plugin
        if (strpos($hook, 'wp-stripe-integration') === false) {
            return;
        }

        // Enregistrer Chart.js
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js',
            [],
            '4.4.1',
            true
        );

        // Enregistrer notre script personnalisé
        wp_enqueue_script(
            'wp-stripe-integration-admin',
            plugins_url('assets/js/admin.js', __FILE__),
            ['chartjs'],
            '1.0.0',
            true
        );

        wp_enqueue_style(
            'wp-stripe-integration-admin',
            plugins_url('assets/css/admin.css', __FILE__),
            [],
            '1.0.0'
        );
    }
}

// Initialisation du plugin
$wp_stripe_integration = WPStripeIntegrationPlugin::get_instance();

// Hooks d'activation et de désactivation
register_activation_hook(__FILE__, [$wp_stripe_integration, 'activate']);
register_deactivation_hook(__FILE__, [$wp_stripe_integration, 'deactivate']); 