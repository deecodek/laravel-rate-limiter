<?php

namespace Deecodek\RateLimiter\Services;

use Deecodek\RateLimiter\Models\RateLimitAlert;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class AlertService
{
    public function sendAlert($type, $channel, $payload)
    {
        $alert = RateLimitAlert::create([
            'type' => $type,
            'channel' => $channel,
            'payload' => $payload,
            'status' => 'pending',
        ]);
        
        try {
            switch ($channel) {
                case 'mail':
                    $this->sendMailAlert($payload);
                    break;
                case 'slack':
                    $this->sendSlackAlert($payload);
                    break;
                case 'webhook':
                    $this->sendWebhookAlert($payload);
                    break;
            }
            
            $alert->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
            
        } catch (\Exception $e) {
            $alert->update([
                'status' => 'failed',
                'payload' => array_merge($payload, ['error' => $e->getMessage()]),
            ]);
        }
    }
    
    protected function sendMailAlert($payload)
    {
        $to = config('rate-limiter.alerts.channels.mail.to');
        if (!$to) return;
        
        // In a real implementation, you'd use Laravel's mail system
        // Mail::to($to)->send(new RateLimitAlertMail($payload));
    }
    
    protected function sendSlackAlert($payload)
    {
        $webhookUrl = config('rate-limiter.alerts.channels.slack.webhook_url');
        if (!$webhookUrl) return;
        
        Http::post($webhookUrl, [
            'text' => $payload['message'] ?? 'Rate limit alert',
        ]);
    }
    
    protected function sendWebhookAlert($payload)
    {
        $url = config('rate-limiter.alerts.channels.webhook.url');
        if (!$url) return;
        
        Http::post($url, $payload);
    }
}