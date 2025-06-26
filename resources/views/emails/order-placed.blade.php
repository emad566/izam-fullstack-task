<x-mail::message>
# New Order Placed! 📦

Hello Admin,

A new order has been placed on your platform. Here are the details:

## Order Information
- **Order Number:** #{{ $order->order_number }}
- **Status:** {{ ucfirst($order->status->value) }}
- **Total Amount:** ${{ number_format($order->total_amount, 2) }}
- **Order Date:** {{ $order->created_at->format('F j, Y \a\t g:i A') }}

## Customer Information
- **Name:** {{ $user->name }}
- **Email:** {{ $user->email }}

## Order Items

<x-mail::table>
| Product | Quantity | Unit Price | Total |
|:--------|:--------:|:----------:|------:|
@foreach($orderProducts as $orderProduct)
| {{ $orderProduct->product->name }} | {{ $orderProduct->quantity }} | ${{ number_format($orderProduct->unit_price, 2) }} | ${{ number_format($orderProduct->total_price, 2) }} |
@endforeach
| | | **Grand Total:** | **${{ number_format($totalAmount, 2) }}** |
</x-mail::table>

@if($order->notes)
## Additional Notes
{{ $order->notes }}
@endif

<x-mail::button :url="config('app.url') . '/admin/orders/' . $order->id">
View Order Details
</x-mail::button>

Best regards,<br>
{{ config('app.name') }} System
</x-mail::message>
