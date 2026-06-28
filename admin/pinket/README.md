# Pinket Integration System

## 📋 Overview

This system provides complete integration with Pinket delivery service API. It handles both full order data and minimal status updates as required by different Pinket endpoints.

## 🎯 API Endpoints Supported

### ✅ Full Order Data Required:
- **Order Creation** (`POST /orders/create`) - Complete order submission
- **Order Cancellation** (`POST /orders/create`) - Cancel orders  
- **Order Update** (`POST /orders/{order-code}/update`) - Update order details

### ✅ Minimal Data Required:
- **Status Query** (`GET /orders/{order-code}`) - Only status + final shopping list
- **Status Webhook** (`POST to Pinket`) - Only status updates

## 📁 Files Structure

```
admin/pinket/
├── pinket-config.php          # Configuration settings
├── order-manager.php          # Main order management class
├── order-status-query.php     # Status query endpoint
├── status-webhook.php         # Status update webhook
├── helpers.php                # Helper functions
└── README.md                  # This file
```

## 🔧 Configuration

Edit `pinket-config.php` to set your Pinket API credentials:

```php
define('PINKET_BASE_URL', 'https://api.pinket.com'); // Your Pinket API URL
define('PINKET_STORE_KEY', 'your-store-key');        // Your store key
define('PINKET_AUTH_TOKEN', 'your-auth-token');      // Your auth token
define('PINKET_WEBHOOK_TOKEN', 'your-webhook-token'); // Your webhook token
```

## 🚀 Usage Examples

### 1. Create New Order (Full Data)
```php
require_once 'admin/pinket/order-manager.php';
$manager = new PinketOrderManager($mysqli);
$result = $manager->createOrder($orderId);
```

### 2. Update Order Status (Minimal Data)
```php
require_once 'admin/pinket/order-manager.php';
$manager = new PinketOrderManager($mysqli);
$result = $manager->updateOrderStatus($orderId);
```

### 3. Cancel Order (Full Data)
```php
require_once 'admin/pinket/order-manager.php';
$manager = new PinketOrderManager($mysqli);
$result = $manager->cancelOrder($orderId);
```

### 4. Update Order Details (Full Data)
```php
require_once 'admin/pinket/order-manager.php';
$manager = new PinketOrderManager($mysqli);
$result = $manager->updateOrder($orderId);
```

## 🌐 API Endpoints

### Status Query Endpoint
**URL:** `GET /admin/pinket/order-status-query.php?order-code={code}`

**Response:**
```json
{
    "status": "Accepted",
    "shoppingList": [
        {
            "itemId": "0011000",
            "name": "Product Name 1000 گرمی",
            "quantity": 2,
            "unitPrice": 50000,
            "totalPrice": 100000
        }
    ],
    "totalPrice": 100000
}
```

### Order Manager Endpoint
**URL:** `POST /admin/pinket/order-manager.php`

**Request Body:**
```json
{
    "action": "create|cancel|update|status",
    "orderId": 123
}
```

### Status Webhook Endpoint
**URL:** `POST /admin/pinket/status-webhook.php`

**Request Body:**
```json
{
    "orderId": 123
}
```

## 📊 Order Status Mapping

| Internal Status | Pinket Status | Description |
|----------------|---------------|-------------|
| 0 | New | سفارش جدید |
| 1 | Accepted | تایید شده |
| 2 | Preparing | در حال آماده سازی |
| 3 | TransferToDriver | تحویل پیک |
| 4 | Delivered | تحویل شده |
| 5 | Rejected | رد شده |
| 6 | NFC | نیاز به تماس |
| 7 | Cancel | کنسل شده |

## 🔄 Data Flow

### Order Creation Flow:
1. Customer places order → `unified_id` generated
2. Order stored in database with `unified_id`
3. `PinketOrderManager::createOrder()` called
4. Full order data sent to Pinket via API
5. Pinket responds with success/failure

### Status Update Flow:
1. Admin changes order status in system
2. `PinketOrderManager::updateOrderStatus()` called
3. Only status sent to Pinket via webhook
4. Pinket acknowledges status update

### Status Query Flow:
1. Pinket requests order status
2. `order-status-query.php` endpoint called
3. Returns minimal data: status + final shopping list
4. Used for reconciliation and tracking

## 🛠 Integration Points

### In Order Management Pages:
```php
// After order status change
require_once 'admin/pinket/order-manager.php';
$manager = new PinketOrderManager($mysqli);
$manager->updateOrderStatus($orderId);
```

### In Order Creation Process:
```php
// After successful order creation
require_once 'admin/pinket/order-manager.php';
$manager = new PinketOrderManager($mysqli);
$manager->createOrder($orderId);
```

## 📝 Logging

All API interactions are logged to `pinket-api.log`:

```php
logPinket("Order $orderId created successfully", "INFO");
logPinket("API call failed: " . $error, "ERROR");
```

## 🔒 Security

- All API tokens stored in configuration file
- HTTPS required for all API calls
- Input validation on all endpoints
- Error handling with appropriate HTTP status codes

## 🚨 Error Handling

The system handles various error scenarios:

- **400 Bad Request**: Invalid input data
- **404 Not Found**: Order not found
- **500 Internal Server Error**: Server/database errors
- **API Errors**: Network/authentication failures

## 📈 Future Enhancements

- Retry mechanism for failed API calls
- Webhook signature verification
- Real-time status synchronization
- Bulk order operations
- Advanced error reporting

## 🤝 Support

For issues or questions about the Pinket integration:

1. Check the log file: `admin/pinket/pinket-api.log`
2. Verify configuration in `pinket-config.php`
3. Test individual endpoints
4. Contact Pinket support for API issues 