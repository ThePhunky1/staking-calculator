/**
 * GlobalStake CoinGecko Price Widget — Client-Side Edition
 * =========================================================
 * Drop this script into your WordPress page via an Elementor HTML widget
 * or your theme's header/footer scripts (e.g. Insert Headers and Footers plugin).
 *
 * USAGE:
 * 1. In your Elementor table cells, replace CryptoWP shortcodes with:
 *
 *    <span data-gs-price="SOL"></span>
 *    <span data-gs-change="SOL"></span>
 *
 *    Or combined:
 *    <span data-gs-price="SOL"></span> <span data-gs-change="SOL"></span>
 *
 * 2. Add this script tag anywhere on the page (bottom of page preferred):
 *    <script src="/path/to/gs-coingecko-widget.js"></script>
 *
 *    Or paste the entire script inside a <script> tag in an Elementor HTML widget.
 */

(function () {
  'use strict';

  // ── CoinGecko symbol-to-ID mapping ──────────────────────────────
  // Add new assets here as needed.
  // Find IDs at https://www.coingecko.com/ (the URL slug is the ID).
  const SYMBOL_MAP = {
    ETH:    'ethereum',
    OSETH:  'stakewise-staked-eth',
    BNB:    'binancecoin',
    SOL:    'solana',
    ADA:    'cardano',
    TRX:    'tron',
    BERA:   'berachain-bera',
    AVAX:   'avalanche-2',
    TON:    'the-open-network',
    DOT:    'polkadot',
    KSM:    'kusama',
    VARA:   'vara-network',
    CFG:    'centrifuge',
    POLYX:  'polymesh',
    AVAIL:  'avail',
    ATOM:   'cosmos',
    ARCH:   'archway',
    NEAR:   'near',
    SUI:    'sui',
    APT:    'aptos',
    XTZ:    'tezos',
    ALGO:   'algorand',
    S:      'sonic-svm',
    EGLD:   'multiversx-egold',
    ONE:    'harmony',
    ZIL:    'zilliqa',
    ICX:    'icon',
    SEI:    'sei-network',
    LYX:    'lukso-token-2',
    TAO:    'bittensor',
    BABY:   'babylon',
    AR:     'arweave',
    FIL:    'filecoin',
    WAL:    'walrus-2',
    PLUME:  'plume-network',
  };

  // ── Inject minimal styles ───────────────────────────────────────
  const style = document.createElement('style');
  style.textContent = `
    [data-gs-price], [data-gs-change] {
      transition: opacity 0.3s ease;
    }
    .gs-loading { opacity: 0.4; }
    .gs-price { font-weight: 600; }
    .gs-change { font-size: 0.9em; margin-left: 0.3em; }
    .gs-change-up { color: #22c55e; }
    .gs-change-down { color: #ef4444; }
    .gs-error { color: #888; }
  `;
  document.head.appendChild(style);

  // ── Collect all symbols used on the page ────────────────────────
  function collectSymbols() {
    const symbols = new Set();
    document.querySelectorAll('[data-gs-price]').forEach(el => {
      symbols.add(el.getAttribute('data-gs-price').toUpperCase());
    });
    document.querySelectorAll('[data-gs-change]').forEach(el => {
      symbols.add(el.getAttribute('data-gs-change').toUpperCase());
    });
    return symbols;
  }

  // ── Format price ────────────────────────────────────────────────
  function formatPrice(price) {
    if (price < 0.01) return '$' + price.toFixed(6);
    if (price < 1) return '$' + price.toFixed(4);
    return '$' + price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // ── Format 24h change ──────────────────────────────────────────
  function formatChange(change) {
    const sign = change >= 0 ? '+' : '';
    return sign + change.toFixed(2) + '%';
  }

  // ── Show loading state ─────────────────────────────────────────
  function setLoading(show) {
    document.querySelectorAll('[data-gs-price], [data-gs-change]').forEach(el => {
      if (show) {
        el.textContent = '--';
        el.classList.add('gs-loading');
      } else {
        el.classList.remove('gs-loading');
      }
    });
  }

  // ── Fetch prices and populate elements ─────────────────────────
  async function fetchAndRender() {
    const symbols = collectSymbols();
    if (symbols.size === 0) return;

    // Resolve symbols to CoinGecko IDs
    const idToSymbol = {};
    const ids = [];
    symbols.forEach(sym => {
      const cgId = SYMBOL_MAP[sym];
      if (cgId) {
        ids.push(cgId);
        idToSymbol[cgId] = sym;
      }
    });

    if (ids.length === 0) return;

    setLoading(true);

    try {
      const url = 'https://api.coingecko.com/api/v3/simple/price?ids='
        + ids.join(',')
        + '&vs_currencies=usd&include_24hr_change=true';

      const res = await fetch(url);
      if (!res.ok) throw new Error('CoinGecko API error: ' + res.status);
      const data = await res.json();

      // Build symbol-keyed lookup
      const prices = {};
      for (const [cgId, sym] of Object.entries(idToSymbol)) {
        if (data[cgId]) {
          prices[sym] = {
            price: data[cgId].usd,
            change: data[cgId].usd_24h_change,
          };
        }
      }

      // Populate price elements
      document.querySelectorAll('[data-gs-price]').forEach(el => {
        const sym = el.getAttribute('data-gs-price').toUpperCase();
        if (prices[sym] && prices[sym].price != null) {
          el.textContent = formatPrice(prices[sym].price);
          el.classList.add('gs-price');
        } else {
          el.textContent = '--';
          el.classList.add('gs-error');
        }
      });

      // Populate change elements
      document.querySelectorAll('[data-gs-change]').forEach(el => {
        const sym = el.getAttribute('data-gs-change').toUpperCase();
        if (prices[sym] && prices[sym].change != null) {
          const change = prices[sym].change;
          el.textContent = formatChange(change);
          el.className = 'gs-change ' + (change >= 0 ? 'gs-change-up' : 'gs-change-down');
        } else {
          el.textContent = '--';
          el.classList.add('gs-error');
        }
      });

    } catch (err) {
      console.error('[GlobalStake Prices]', err.message);
      document.querySelectorAll('[data-gs-price], [data-gs-change]').forEach(el => {
        el.textContent = '--';
        el.classList.add('gs-error');
      });
    } finally {
      setLoading(false);
    }
  }

  // ── Initialize ─────────────────────────────────────────────────
  // Run on DOM ready, then refresh every 15 minutes
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fetchAndRender);
  } else {
    fetchAndRender();
  }

  setInterval(fetchAndRender, 15 * 60 * 1000);

})();
