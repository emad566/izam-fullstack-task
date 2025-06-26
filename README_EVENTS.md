# Order Events and Email Notifications

This document describes the event-driven email notification system implemented for order placement.

## System Overview

When a new order is placed, an event-driven system automatically sends email notifications to all administrators. This system is built using Laravel's event/listener architecture and provides reliable, scalable notification delivery.

## Components

### 1. OrderPlaced Event (`app/Events/OrderPlaced.php`)
- Fired when a new order is successfully created
- Contains the order and user data
- Implements Laravel's event broadcasting interfaces

### 2. SendOrderNotificationToAdmin Listener (`app/Listeners/SendOrderNotificationToAdmin.php`)
- Handles the OrderPlaced event
- Sends email notifications to all administrators
- Implements `ShouldQueue` for background processing
- Includes error handling and logging for failed email deliveries

### 3. OrderPlacedNotification Mailable (`app/Mail/OrderPlacedNotification.php`)
- Formats and structures the email content
- Uses Markdown templates for professional email appearance
- Includes order details, customer information, and product listings

### 4. Email Template (`resources/views/emails/order-placed.blade.php`)
- Professional Markdown-based email template
- Displays order information, customer details, and itemized product list
- Includes call-to-action button to view order details

## Features

### Automatic Email Delivery
- Emails are sent automatically when orders are placed
- All active administrators receive notifications
- No manual intervention required

### Comprehensive Order Information
- Order number and status
- Customer name and email
- Complete product listing with quantities and prices
- Order total and timestamps
- Optional order notes

### Background Processing
- Emails are queued for background processing
- Prevents delays in order creation process
- Ensures reliable delivery even during high traffic

### Error Handling
- Failed email deliveries are logged
- System continues operating even if email delivery fails
- Graceful degradation prevents order creation from failing

### Professional Email Design
- Clean, responsive Markdown-based template
- Branded with application name
- Clear call-to-action for admin follow-up

## Configuration

### Event Registration
Events and listeners are registered in `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    OrderPlaced::class => [
        SendOrderNotificationToAdmin::class,
    ],
];
```

### Email Configuration
Configure your email settings in `.env`:

```bash
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Usage

### Automatic Triggering
The event is automatically fired when orders are created through:
- User order creation via `POST /api/user/orders`
- The event fires after successful order creation and stock updates

### Manual Testing
Test the email notification system using the provided artisan command:

```bash
# Send test email to first admin in database
php artisan email:test-order-notification

# Send test email to specific admin
php artisan email:test-order-notification admin@example.com
```

## Implementation Details

### Order Creation Flow
1. User submits order via API
2. Order validation occurs
3. Stock availability is checked
4. Order and order products are created
5. Product stock is decremented
6. **OrderPlaced event is fired**
7. SendOrderNotificationToAdmin listener processes the event
8. Emails are sent to all administrators
9. Success response is returned to user

### Event Data
The OrderPlaced event includes:
- **$order**: Complete order model with relationships
- **$user**: Customer who placed the order

### Email Content
Each notification email includes:
- Order number and status
- Customer information (name, email)
- Complete product listing
- Individual and total pricing
- Order date and time
- Optional order notes
- Direct link to view order details

## Testing

Comprehensive test coverage is provided in `tests/Feature/OrderEventTest.php`:

- ✅ Event firing verification
- ✅ Event data validation
- ✅ Email delivery to administrators
- ✅ Email content accuracy
- ✅ Subject line formatting
- ✅ Error handling
- ✅ Multiple order handling

Run tests with:
```bash
php artisan test tests/Feature/OrderEventTest.php
```

## File Structure

```
app/
├── Events/
│   └── OrderPlaced.php
├── Listeners/
│   └── SendOrderNotificationToAdmin.php
├── Mail/
│   └── OrderPlacedNotification.php
├── Console/Commands/
│   └── TestOrderNotificationEmail.php
└── Providers/
    └── EventServiceProvider.php

resources/views/emails/
└── order-placed.blade.php

tests/Feature/
└── OrderEventTest.php
```

## Benefits

1. **Immediate Notifications**: Admins are notified instantly when orders are placed
2. **Scalable**: System handles multiple administrators automatically
3. **Reliable**: Background processing and error handling ensure delivery
4. **Professional**: Clean, branded email templates
5. **Maintainable**: Clear separation of concerns with event-driven architecture
6. **Testable**: Comprehensive test coverage ensures reliability

## Future Enhancements

- Order status change notifications
- Customer email confirmations
- SMS notifications for high-value orders
- Email template customization
- Notification preferences per administrator 
