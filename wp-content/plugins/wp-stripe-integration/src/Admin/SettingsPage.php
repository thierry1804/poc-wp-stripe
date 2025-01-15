<?php
namespace StripeIntegration\Admin;

use StripeIntegration\Core\ProductManager;
use StripeIntegration\Core\TransactionManager;

class SettingsPage {
    private $product_manager;
    private $transaction_manager;

    public function __construct() {
        $this->product_manager = new ProductManager();
        $this->transaction_manager = new TransactionManager();
    }

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

    /**
     * Affiche la page des transactions
     */
    public function render_transactions_page() {
        // Gestion de l'export CSV
        if (isset($_POST['action']) && $_POST['action'] === 'export_csv') {
            $transactions = $this->transaction_manager->get_transactions();
            $this->transaction_manager->export_transactions_csv($transactions);
            return;
        }

        // Récupération des filtres
        $filters = [
            'date_start' => $_GET['date_start'] ?? '',
            'date_end' => $_GET['date_end'] ?? '',
            'status' => $_GET['status'] ?? '',
            'email' => $_GET['email'] ?? ''
        ];

        // Pagination
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        
        // Récupérer les transactions paginées et les stats
        $paginated_results = $this->transaction_manager->get_paginated_transactions($filters, $current_page, $per_page);
        $stats = $this->transaction_manager->get_transactions_stats($filters);
        
        ?>
        <div class="wrap">
            <h1>
                <?php echo esc_html(get_admin_page_title()); ?>
                <form method="post" style="display: inline-block;">
                    <input type="hidden" name="action" value="export_csv">
                    <button type="submit" class="page-title-action">
                        <?php _e('Exporter en CSV', 'wp-stripe-integration'); ?>
                    </button>
                </form>
            </h1>

            <!-- Dashboard des statistiques -->
            <div class="dashboard-widgets-wrap">
                <div class="metabox-holder">
                    <div class="postbox-container" style="width: 49%; float: left; margin-right: 1%;">
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e('Statistiques globales', 'wp-stripe-integration'); ?></span></h2>
                            <div class="inside">
                                <p><strong><?php _e('Montant total :', 'wp-stripe-integration'); ?></strong> 
                                   <?php echo number_format($stats['total_amount'], 2); ?> €</p>
                                <p><strong><?php _e('Nombre de transactions :', 'wp-stripe-integration'); ?></strong> 
                                   <?php echo $stats['count']; ?></p>
                                <p><strong><?php _e('Taux de réussite :', 'wp-stripe-integration'); ?></strong> 
                                   <?php echo $stats['success_rate']; ?>%</p>
                            </div>
                        </div>
                    </div>
                    <div class="postbox-container" style="width: 49%; float: left;">
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e('Répartition par statut', 'wp-stripe-integration'); ?></span></h2>
                            <div class="inside">
                                <?php foreach ($stats['status_count'] as $status => $count): ?>
                                    <p><strong><?php echo esc_html($status); ?> :</strong> <?php echo $count; ?></p>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </div>

            <!-- Graphique des transactions -->
            <div class="postbox">
                <h2 class="hndle"><span><?php _e('Évolution des transactions', 'wp-stripe-integration'); ?></span></h2>
                <div class="inside">
                    <canvas id="transactions-chart" 
                            data-transactions='<?php echo esc_attr(json_encode($stats['daily_amounts'])); ?>'
                            style="width: 100%; height: 300px;">
                    </canvas>
                </div>
            </div>

            <!-- Formulaire de filtres -->
            <div class="card">
                <h2><?php _e('Filtres', 'wp-stripe-integration'); ?></h2>
                <form method="get">
                    <input type="hidden" name="page" value="wp-stripe-integration-transactions">
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Période', 'wp-stripe-integration'); ?></th>
                            <td>
                                <input type="date" name="date_start" value="<?php echo esc_attr($filters['date_start']); ?>">
                                -
                                <input type="date" name="date_end" value="<?php echo esc_attr($filters['date_end']); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Statut', 'wp-stripe-integration'); ?></th>
                            <td>
                                <select name="status">
                                    <option value=""><?php _e('Tous', 'wp-stripe-integration'); ?></option>
                                    <option value="succeeded" <?php selected($filters['status'], 'succeeded'); ?>>
                                        <?php _e('Réussie', 'wp-stripe-integration'); ?>
                                    </option>
                                    <option value="processing" <?php selected($filters['status'], 'processing'); ?>>
                                        <?php _e('En cours', 'wp-stripe-integration'); ?>
                                    </option>
                                    <option value="canceled" <?php selected($filters['status'], 'canceled'); ?>>
                                        <?php _e('Annulée', 'wp-stripe-integration'); ?>
                                    </option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Email client', 'wp-stripe-integration'); ?></th>
                            <td>
                                <input type="email" name="email" value="<?php echo esc_attr($filters['email']); ?>">
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(__('Filtrer', 'wp-stripe-integration')); ?>
                </form>
            </div>

            <!-- Table des transactions -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'wp-stripe-integration'); ?></th>
                        <th><?php _e('Date', 'wp-stripe-integration'); ?></th>
                        <th><?php _e('Client', 'wp-stripe-integration'); ?></th>
                        <th><?php _e('Montant', 'wp-stripe-integration'); ?></th>
                        <th><?php _e('Statut', 'wp-stripe-integration'); ?></th>
                        <th><?php _e('Actions', 'wp-stripe-integration'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($paginated_results['data'])) : ?>
                        <tr>
                            <td colspan="6"><?php _e('Aucune transaction pour le moment.', 'wp-stripe-integration'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($paginated_results['data'] as $transaction) : ?>
                            <tr>
                                <td><?php echo esc_html($transaction['id']); ?></td>
                                <td><?php echo esc_html($transaction['date']); ?></td>
                                <td><?php echo esc_html($transaction['customer_email']); ?></td>
                                <td><?php echo esc_html(number_format($transaction['amount'], 2) . ' ' . $transaction['currency']); ?></td>
                                <td>
                                    <span class="status-<?php echo sanitize_html_class(strtolower($transaction['status'])); ?>">
                                        <?php echo esc_html($transaction['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo add_query_arg(['transaction_id' => $transaction['id']]); ?>" 
                                       class="button button-small">
                                        <?php _e('Détails', 'wp-stripe-integration'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($paginated_results['total_pages'] > 1) : ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <span class="displaying-num">
                            <?php printf(
                                _n('%s élément', '%s éléments', $paginated_results['total'], 'wp-stripe-integration'),
                                number_format_i18n($paginated_results['total'])
                            ); ?>
                        </span>
                        <?php
                        echo paginate_links([
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => __('&laquo;'),
                            'next_text' => __('&raquo;'),
                            'total' => $paginated_results['total_pages'],
                            'current' => $paginated_results['current_page']
                        ]);
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_transaction_details($transaction_id) {
        $transaction = $this->transaction_manager->get_transaction_details($transaction_id);
        if (!$transaction) {
            wp_die(__('Transaction non trouvée.', 'wp-stripe-integration'));
        }
        ?>
        <div class="wrap">
            <h1>
                <?php _e('Détails de la transaction', 'wp-stripe-integration'); ?>
                <a href="<?php echo remove_query_arg('transaction_id'); ?>" class="page-title-action">
                    <?php _e('Retour à la liste', 'wp-stripe-integration'); ?>
                </a>
            </h1>

            <div class="card">
                <h2><?php _e('Informations générales', 'wp-stripe-integration'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><?php _e('ID', 'wp-stripe-integration'); ?></th>
                        <td><?php echo esc_html($transaction['id']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Date', 'wp-stripe-integration'); ?></th>
                        <td><?php echo esc_html($transaction['date']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Montant', 'wp-stripe-integration'); ?></th>
                        <td><?php echo esc_html(number_format($transaction['amount'], 2) . ' ' . $transaction['currency']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Statut', 'wp-stripe-integration'); ?></th>
                        <td><?php echo esc_html($transaction['status']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Client', 'wp-stripe-integration'); ?></th>
                        <td><?php echo esc_html($transaction['customer_email']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Méthode de paiement', 'wp-stripe-integration'); ?></th>
                        <td><?php echo esc_html($transaction['payment_method']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Affiche la page des produits
     */
    public function render_products_page() {
        // Traitement du formulaire d'ajout de produit
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_product') {
            $this->handle_create_product();
        }

        $products = $this->product_manager->get_products();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <!-- Formulaire d'ajout de produit -->
            <div class="card">
                <h2><?php _e('Ajouter un produit', 'wp-stripe-integration'); ?></h2>
                <form method="post" action="">
                    <input type="hidden" name="action" value="create_product">
                    <?php wp_nonce_field('create_stripe_product', 'stripe_product_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="product_name"><?php _e('Nom', 'wp-stripe-integration'); ?></label></th>
                            <td><input type="text" id="product_name" name="product_name" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="product_description"><?php _e('Description', 'wp-stripe-integration'); ?></label></th>
                            <td><textarea id="product_description" name="product_description" class="regular-text" rows="3"></textarea></td>
                        </tr>
                        <tr>
                            <th><label for="product_price"><?php _e('Prix (EUR)', 'wp-stripe-integration'); ?></label></th>
                            <td><input type="number" id="product_price" name="product_price" class="regular-text" step="0.01" required></td>
                        </tr>
                    </table>
                    
                    <?php submit_button(__('Ajouter le produit', 'wp-stripe-integration')); ?>
                </form>
            </div>

            <!-- Liste des produits -->
            <h2><?php _e('Produits existants', 'wp-stripe-integration'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'wp-stripe-integration'); ?></th>
                        <th><?php _e('Nom', 'wp-stripe-integration'); ?></th>
                        <th><?php _e('Description', 'wp-stripe-integration'); ?></th>
                        <th><?php _e('Prix', 'wp-stripe-integration'); ?></th>
                        <th><?php _e('Actions', 'wp-stripe-integration'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)) : ?>
                        <tr>
                            <td colspan="5"><?php _e('Aucun produit pour le moment.', 'wp-stripe-integration'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($products as $product) : ?>
                            <tr>
                                <td><?php echo esc_html($product['id']); ?></td>
                                <td><?php echo esc_html($product['name']); ?></td>
                                <td><?php echo esc_html($product['description']); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($product['prices'])) {
                                        foreach ($product['prices'] as $price) {
                                            echo esc_html(number_format($price['amount'], 2) . ' ' . strtoupper($price['currency']));
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="#" class="button button-small"><?php _e('Modifier', 'wp-stripe-integration'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private function handle_create_product() {
        if (!isset($_POST['stripe_product_nonce']) || !wp_verify_nonce($_POST['stripe_product_nonce'], 'create_stripe_product')) {
            wp_die(__('Sécurité : jeton invalide.', 'wp-stripe-integration'));
        }

        $name = sanitize_text_field($_POST['product_name']);
        $description = sanitize_textarea_field($_POST['product_description']);
        $price = floatval($_POST['product_price']);

        $result = $this->product_manager->create_product($name, $description, $price);

        if ($result['success']) {
            add_settings_error(
                'wp_stripe_integration',
                'product_created',
                __('Produit créé avec succès.', 'wp-stripe-integration'),
                'success'
            );
        } else {
            add_settings_error(
                'wp_stripe_integration',
                'product_error',
                sprintf(__('Erreur lors de la création du produit : %s', 'wp-stripe-integration'), $result['error']),
                'error'
            );
        }
    }
} 