<?php

return [
    /*
     * Meta Pixel + Conversions API (dataset "Rumah Cahaya").
     * Pixel ID dipakai juga oleh partial meta-pixel-script (PageView).
     * Token CAPI diisi dari Events Manager (Settings → Conversions API →
     * Generate access token) atau token System User; TANPA token, pengiriman
     * server dilewati diam-diam (event browser tetap jalan).
     */
    'pixel_id' => env('META_PIXEL_ID', '1645492726958429'),
    'capi_token' => env('META_CAPI_TOKEN'),
    'capi_version' => env('META_CAPI_VERSION', 'v21.0'),
    // Opsional: kode uji dari tab Test Events (hanya untuk verifikasi).
    'test_event_code' => env('META_TEST_EVENT_CODE'),
];
