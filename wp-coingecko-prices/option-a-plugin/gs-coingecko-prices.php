<?php
/**
 * Plugin Name: GlobalStake CoinGecko Prices
 * Description: Replaces CryptoWP with CoinGecko-powered price shortcodes. Usage: [gs_price symbol="SOL"] for price, [gs_change symbol="SOL"] for 24h change.
 * Version: 1.0.0
 * Author: GlobalStake
 * License: MIT
 */

if (!defined('ABSPATH')) exit;

// ── CoinGecko symbol-to-ID mapping ────────────────────────────────
// CoinGecko uses slug IDs (e.g. "solana") not ticker symbols.
// Add new assets here as needed.
define('GS_COINGECKO_MAP', [
    'ETH'    => 'ethereum',
    'OSETH'  => 'stakewise-staked-eth',
    'BNB'    => 'binancecoin',
    'SOL'    => 'solana',
    'ADA'    => 'cardano',
    'TRX'    => 'tron',
    'BERA'   => 'berachain-bera',
    'AVAX'   => 'avalanche-2',
    'TON'    => 'the-open-network',
    'DOT'    => 'polkadot',
    'KSM'    => 'kusama',
    'VARA'   => 'vara-network',
    'CFG'    => 'centrifuge',
    'POLYX'  => 'polymesh',
    'AVAIL'  => 'avail',
    'ATOM'   => 'cosmos',
    'ARCH'   => 'archway',
    'NEAR'   => 'near',
    'SUI'    => 'sui',
    'APT'    => 'aptos',
    'XTZ'    => 'tezos',
    'ALGO'   => 'algorand',
    'S'      => 'sonic-svm',
    'EGLD'   => 'multiversx-egold',
    'ONE'    => 'harmony',
    'ZIL'    => 'zilliqa',
    'ICX'    => 'icon',
    'SEI'    => 'sei-network',
    'LYX'    => 'lukso-token-2',
    'TAO'    => 'bittensor',
    'BABY'   => 'babylon',
    'AR'     => 'arweave',
    'FIL'    => 'filecoin',
    'WAL'    => 'walrus-2',
    'PLUME'  => 'plume-network',
]);

// ── Transient cache key ────────────────────────────────────────────
define('GS_CACHE_KEY', 'gs_coingecko_prices');
define('GS_CACHE_TTL', 900); // 15 minutes

/**
 * Fetch all prices in a single batch call to CoinGecko.
 * Cached in a WP transient for 15 minutes.
 */
function gs_fetch_all_prices() {
    $cached = get_transient(GS_CACHE_KEY);
    if ($cached !== false) {
        return $cached;
    }

    $ids = implode(',', array_values(GS_COINGECKO_MAP));
    $url = 'https://api.coingecko.com/api/v3/simple/price?ids=' . $ids . '&vs_currencies=usd&include_24hr_change=true';

    $response = wp_remote_get($url, [
        'timeout' => 10,
        'headers' => ['Accept' => 'application/json'],
    ]);

    if (is_wp_error($response)) {
        return [];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!is_array($data)) {
        return [];
    }

    // Restructure: symbol => { price, change }
    $prices = [];
    foreach (GS_COINGECKO_MAP as $symbol => $cg_id) {
        if (isset($data[$cg_id])) {
            $prices[$symbol] = [
                'price'  => $data[$cg_id]['usd'] ?? null,
                'change' => $data[$cg_id]['usd_24h_change'] ?? null,
            ];
        }
    }

    set_transient(GS_CACHE_KEY, $prices, GS_CACHE_TTL);
    return $prices;
}

/**
 * Shortcode: [gs_price symbol="SOL"]
 * Outputs the current USD price.
 */
function gs_price_shortcode($atts) {
    $atts = shortcode_atts(['symbol' => ''], $atts, 'gs_price');
    $symbol = strtoupper(trim($atts['symbol']));

    if (empty($symbol) || !isset(GS_COINGECKO_MAP[$symbol])) {
        return '<span class="gs-price gs-price-error">--</span>';
    }

    $prices = gs_fetch_all_prices();

    if (!isset($prices[$symbol]) || $prices[$symbol]['price'] === null) {
        return '<span class="gs-price gs-price-error">--</span>';
    }

    $price = $prices[$symbol]['price'];

    // Format: show more decimals for sub-$1 tokens
    if ($price < 0.01) {
        $formatted = '$' . number_format($price, 6);
    } elseif ($price < 1) {
        $formatted = '$' . number_format($price, 4);
    } else {
        $formatted = '$' . number_format($price, 2);
    }

    return '<span class="gs-price">' . esc_html($formatted) . '</span>';
}
add_shortcode('gs_price', 'gs_price_shortcode');

/**
 * Shortcode: [gs_change symbol="SOL"]
 * Outputs the 24h percent change with up/down styling.
 */
function gs_change_shortcode($atts) {
    $atts = shortcode_atts(['symbol' => ''], $atts, 'gs_change');
    $symbol = strtoupper(trim($atts['symbol']));

    if (empty($symbol) || !isset(GS_COINGECKO_MAP[$symbol])) {
        return '<span class="gs-change">--</span>';
    }

    $prices = gs_fetch_all_prices();

    if (!isset($prices[$symbol]) || $prices[$symbol]['change'] === null) {
        return '<span class="gs-change">--</span>';
    }

    $change = $prices[$symbol]['change'];
    $sign   = $change >= 0 ? '+' : '';
    $class  = $change >= 0 ? 'gs-change-up' : 'gs-change-down';

    return '<span class="gs-change ' . $class . '">' . esc_html($sign . number_format($change, 2) . '%') . '</span>';
}
add_shortcode('gs_change', 'gs_change_shortcode');

/**
 * Combined shortcode: [gs_price_widget symbol="SOL"]
 * Outputs price + 24h change together (drop-in replacement for CryptoWP shortcodes).
 */
function gs_price_widget_shortcode($atts) {
    $atts = shortcode_atts(['symbol' => ''], $atts, 'gs_price_widget');
    $symbol = strtoupper(trim($atts['symbol']));

    return gs_price_shortcode(['symbol' => $symbol]) . ' ' . gs_change_shortcode(['symbol' => $symbol]);
}
add_shortcode('gs_price_widget', 'gs_price_widget_shortcode');

/**
 * Enqueue minimal inline styles.
 */
function gs_enqueue_styles() {
    $css = '
        .gs-price { font-weight: 600; }
        .gs-price-error { color: #888; }
        .gs-change { font-size: 0.9em; margin-left: 0.3em; }
        .gs-change-up { color: #22c55e; }
        .gs-change-down { color: #ef4444; }
    ';
    wp_register_style('gs-coingecko-prices', false);
    wp_enqueue_style('gs-coingecko-prices');
    wp_add_inline_style('gs-coingecko-prices', $css);
}
add_action('wp_enqueue_scripts', 'gs_enqueue_styles');
