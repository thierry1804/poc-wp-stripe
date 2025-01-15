<?php
namespace StripeIntegration\Core;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Charge;

class TransactionManager {
    public function __construct() {
        $options = get_option('wp_stripe_integration_options');
        if (!empty($options['stripe_secret_key'])) {
            Stripe::setApiKey($options['stripe_secret_key']);
        }
    }

    /**
     * Récupère toutes les transactions depuis Stripe
     */
    public function get_transactions($limit = 100) {
        try {
            // Récupérer les paiements via PaymentIntent
            $payment_intents = PaymentIntent::all([
                'limit' => $limit,
                'expand' => ['data.customer', 'data.payment_method']
            ]);

            $formatted_transactions = [];

            foreach ($payment_intents as $intent) {
                $formatted_transactions[] = [
                    'id' => $intent->id,
                    'amount' => $intent->amount / 100, // Conversion en euros
                    'currency' => strtoupper($intent->currency),
                    'status' => $this->format_status($intent->status),
                    'date' => date('Y-m-d H:i:s', $intent->created),
                    'customer_email' => $intent->customer ? $intent->customer->email : '',
                    'payment_method' => $intent->payment_method ? $intent->payment_method->type : '',
                    'metadata' => $intent->metadata->toArray()
                ];
            }

            return $formatted_transactions;
        } catch (\Exception $e) {
            error_log('Stripe Transaction Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Formate le statut pour l'affichage
     */
    private function format_status($status) {
        $statuses = [
            'succeeded' => __('Réussie', 'wp-stripe-integration'),
            'processing' => __('En cours', 'wp-stripe-integration'),
            'requires_payment_method' => __('En attente', 'wp-stripe-integration'),
            'requires_confirmation' => __('À confirmer', 'wp-stripe-integration'),
            'requires_action' => __('Action requise', 'wp-stripe-integration'),
            'canceled' => __('Annulée', 'wp-stripe-integration'),
        ];

        return $statuses[$status] ?? $status;
    }

    /**
     * Récupère les détails d'une transaction spécifique
     */
    public function get_transaction_details($transaction_id) {
        try {
            $intent = PaymentIntent::retrieve([
                'id' => $transaction_id,
                'expand' => ['customer', 'payment_method', 'charges.data']
            ]);

            return [
                'id' => $intent->id,
                'amount' => $intent->amount / 100,
                'currency' => strtoupper($intent->currency),
                'status' => $this->format_status($intent->status),
                'date' => date('Y-m-d H:i:s', $intent->created),
                'customer_email' => $intent->customer ? $intent->customer->email : '',
                'payment_method' => $intent->payment_method ? $intent->payment_method->type : '',
                'metadata' => $intent->metadata->toArray(),
                'charges' => $intent->charges->data
            ];
        } catch (\Exception $e) {
            error_log('Stripe Transaction Detail Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère les transactions avec filtres
     */
    public function get_filtered_transactions($filters = [], $limit = 100) {
        try {
            $params = [
                'limit' => $limit,
                'expand' => ['data.customer', 'data.payment_method']
            ];

            // Filtre par date
            if (!empty($filters['date_start'])) {
                $params['created']['gte'] = strtotime($filters['date_start']);
            }
            if (!empty($filters['date_end'])) {
                $params['created']['lte'] = strtotime($filters['date_end']);
            }

            // Filtre par statut
            if (!empty($filters['status'])) {
                $params['status'] = $filters['status'];
            }

            $payment_intents = PaymentIntent::all($params);
            $formatted_transactions = [];

            foreach ($payment_intents as $intent) {
                // Filtre par email si spécifié
                if (!empty($filters['email']) && 
                    (!$intent->customer || 
                     stripos($intent->customer->email, $filters['email']) === false)) {
                    continue;
                }

                $formatted_transactions[] = [
                    'id' => $intent->id,
                    'amount' => $intent->amount / 100,
                    'currency' => strtoupper($intent->currency),
                    'status' => $this->format_status($intent->status),
                    'date' => date('Y-m-d H:i:s', $intent->created),
                    'customer_email' => $intent->customer ? $intent->customer->email : '',
                    'payment_method' => $intent->payment_method ? $intent->payment_method->type : '',
                    'metadata' => $intent->metadata->toArray()
                ];
            }

            return $formatted_transactions;
        } catch (\Exception $e) {
            error_log('Stripe Filtered Transactions Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Exporte les transactions en CSV
     */
    public function export_transactions_csv($transactions) {
        $filename = 'stripe-transactions-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // En-têtes CSV
        fputcsv($output, [
            'ID',
            'Date',
            'Client',
            'Montant',
            'Devise',
            'Statut',
            'Méthode de paiement'
        ]);
        
        // Données
        foreach ($transactions as $transaction) {
            fputcsv($output, [
                $transaction['id'],
                $transaction['date'],
                $transaction['customer_email'],
                $transaction['amount'],
                $transaction['currency'],
                $transaction['status'],
                $transaction['payment_method']
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Récupère les statistiques des transactions
     */
    public function get_transactions_stats($filters = []) {
        $transactions = $this->get_filtered_transactions($filters);
        
        $stats = [
            'total_amount' => 0,
            'count' => count($transactions),
            'status_count' => [],
            'daily_amounts' => [],
            'success_rate' => 0
        ];
        
        foreach ($transactions as $transaction) {
            // Montant total
            if ($transaction['status'] === __('Réussie', 'wp-stripe-integration')) {
                $stats['total_amount'] += $transaction['amount'];
            }
            
            // Comptage par statut
            if (!isset($stats['status_count'][$transaction['status']])) {
                $stats['status_count'][$transaction['status']] = 0;
            }
            $stats['status_count'][$transaction['status']]++;
            
            // Montants journaliers
            $date = date('Y-m-d', strtotime($transaction['date']));
            if (!isset($stats['daily_amounts'][$date])) {
                $stats['daily_amounts'][$date] = 0;
            }
            if ($transaction['status'] === __('Réussie', 'wp-stripe-integration')) {
                $stats['daily_amounts'][$date] += $transaction['amount'];
            }
        }
        
        // Taux de réussite
        if ($stats['count'] > 0) {
            $success_count = $stats['status_count'][__('Réussie', 'wp-stripe-integration')] ?? 0;
            $stats['success_rate'] = round(($success_count / $stats['count']) * 100, 2);
        }
        
        return $stats;
    }

    /**
     * Récupère les transactions paginées
     */
    public function get_paginated_transactions($filters = [], $page = 1, $per_page = 20) {
        $transactions = $this->get_filtered_transactions($filters);
        
        $total = count($transactions);
        $total_pages = ceil($total / $per_page);
        $offset = ($page - 1) * $per_page;
        
        return [
            'data' => array_slice($transactions, $offset, $per_page),
            'total' => $total,
            'total_pages' => $total_pages,
            'current_page' => $page
        ];
    }
} 