<?php
namespace StripeIntegration\Core;

use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;

class ProductManager {
    private $stripe;

    public function __construct() {
        $options = get_option('wp_stripe_integration_options');
        if (!empty($options['stripe_secret_key'])) {
            Stripe::setApiKey($options['stripe_secret_key']);
        }
    }

    /**
     * Récupère tous les produits depuis Stripe
     */
    public function get_products() {
        try {
            $products = Product::all(['active' => true, 'limit' => 100]);
            $formatted_products = [];

            foreach ($products as $product) {
                // Récupérer les prix pour ce produit
                $prices = Price::all(['product' => $product->id, 'active' => true]);
                
                $formatted_products[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'prices' => array_map(function($price) {
                        return [
                            'id' => $price->id,
                            'amount' => $price->unit_amount / 100, // Conversion en euros
                            'currency' => $price->currency
                        ];
                    }, $prices->data)
                ];
            }

            return $formatted_products;
        } catch (\Exception $e) {
            error_log('Stripe Product Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Crée un nouveau produit dans Stripe
     */
    public function create_product($name, $description, $price, $currency = 'eur') {
        try {
            // Créer le produit
            $product = Product::create([
                'name' => $name,
                'description' => $description,
            ]);

            // Créer le prix pour ce produit
            $price_object = Price::create([
                'product' => $product->id,
                'unit_amount' => $price * 100, // Conversion en centimes
                'currency' => $currency,
            ]);

            return [
                'success' => true,
                'product' => $product,
                'price' => $price_object
            ];
        } catch (\Exception $e) {
            error_log('Stripe Product Creation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 