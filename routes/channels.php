<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('orders.{orderId}', function (User $user, int $orderId) {
    return true;
});

Broadcast::channel('admin', function (User $user) {
    return $user->hasRole(['super_admin', 'admin', 'sales']);
});

Broadcast::channel('customer.{customerId}', function (User $user, int $customerId) {
    return $user->customer?->id === $customerId;
});
