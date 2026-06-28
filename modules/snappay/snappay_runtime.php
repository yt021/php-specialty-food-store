<?php

if (!function_exists('snappay_checkout_enabled')) {
    function snappay_checkout_enabled()
    {
        if (defined('SNAPPAY_CHECKOUT_ENABLED')) {
            return (bool)SNAPPAY_CHECKOUT_ENABLED;
        }
        if (defined('SNAPPAY_ENABLED')) {
            return (bool)SNAPPAY_ENABLED;
        }
        return true;
    }
}

if (!function_exists('snappay_backend_enabled')) {
    function snappay_backend_enabled()
    {
        if (defined('SNAPPAY_BACKEND_ENABLED')) {
            return (bool)SNAPPAY_BACKEND_ENABLED;
        }
        return true;
    }
}

