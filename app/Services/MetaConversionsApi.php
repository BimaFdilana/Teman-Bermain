<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Conversions API (server-side) untuk dataset Meta Pixel "Rumah Cahaya".
 *
 * Dipakai untuk Purchase saat admin menyetujui order — satu-satunya titik
 * Purchase yang akurat, karena pembelian baru sah setelah verifikasi admin
 * (event browser berhenti di InitiateCheckout; tidak ada Purchase browser).
 * Tanpa token CAPI, pengiriman dilewati diam-diam dan approval tetap jalan.
 * Kegagalan kirim tidak pernah menggagalkan approval (try/catch di caller
 * + di sini); event_id stabil ("purchase-<kode_order>") membuat kirim
 * ulang idempotent di sisi Meta.
 */
class MetaConversionsApi
{
    public function purchase(Order $order): bool
    {
        $pixelId = (string) config('meta.pixel_id');
        $token = (string) config('meta.capi_token');

        if ($pixelId === '' || $token === '') {
            Log::info('Meta CAPI dilewati: pixel ID / token belum dikonfigurasi.', [
                'kode_order' => $order->kode_order,
            ]);

            return false;
        }

        $order->loadMissing(['items', 'user']);

        $contentIds = $order->items->isNotEmpty()
            ? $order->items->pluck('product_id')->map(fn ($id) => (string) $id)->all()
            : ($order->product_id ? [(string) $order->product_id] : []);

        $userData = [];
        if ($email = $order->emailPembeli()) {
            $userData['em'] = hash('sha256', strtolower(trim($email)));
        }
        if ($wa = $this->normalisasiWa($order->waPembeli())) {
            $userData['ph'] = hash('sha256', $wa);
        }

        $payload = [
            'event_name' => 'Purchase',
            'event_time' => time(),
            'event_id' => 'purchase-'.$order->kode_order,
            'action_source' => 'website',
            'event_source_url' => route('checkout.terima-kasih', $order->kode_order),
            'user_data' => $userData,
            'custom_data' => [
                'value' => (float) $order->total_bayar,
                'currency' => 'IDR',
                'content_ids' => $contentIds,
                'content_type' => 'product',
                'order_id' => $order->kode_order,
            ],
        ];

        $body = ['data' => [$payload]];
        if ($testCode = config('meta.test_event_code')) {
            $body['test_event_code'] = $testCode;
        }

        try {
            $response = Http::timeout(5)->post(
                'https://graph.facebook.com/'.config('meta.capi_version').'/'.$pixelId.'/events',
                array_merge($body, ['access_token' => $token])
            );

            if (! $response->successful()) {
                Log::warning('Meta CAPI Purchase gagal.', [
                    'kode_order' => $order->kode_order,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Meta CAPI Purchase error: '.$e->getMessage(), [
                'kode_order' => $order->kode_order,
            ]);

            return false;
        }
    }

    /** Normalisasi nomor WA ke digit internasional (08… → 62…). */
    private function normalisasiWa(?string $wa): ?string
    {
        if (! $wa) {
            return null;
        }

        $digit = preg_replace('/\D+/', '', $wa) ?? '';
        if ($digit === '') {
            return null;
        }
        if (str_starts_with($digit, '0')) {
            $digit = '62'.substr($digit, 1);
        }

        return $digit;
    }
}
