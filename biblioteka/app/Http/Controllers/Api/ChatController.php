<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
            ->limit(5)
            ->get();

        if ($books->isEmpty()) {
            $books = Gramata::limit(10)->get();
        }

        $bookListText = "";
        foreach ($books as $book) {
           
            $shortDesc = Str::limit($book->apraksts, 1500);
            $bookListText .= "- Nosaukums: {$book->nosaukums}, Autors: {$book->autors}. Apraksts: {$shortDesc}\n";
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

       
        $models = ['gemini-3.6-flash', 'gemini-3.8-flash', 'gemini-3.5-flash'];
        $response = null;
        $isSuccess = false;

        foreach ($models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            try {
                
                $response = Http::withoutVerifying()
                    ->timeout(1200) 
                    ->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $fullPrompt]
                                ]
                            ]
                        ]
                    ]);

                if ($response->successful()) {
                    $isSuccess = true;
                    Log::info("Chat atbilde saņemta no modeļa: {$model}");
                    break; 
                }

                
                if ($response->status() === 503) {
                    Log::warning("Modelis {$model} pārslogots. Pārejam uz nākamo...");
                    continue;
                }

                Log::error('Gemini API Error: ' . $response->body());
                break;

            } catch (\Exception $e) {
                Log::error("Chat izņēmums ar modeli {$model}: " . $e->getMessage());
                continue;
            }
        }

        if (!$isSuccess || !$response || $response->failed()) {
            return response()->json(['error' => 'AI pakalpojums īslaicīgi nav pieejams vai arī atbilde aizņem pārāk ilgu laiku. Lūdzu, mēģiniet vēlreiz.'], 503);
        }

        $data = $response->json();
        $aiText = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Atvainojiet, es nesapratu jautājumu.';

        return response()->json(['reply' => trim($aiText)]);
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
        
        
        $models = ['gemini-3.8-flash', 'gemini-3.6-flash', 'gemini-3.5-flash'];
        $response = null;
        $isSuccess = false;

        foreach ($models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            try {
                
                $response = Http::withoutVerifying()
                    ->timeout(1200)
                    ->retry(2, 3000, function ($exception) {
                        return $exception instanceof \Illuminate\Http\Client\RequestException 
                            && $exception->response->status() === 503;
                    })
                    ->post($url, [
                        'contents' => [['parts' => [['text' => $prompt]]]]
                    ]);

                if ($response->successful()) {
                    $isSuccess = true;
                    break; 
                }

                if ($response->status() === 503) {
                    \Log::warning("Sammari: Modelis {$model} pārslogots. Pārejam uz nākamo...");
                    continue;
                }

                break; 

            } catch (\Exception $e) {
                \Log::error("Sammari izņēmums ar modeli {$model}: " . $e->getMessage());
                continue; 
            }
        }

        
        if (!$isSuccess || !$response || $response->failed()) {
            
            if ($cachedSummary) {
                return response()->json(['summary' => $cachedSummary->summary_text, 'cached' => true]);
            }
            return response()->json(['summary' => 'AI šobrīd ir pārslogots. Lūdzu, mēģiniet vēlāk.'], 503);
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
    }

    
    public function generateBookData(Request $request)
    {
        $request->validate(['nosaukums' => 'required|string']);

        $apiKey = env('GEMINI_API_KEY_ADMIN') ?: env('GEMINI_API_KEY_USER'); 
        if (!$apiKey) {
            return response()->json(['error' => 'API atslēga nav konfigurēta.'], 500);
        }

        $prompt = "Tu esi grāmatu eksperts. Pamatojoties uz nosaukumu '{$request->nosaukums}', nosaki autora vārdu, izdošanas gadu, aptuvenu lappušu skaitu un uzraksti aprakstu (līdz 400 rakstzīmēm) latviešu valodā. 
        Ja šī grāmatas nav plaši pazīstama, lūdzu, izdomā loģisku un ticamu informāciju.
        Atgriez TIKAI JSON formātu, bez jebkāda cita teksta.
        Formāts: {\"autors\": \"Vārds Uzvārds\", \"gads\": \"2020\", \"lapu_skaits\": 250, \"apraksts\": \"Šeit ir apraksts...\"}";

        
        $models = [
            'gemini-3.8-flash',
            'gemini-3.6-flash',
            'gemini-3.5-flash',
            'gemini-3.1-flash-lite'
        ];
        
        $response = null;
        $isSuccess = false;

        foreach ($models as $index => $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            try {
                
                $response = Http::withoutVerifying()
                    ->timeout(120)
                    ->retry(2, 2000, function ($exception) {
                        return $exception instanceof \Illuminate\Http\Client\RequestException 
                            && $exception->response->status() === 503;
                    })
                    ->post($url, [
                        'contents' => [['parts' => [['text' => $prompt]]]]
                    ]);

                if ($response->successful()) {
                    $isSuccess = true;
                    Log::info("AI atbilde saņemta no modeļa: {$model}");
                    break; 
                }

                
                if ($response->status() === 503) {
                    Log::warning("Modelis {$model} joprojām pārslogots. Pārejam uz nākamo...");
                    continue; 
                }

               
                Log::error("Gemini API kļūda ({$model}): " . $response->body());
                continue; 

            } catch (\Exception $e) {
                Log::error("Izņēmums ar modeli {$model}: " . $e->getMessage());
                continue;
            }
        }

        if (!$isSuccess || !$response || $response->failed()) {
            return response()->json(['error' => 'Visas AI modeles šobrīd ir pārslogotas. Lūdzu, mēģiniet vēlreiz pēc minūtes.'], 503);
        }

        $data = $response->json();
        $aiText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        
        $aiText = str_replace(['```json', '```'], '', $aiText);
        $aiText = trim($aiText);
        
        $bookData = json_decode($aiText, true);

        if (!$bookData || !isset($bookData['autors'])) {
            Log::error('AI JSON Parse Error. Raw text: ' . $aiText);
            return response()->json(['error' => 'AI atgrieza nederīgu formātu. Mēģiniet vēlreiz.'], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $bookData
        ]);
    }
}