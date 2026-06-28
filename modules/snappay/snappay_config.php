<?php
// SnappPay configuration
// NOTE: Keep this file in sync across environments (staging/prod).

// Checkout visibility for new orders.
define('SNAPPAY_CHECKOUT_ENABLED', false);

// Backend exchange with SnappPay API (verify/settle/status/cancel/update/reconcile/callback).
define('SNAPPAY_BACKEND_ENABLED', false);

// Legacy alias kept for backward compatibility with older checks.
define('SNAPPAY_ENABLED', SNAPPAY_CHECKOUT_ENABLED);

// Logging (JSON lines). Includes masking for mobile/paymentToken/access_token.
define('SNAPPAY_LOG_ENABLED', false);
define('SNAPPAY_LOG_LEVEL', 'INFO'); // DEBUG | INFO | WARN | ERROR
// Optional absolute path; default is repository-root/logs/snappay.json
define('SNAPPAY_LOG_FILE', '');

// Base URL (no trailing slash). Example (staging):
// https://fms-gateway-staging.apps.public.okd4.teh-1.snappcloud.io
define('SNAPPAY_BASE_URL', 'https://example.invalid');

// OAuth credentials (provided by SnappPay).
define('SNAPPAY_CLIENT_ID', 'disabled');
define('SNAPPAY_CLIENT_SECRET', 'not-configured');
define('SNAPPAY_USERNAME', 'disabled');
define('SNAPPAY_PASSWORD', 'not-configured');
define('SNAPPAY_SCOPE', 'online-merchant');

// returnURL must be absolute and domain-whitelisted by SnappPay.
define('SNAPPAY_RETURN_URL', 'http://localhost/cart/snappay_return.php');

// Amount conversion: SnappPay uses IRR (Rial). Your DB uses Toman.
define('SNAPPAY_AMOUNT_MULTIPLIER', 10);

// Eligible API payment methods filter.
// Example: ['INSTALLMENT'] or ['POSTPAID','INSTALLMENT'].
// Leave empty array [] to omit paymentMethodTypes and let SnappPay return all available methods.
define('SNAPPAY_ELIGIBLE_PAYMENT_METHOD_TYPES', []);

// Optional (may require SnappPay-side enablement). Leave empty array to omit.
define('SNAPPAY_FORCED_PAYMENT_METHOD_TYPES', []);

// Cart payload behavior (v1 default).
define('SNAPPAY_IS_TAX_INCLUDED', true);
define('SNAPPAY_TAX_AMOUNT_FIXED_IRR', 0);
define('SNAPPAY_IS_SHIPMENT_INCLUDED', true);

// Optional: callback IP allowlist (leave empty to disable enforcement).
define('SNAPPAY_ALLOWED_CALLBACK_IPS', []);
// If your site is behind a reverse proxy/CDN, set this to true to use X-Forwarded-For for callback IP checks.
// Safer default is false (uses REMOTE_ADDR).
define('SNAPPAY_CALLBACK_IP_USE_XFF', false);

// Claim TTLs for stale in-progress verify/settle recovery in callback races.
define('SNAPPAY_VERIFY_CLAIM_TTL_SECONDS', 120);
define('SNAPPAY_SETTLE_CLAIM_TTL_SECONDS', 120);

// Reconciliation worker controls.
define('SNAPPAY_RECONCILE_ENABLED', false);
define('SNAPPAY_RECONCILE_LOOKBACK_MINUTES', 180);
define('SNAPPAY_RECONCILE_BATCH_SIZE', 200);
define('SNAPPAY_RECONCILE_INTERVAL_SECONDS', 300);

// Admin dashboard health check controls.
define('SNAPPAY_HEALTHCHECK_ENABLED', false);
define('SNAPPAY_HEALTHCHECK_TEST_AMOUNT_IRR', 10000);

// Product feed for Snappay/Searchwise.
// The feed file itself is public, but the web runner is disabled unless this token is set.
// Preferred cron command uses PHP CLI and does not need a token.
define('SNAPPAY_PRODUCT_FEED_CRON_TOKEN', '');

// CommissionType mapping (per your SnappPay contract). If you don't have a mapping, keep default=100.
define('SNAPPAY_COMMISSION_TYPE_DEFAULT', 100);
// Example: ['nuts' => 101, 'dried-fruit' => 102]
define('SNAPPAY_COMMISSION_TYPE_MAP', []);

// Admin state mapping (set these to your `orders.state` values).
// When cancel succeeds, `orders.state` will be set to this value.
// If you run `modules/snappay/add_orders_cancel_state.sql`, set this to:
//   (admin_orders_state.id where flag='cancelled') - 1
define('SNAPPAY_ORDER_STATE_CANCELLED', 5);
