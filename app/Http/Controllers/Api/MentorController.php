<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class MentorController extends Controller
{
    public function __construct(private GeminiService $gemini) {}

    public function conversations()
    {
        $child = auth('api')->user()->childProfile;
        if (!$child) return response()->json(['conversations' => []]);

        $convs = ChatConversation::where('child_profile_id', $child->id)
            ->orderByDesc('updated_at')
            ->withCount('messages')
            ->get();

        return response()->json(['conversations' => $convs]);
    }

    public function createConversation(Request $request)
    {
        $child = auth('api')->user()->childProfile;
        if (!$child) return response()->json(['message' => 'Profile not found'], 403);

        $conv = ChatConversation::create([
            'child_profile_id' => $child->id,
            'title'            => $request->title ?? "Chat with Bloomy 🤖",
        ]);

        // Add initial greeting from Bloomy
        $greeting = "Hi {$child->hero_name}! 🌟 I'm Bloomy, your AI mentor. Ready for today's quest? Ask me anything! 🚀";
        ChatMessage::create([
            'conversation_id' => $conv->id,
            'role'            => 'assistant',
            'content'         => $greeting,
        ]);

        return response()->json(['conversation' => $conv, 'greeting' => $greeting], 201);
    }

    public function deleteConversation(int $conversationId)
    {
        $child = auth('api')->user()->childProfile;
        $conv = ChatConversation::where('id', $conversationId)
            ->where('child_profile_id', $child->id)->firstOrFail();

        $conv->delete();

        return response()->json(['message' => 'Conversation deleted successfully']);
    }

    public function messages(int $conversationId)
    {
        $child = auth('api')->user()->childProfile;
        $conv = ChatConversation::where('id', $conversationId)
            ->where('child_profile_id', $child->id)->firstOrFail();

        return response()->json(['messages' => $conv->messages]);
    }

    public function sendMessage(Request $request, int $conversationId)
    {
        $child = auth('api')->user()->childProfile;
        $conv = ChatConversation::where('id', $conversationId)
            ->where('child_profile_id', $child->id)->firstOrFail();

        $request->validate(['message' => 'required|string|max:500']);

        // Rate limiting: 20 messages per minute per user
        $key = 'mentor_chat_' . $child->id;
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json(['message' => '⏳ Slow down! Bloomy needs a moment to think. Try again in a minute!'], 429);
        }
        RateLimiter::hit($key, 60);

        // Save user message
        ChatMessage::create([
            'conversation_id' => $conv->id,
            'role'            => 'user',
            'content'         => $request->message,
        ]);

        // Build history for context (last 10 messages)
        $history = $conv->messages()->orderByDesc('created_at')->take(10)->get()->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])->values()->toArray();

        // Get top skills
        $topSkills = $child->skillScores()->orderByDesc('score')->take(3)->pluck('category')->toArray();
        if (empty($topSkills)) $topSkills = ['Logic', 'Creativity'];

        // Get AI response
        $aiResponse = $this->gemini->chat(
            $child->hero_name ?? $child->user->name,
            $child->level,
            $topSkills,
            $history,
            $request->message
        );

        // Save AI response
        $aiMessage = ChatMessage::create([
            'conversation_id' => $conv->id,
            'role'            => 'assistant',
            'content'         => $aiResponse,
        ]);

        $conv->touch();

        return response()->json([
            'user_message' => $request->message,
            'ai_response'  => $aiResponse,
            'message_id'   => $aiMessage->id,
        ]);
    }
}
