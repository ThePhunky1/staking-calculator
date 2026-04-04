OPTION B — Client-Side JavaScript Widget
=========================================

WHAT IT DOES
------------
A standalone JavaScript file that fetches live prices from CoinGecko
directly in the browser. No plugin upload needed — just paste it into
your page. Prices auto-refresh every 15 minutes.

INSTALLATION
------------
Two ways to add the script:

METHOD 1 — Elementor HTML Widget (easiest):
  1. Edit the Supported Assets page in Elementor.
  2. Drag an "HTML" widget to the bottom of the page.
  3. Paste the contents of gs-coingecko-widget.js inside <script> tags:
     <script>
       // paste entire contents of gs-coingecko-widget.js here
     </script>

METHOD 2 — Theme header/footer (site-wide):
  1. Install the "Insert Headers and Footers" plugin (or use
     Appearance > Theme File Editor > footer.php).
  2. Add before </body>:
     <script src="/wp-content/uploads/gs-coingecko-widget.js"></script>
     (upload the JS file to your Media Library first)

REPLACING CRYPTOWP SHORTCODES
------------------------------
In each Elementor table cell, replace CryptoWP shortcodes with
HTML data attributes:

  OLD (CryptoWP):
    [crypto id="1" type="price"]
    [crypto id="1" type="percent"]

  NEW (this widget):
    <span data-gs-price="ETH"></span>
    <span data-gs-change="ETH"></span>

IMPORTANT: Since Elementor renders shortcodes but also supports
raw HTML in text editors, you may need to switch the cell to
"HTML" mode (the </> icon in the Elementor text editor) to paste
the data-attribute spans.

EXAMPLE TABLE CELL
------------------
For Solana:
  <span data-gs-price="SOL"></span> <span data-gs-change="SOL"></span>

This renders as:  $148.50 +2.35%

SUPPORTED SYMBOLS
-----------------
ETH, osETH, BNB, SOL, ADA, TRX, BERA, AVAX, TON, DOT, KSM, VARA,
CFG, POLYX, AVAIL, ATOM, ARCH, NEAR, SUI, APT, XTZ, ALGO, S, EGLD,
ONE, ZIL, ICX, SEI, LYX, TAO, BABY, AR, FIL, WAL, PLUME

To add a new token, edit the SYMBOL_MAP object in the JS file.
Find CoinGecko IDs at: https://www.coingecko.com/ (the URL slug).

PROS OF THIS OPTION
-------------------
- No plugin upload or server access required
- Can be added by anyone with Elementor edit access
- Auto-refreshes every 15 minutes in the browser
- Zero server load — all API calls happen client-side
- Easy to test: just open an HTML file locally

CONS
----
- Prices flash from "--" to value on page load (brief loading state)
- Subject to CoinGecko rate limits if page gets heavy traffic
  (free tier: ~30 calls/min — fine for normal traffic since it
  batches all tokens in a single API call)
- Not SEO-friendly (prices load via JS, not in initial HTML)
- CORS: CoinGecko's free API generally allows browser requests,
  but if issues arise, Option A (server-side) is the fix.

TESTING LOCALLY
---------------
To test without WordPress, create a quick test.html:

  <!DOCTYPE html>
  <html>
  <body style="font-family: sans-serif; padding: 2rem;">
    <h2>Price Test</h2>
    <p>ETH: <span data-gs-price="ETH"></span> <span data-gs-change="ETH"></span></p>
    <p>SOL: <span data-gs-price="SOL"></span> <span data-gs-change="SOL"></span></p>
    <p>ATOM: <span data-gs-price="ATOM"></span> <span data-gs-change="ATOM"></span></p>
    <script src="gs-coingecko-widget.js"></script>
  </body>
  </html>
