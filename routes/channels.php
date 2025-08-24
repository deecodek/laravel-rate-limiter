<?php

use Illuminate\Support\Facades\Broadcast;

// Webhook channels for alerts
Broadcast::channel('rate-limit-alerts', function ($user) {
    return true; // Adjust based on your auth logic
});