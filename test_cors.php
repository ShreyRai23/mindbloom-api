<?php
/**
 * Test CORS preflight from different localhost origins
 */

function testCors(string $origin): void
{
    $ch = curl_init("http://127.0.0.1:8000/api/auth/login");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "OPTIONS");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Origin: $origin",
        "Access-Control-Request-Method: POST",
        "Access-Control-Request-Headers: Content-Type, Authorization, Accept",
    ]);
    curl_setopt($ch, CURLOPT_HEADER, true);

    $response = curl_exec($ch);
    $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Extract ACAO header
    preg_match('/Access-Control-Allow-Origin: (.+)/i', $response, $m);
    $acao = trim($m[1] ?? 'NOT SET');

    $ok = ($code === 200 || $code === 204) && $acao !== 'NOT SET';
    echo ($ok ? '✅' : '❌') . " Origin: $origin → HTTP $code | ACAO: $acao\n";
}

echo "\n=== CORS Preflight Tests ===\n\n";
testCors("http://localhost:5173");
testCors("http://localhost:8081");
testCors("http://localhost:3000");
testCors("http://127.0.0.1:8081");
testCors("http://evil.com");   // Should be blocked
echo "\n";
