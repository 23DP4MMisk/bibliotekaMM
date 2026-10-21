<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Gramata; 
use App\Models\Atsauksmes;
use App\Models\AiSummary;

class ChatController extends Controller
{
    public function askAdvisor(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $apiKey = env('GEMINI_API_KEY');
        
        if (!$apiKey) {
            return response()->json(['error' => 'API atslēga nav konfigurēta.'], 500);
        }

       
        $searchQuery = $request->message;
        
        $books = Gramata::where('nosaukums', 'LIKE', '%' . $searchQuery . '%')
            ->orWhere('autors', 'LIKE', '%' . $searchQuery . '%')
            ->orWhere('apraksts', 'LIKE', '%' . $searchQuery . '%')
            ->limit(10)
            ->get();

        if ($books->isEmpty()) {
           
            $books = Gramata::limit(15)->get();
        }

       
        $bookListText = "";
        foreach ($books as $book) {
            $bookListText .= "- Nosaukums: {$book->nosaukums}, Autors: {$book->autors}. Apraksts: {$book->apraksts}\n";
        }

        if (empty($bookListText)) {
            $bookListText = "Bibliotēka šobrīd ir tukša.";
        }

       
        $systemPrompt = "Tu esi draudzīgs un gudrs MyLibrary virtuālais bibliotekārs. 
        Tavs uzdevums ir ieteikt grāmatas TIKAI no zemāk pievienotā saraksta. 
        Ja lietotājs jautā par tēmu, kurai nav piemērotu grāmatu sarakstā, tu laipni paskaidro, ka mūsu bibliotēkā šobrīd nav šādu grāmatu, un piedāvā kādu no sarakstā esošajām alternatīvām.
        
        Šeit ir saraksts ar grāmatām, kas šobrīd ir pieejamas mūsu bibliotēkā:
        {$bookListText}";

        $fullPrompt = $systemPrompt . "\n\nLietotāja jautājums: " . $request->message;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent?key={$apiKey}";

        try {
            $response = Http::withoutVerifying()->timeout(20)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $fullPrompt]
                        ]
                    ]
                ]
            ]);

            if ($response->failed()) {
                Log::error('Gemini API Error: ' . $response->body());
                return response()->json(['error' => 'AI pakalpojums īslaicīgi nav pieejams.'], 500);
            }

            $data = $response->json();
            $aiText = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Atvainojiet, es nesapratu jautājumu.';

            return response()->json(['reply' => trim($aiText)]);

        } catch (\Exception $e) {
            Log::error('Chat Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Radās tehniska kļūda.'], 500);
        }
    }

    public function summarizeReviews($isbn)
    {
        
        $currentReviewsCount = Atsauksmes::where('Gramatas_ID', $isbn)->whereNotNull('komentārs')->count();

        if ($currentReviewsCount < 2) {
            return response()->json(['summary' => 'Nav pietiekami daudz atsauksmju, lai izveidotu kopsavilkumu.']);
        }

       
        $cachedSummary = AiSummary::where('gramatas_id', $isbn)->first();

       
        if ($cachedSummary && ($currentReviewsCount - $cachedSummary->review_count) < 2) {
            return response()->json(['summary' => $cachedSummary->summary_text, 'cached' => true]);
        }

       
        $apiKey = env('GEMINI_API_KEY');
        $reviews = Atsauksmes::where('Gramatas_ID', $isbn)->whereNotNull('komentārs')->pluck('komentārs')->toArray();
        $reviewsText = implode("\n- ", $reviews);
        
        $prompt = "Tu esi bibliotekārs. Lūk, lietotāju atsauksmes:\n- {$reviewsText}\n\nUzraksti īsu kopsavilkumu (2-3 teikumus) latviešu valodā.";
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        try {
            $response = Http::withoutVerifying()->timeout(45)->post($url, [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

           
            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? 'Nezināma AI kļūda';
                \Log::error('Google API Error: ' . $errorMessage);

              
                if ($response->status() === 429) {
                    if ($cachedSummary) {
                        return response()->json(['summary' => $cachedSummary->summary_text, 'cached' => true]);
                    }
                    return response()->json(['summary' => 'AI dienas limits ir izsmelts. Lūdzu, mēģiniet vēlāk.'], 429);
                }

                
                return response()->json(['summary' => 'AI kļūda: ' . $errorMessage], 500);
            }

           
            $data = $response->json();
            $summaryText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (empty(trim($summaryText))) {
                 return response()->json(['summary' => 'AI atgrieza tukšu atbildi.']);
            }

           
            AiSummary::updateOrCreate(
                ['gramatas_id' => $isbn],
                [
                    'summary_text' => trim($summaryText),
                    'review_count' => $currentReviewsCount
                ]
            );

            return response()->json(['summary' => trim($summaryText), 'cached' => false]);

        } catch (\Exception $e) {
            \Log::error('Summary Exception: ' . $e->getMessage());
            if ($cachedSummary) {
                return response()->json(['summary' => $cachedSummary->summary_text, 'cached' => true]);
            }
            return response()->json(['summary' => 'Kļūda ģenerējot kopsavilkumu.']);
        }
    }
}