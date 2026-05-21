<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NexaQuest AI — Aptitude Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; background: #fffbf0; color: #1a1a2e; font-size: 12px; }
        /* Header */
        .header { background-color: #4f46e5; color: #ffffff; padding: 24px 28px; }
        .header-emoji { font-size: 44px; float: left; margin-right: 16px; line-height: 1; }
        .header-info { overflow: hidden; }
        .header h1 { font-size: 22px; font-weight: 900; color: #ffffff; margin-bottom: 4px; }
        .header .subtitle { font-size: 11px; color: rgba(255,255,255,0.8); }
        .badge { display: inline-block; background: rgba(255,255,255,0.2); padding: 3px 9px; border-radius: 8px; font-size: 10px; margin: 4px 2px 0 0; color: #ffffff; }
        .clearfix { clear: both; }
        /* Content */
        .content { padding: 18px 22px; }
        /* Sections */
        .section { background: #ffffff; border-radius: 6px; padding: 16px; margin-bottom: 14px; border: 1px solid #e5e7eb; }
        .section-title { font-size: 13px; font-weight: 800; color: #4f46e5; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 2px solid #e5e7eb; }
        /* Summary */
        .summary-box { background: #fef9c3; border-left: 4px solid #facc15; padding: 12px 14px; border-radius: 4px; line-height: 1.7; font-size: 12px; }
        /* Stats row (3 cols using table) */
        .stats-table { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin-top: 12px; }
        .stat-cell { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 6px; padding: 10px; text-align: center; width: 33%; }
        .stat-label { font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: 700; }
        .stat-value { font-size: 14px; font-weight: 900; color: #1e293b; margin-top: 3px; }
        /* Skill bars */
        .skill-row { margin-bottom: 9px; }
        .skill-name { font-weight: 700; font-size: 11px; margin-bottom: 3px; }
        .skill-pct { float: right; font-weight: 700; font-size: 11px; }
        .skill-track { height: 9px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
        .skill-fill { height: 9px; background: #4f46e5; border-radius: 4px; }
        /* Two column layout */
        .two-col-table { width: 100%; border-collapse: separate; border-spacing: 10px 0; margin-bottom: 14px; }
        .two-col-cell { width: 50%; vertical-align: top; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 14px; }
        .two-col-cell.green { background: #f0fdf4; border-color: #bbf7d0; }
        .two-col-cell.orange { background: #fff7ed; border-color: #fed7aa; }
        /* List items */
        .list-item { background: #f8fafc; border-left: 3px solid #4f46e5; padding: 8px 10px; margin-bottom: 6px; border-radius: 3px; font-size: 11px; line-height: 1.5; }
        .list-item.orange { border-left-color: #f97316; }
        .list-item.green { border-left-color: #22c55e; }
        /* Career cards */
        .career-card { background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 6px; padding: 12px; margin-bottom: 10px; }
        .career-header { margin-bottom: 5px; }
        .career-emoji { font-size: 20px; float: left; margin-right: 8px; line-height: 1.2; }
        .career-title { font-size: 13px; font-weight: 800; line-height: 1.3; }
        .career-match { float: right; font-size: 12px; font-weight: 900; color: #16a34a; }
        .career-reason { font-size: 11px; color: #475569; margin-top: 5px; line-height: 1.5; }
        /* Footer */
        .footer { text-align: center; color: #94a3b8; font-size: 10px; padding: 14px 20px; border-top: 1px solid #e2e8f0; margin-top: 6px; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <div class="header-emoji">{{ $child->avatar_emoji ?? '🦊' }}</div>
        <div class="header-info">
            <h1>{{ $child->hero_name ?? $child->user->name }} — Aptitude Report</h1>
            <div class="subtitle">NexaQuest AI · Generated {{ $report->report_date->format('F j, Y') }}</div>
            <div style="margin-top: 8px;">
                <span class="badge">Level {{ $child->level }}</span>
                <span class="badge">{{ $child->xp }} XP</span>
                <span class="badge">{{ $child->streak_count }} Day Streak</span>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="content">

        {{-- AI SUMMARY --}}
        <div class="section">
            <div class="section-title">🤖 AI Summary</div>
            <div class="summary-box">{{ $report->summary }}</div>
            <table class="stats-table">
                <tr>
                    <td class="stat-cell">
                        <div class="stat-label">Top Strength</div>
                        <div class="stat-value">{{ $report->top_strength ?? '—' }}</div>
                    </td>
                    <td class="stat-cell">
                        <div class="stat-label">Learning Style</div>
                        <div class="stat-value" style="font-size:11px;">{{ $report->learning_style ?? '—' }}</div>
                    </td>
                    <td class="stat-cell">
                        <div class="stat-label">Personality</div>
                        <div class="stat-value" style="font-size:11px;">{{ $report->personality_type ?? '—' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- SKILL BREAKDOWN --}}
        @if($report->skill_scores_snapshot)
        <div class="section">
            <div class="section-title">⚡ Skill Breakdown</div>
            @foreach($report->skill_scores_snapshot as $skill)
            <div class="skill-row">
                <div class="skill-name">
                    <span class="skill-pct">{{ $skill['score'] }}%</span>
                    {{ $skill['category'] }}
                </div>
                <div class="clearfix"></div>
                <div class="skill-track">
                    <div class="skill-fill" style="width: {{ $skill['score'] }}%;"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- STRENGTHS & WEAKNESSES (2-col table) --}}
        <table class="two-col-table">
            <tr>
                <td class="two-col-cell green">
                    <div class="section-title" style="color:#16a34a; border-color:#bbf7d0;">💪 Strengths</div>
                    @foreach($report->strengths_json ?? [] as $s)
                    <div class="list-item green">✅ {{ $s }}</div>
                    @endforeach
                </td>
                <td class="two-col-cell orange">
                    <div class="section-title" style="color:#ea580c; border-color:#fed7aa;">📈 Growth Areas</div>
                    @foreach($report->weaknesses_json ?? [] as $w)
                    <div class="list-item orange">🎯 {{ $w }}</div>
                    @endforeach
                </td>
            </tr>
        </table>

        {{-- RECOMMENDATIONS --}}
        @if(!empty($report->recommendations_json))
        <div class="section">
            <div class="section-title">🚀 Recommendations</div>
            @foreach($report->recommendations_json as $r)
            <div class="list-item green">💡 {{ $r }}</div>
            @endforeach
        </div>
        @endif

        {{-- CAREER RECOMMENDATIONS --}}
        @if($careers->count() > 0)
        <div class="section">
            <div class="section-title">🎯 Future Career Matches</div>
            @foreach($careers as $career)
            <div class="career-card">
                <div class="career-header">
                    <span class="career-match">{{ $career->match_percentage }}% Match</span>
                    <span class="career-emoji">{{ $career->career_emoji }}</span>
                    <span class="career-title">{{ $career->career_title }}</span>
                    <div class="clearfix"></div>
                </div>
                <div class="career-reason">{{ $career->ai_reasoning }}</div>
            </div>
            @endforeach
        </div>
        @endif

    </div>

    <div class="footer">
        Generated by NexaQuest AI 🌸 &middot; Powered by Gemini AI &middot; {{ now()->format('Y') }}
    </div>

</body>
</html>
