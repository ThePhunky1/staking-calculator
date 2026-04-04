OPTION A — WordPress Plugin: GlobalStake CoinGecko Prices
==========================================================

WHAT IT DOES
------------
Replaces the CryptoWP plugin with CoinGecko-powered shortcodes.
Fetches all prices in a single API call and caches them server-side
for 15 minutes using WordPress transients. No API key required.

INSTALLATION
------------
1. Upload the file gs-coingecko-prices.php to:
   /wp-content/plugins/gs-coingecko-prices/gs-coingecko-prices.php

   Or create a zip containing the file and upload via:
   WordPress Admin > Plugins > Add New > Upload Plugin

2. Activate the plugin in WordPress Admin > Plugins.

3. Replace each CryptoWP shortcode in your Elementor tables:

   OLD (CryptoWP):
     [crypto id="1" type="price"]
     [crypto id="1" type="percent"]

   NEW (this plugin):
     [gs_price symbol="ETH"]
     [gs_change symbol="ETH"]

   Or use the combined shortcode for both price + change:
     [gs_price_widget symbol="ETH"]

4. Once all shortcodes are migrated, deactivate and delete CryptoWP.

SUPPORTED SYMBOLS
-----------------
ETH, osETH, BNB, SOL, ADA, TRX, BERA, AVAX, TON, DOT, KSM, VARA,
CFG, POLYX, AVAIL, ATOM, ARCH, NEAR, SUI, APT, XTZ, ALGO, S, EGLD,
ONE, ZIL, ICX, SEI, LYX, TAO, BABY, AR, FIL, WAL, PLUME

To add a new token, edit the GS_COINGECKO_MAP array in the PHP file.
Find the CoinGecko ID at: https://www.coingecko.com/ (it's the URL slug).

CSS CLASSES
-----------
.gs-price          — price value
.gs-price-error    — shown when price unavailable
.gs-change         — 24h change container
.gs-change-up      — positive change (green)
.gs-change-down    — negative change (red)

You can override these in your theme's custom CSS or Elementor's
custom CSS panel to match your existing table styling.

PROS OF THIS OPTION
-------------------
- Server-side caching = fast page loads, no browser-side API calls
- Works natively with Elementor shortcode widgets
- Single batch API call for all tokens (efficient)
- No CORS issues
- SEO-friendly (prices are in the HTML source)

CONS
----
- Requires plugin upload access
- Prices update every 15 minutes (adjustable via GS_CACHE_TTL constant)
