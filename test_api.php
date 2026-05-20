<?php
/**
 * HTTP test script — simulates a browser making API calls to the Laravel server
 */

function apiRequest(string $method, string $path, array $data = [], string $token = ''): array
{
    $url = "http://127.0.0.1:8000" . $path;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_filter([
        'Content-Type: application/json',
        'Accept: application/json',
        $token ? "Authorization: Bearer $token" : null,
    ]));
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($err) return ['error' => $err, 'code' => 0];
    return ['code' => $code, 'body' => json_decode($body, true), 'raw' => $body];
}

echo "\n=== MindBloom API Test Suite ===\n\n";

// 1. Health
$r = apiRequest('GET', '/api/health');
echo "[{$r['code']}] Health: " . ($r['body']['status'] ?? 'FAIL') . "\n";

// 2. Login
$r = apiRequest('POST', '/api/auth/login', ['email' => 'aarav@mindbloom.ai', 'password' => 'password123']);
echo "[{$r['code']}] Login: " . ($r['body']['message'] ?? ($r['body']['error'] ?? 'FAIL')) . "\n";
if ($r['code'] !== 200) {
    echo "LOGIN FAILED. Raw: " . substr($r['raw'], 0, 200) . "\n";
    exit(1);
}
$token = $r['body']['token'];
$user  = $r['body']['user'];
echo "  -> User: {$user['name']} | Role: {$user['role']} | Hero: " . ($user['profile']['hero_name'] ?? 'N/A') . "\n";

// 3. Dashboard
$r = apiRequest('GET', '/api/dashboard', [], $token);
echo "[{$r['code']}] Dashboard: Level " . ($r['body']['child']['level'] ?? 'N/A') . ", XP: " . ($r['body']['child']['xp'] ?? 'N/A') . "\n";
echo "  -> Skills: " . count($r['body']['skill_scores'] ?? []) . " categories | Tips: " . count($r['body']['ai_tips'] ?? []) . "\n";

// 4. Quizzes
$r = apiRequest('GET', '/api/quizzes', [], $token);
echo "[{$r['code']}] Quizzes: " . count($r['body']['quizzes'] ?? []) . " quizzes available\n";
$firstQuiz = $r['body']['quizzes'][0] ?? null;

// 5. Single Quiz
if ($firstQuiz) {
    $r2 = apiRequest('GET', '/api/quizzes/' . $firstQuiz['id'], [], $token);
    $qCount = count($r2['body']['quiz']['questions'] ?? []);
    echo "[{$r2['code']}] Quiz '{$firstQuiz['title']}': $qCount questions\n";
}

// 6. Missions Today
$r = apiRequest('GET', '/api/missions/today', [], $token);
echo "[{$r['code']}] Missions Today: " . ($r['body']['total'] ?? 'N/A') . " assigned, " . ($r['body']['completed'] ?? 0) . " completed\n";

// 7. Achievements
$r = apiRequest('GET', '/api/achievements', [], $token);
echo "[{$r['code']}] Achievements: " . ($r['body']['unlocked_count'] ?? 0) . "/" . ($r['body']['total_count'] ?? 0) . " unlocked\n";

// 8. Skills
$r = apiRequest('GET', '/api/skills', [], $token);
echo "[{$r['code']}] Skills: " . count($r['body']['radar_data'] ?? []) . " categories in radar\n";

// 9. Report
$r = apiRequest('GET', '/api/reports/latest', [], $token);
echo "[{$r['code']}] Report: " . ($r['body']['report'] ? 'Has existing report' : 'No report yet') . "\n";

// 10. Parent Login
$r = apiRequest('POST', '/api/auth/login', ['email' => 'priya@mindbloom.ai', 'password' => 'password123']);
echo "[{$r['code']}] Parent Login: " . ($r['body']['message'] ?? 'FAIL') . "\n";
if ($r['code'] === 200) {
    $parentToken = $r['body']['token'];
    $r2 = apiRequest('GET', '/api/parent/dashboard', [], $parentToken);
    echo "[{$r2['code']}] Parent Dashboard: " . count($r2['body']['children'] ?? []) . " children tracked\n";
}

echo "\n=== ALL TESTS COMPLETE ===\n";
