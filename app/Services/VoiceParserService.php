<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoiceParserService
{
    /**
     * Parse text from voice input into transaction attributes.
     * 
     * @param string $text
     * @param array $categories
     * @return array
     */
    public function parse(string $text, array $categories = []): array
    {
        // If API Key exists, we can use AI
        $geminiKey = env('GEMINI_API_KEY');
        if (!empty($geminiKey)) {
            try {
                return $this->parseWithGemini($text, $categories, $geminiKey);
            } catch (\Exception $e) {
                Log::error('Failed parsing with Gemini API: ' . $e->getMessage());
            }
        }

        // Fallback to zero-config smart local parser
        return $this->parseLocally($text, $categories);
    }

    /**
     * Smart Local Parser for Indonesian Natural Language
     */
    protected function parseLocally(string $text, array $categories): array
    {
        $cleanText = strtolower($text);
        
        // Common Indonesian slang and number mapping
        $slang = [
            'gopek' => 500,
            'goceng' => 5000,
            'ceng' => 1000,
            'ceban' => 10000,
            'ceng' => 1000,
            'goban' => 50000,
            'cepek' => 100,
        ];

        foreach ($slang as $slangWord => $val) {
            if (str_contains($cleanText, $slangWord)) {
                $cleanText = str_replace($slangWord, " $val ", $cleanText);
            }
        }

        // Basic text numbers to digits
        $wordsToDigits = [
            'setengah' => '0.5',
            'setengah' => '0.5',
            'satu' => '1', 'dua' => '2', 'tiga' => '3', 'empat' => '4', 
            'lima' => '5', 'enam' => '6', 'tujuh' => '7', 'delapan' => '8', 'sembilan' => '9',
            'sepuluh' => '10', 'sebelas' => '11', 'seratus' => '100', 'seribu' => '1000',
            'sejuta' => '1000000'
        ];

        foreach ($wordsToDigits as $word => $digit) {
            $cleanText = preg_replace('/\b' . $word . '\b/u', $digit, $cleanText);
        }

        // Extract numbers and words
        // Match direct numbers like 25.000, 25000, 10,000
        $numericPattern = '/(\d+(?:[\.,]\d+)*)/';
        
        $amount = 0;
        
        // Identify modifiers (ribu, juta, dsb)
        $multipliers = [
            'ribu' => 1000,
            'juta' => 1000000,
            'miliar' => 1000000000,
            'k' => 1000,
        ];

        // Find all numerical chunks
        preg_match_all($numericPattern, $cleanText, $matches);
        $rawNumbers = $matches[0] ?? [];

        if (!empty($rawNumbers)) {
            // Let's try to find combinations of "number + multiplier"
            foreach ($rawNumbers as $rawNum) {
                // Strip commas/periods for normalization
                $numVal = (float) str_replace([',', '.'], ['', ''], $rawNum);
                
                // Scan around the number for multiplier words
                foreach ($multipliers as $word => $mult) {
                    if (preg_match('/' . preg_quote($rawNum, '/') . '\s*(' . $word . ')/', $cleanText)) {
                        $numVal *= $mult;
                        break;
                    }
                }

                // Take the largest identified amount
                if ($numVal > $amount) {
                    $amount = $numVal;
                }
            }
        }

        // Fallback if text had pure numbers but no multipliers
        if ($amount === 0 && !empty($rawNumbers)) {
            $amount = (float) str_replace([',', '.'], ['', ''], $rawNumbers[0]);
        }

        // Construct description (Note)
        // Strip amount indicator words and the extracted numbers
        $stopWords = ['rupiah', 'rp', 'ribu', 'juta', 'miliar', 'k', 'sebesar', 'nominal'];
        $note = $text;
        
        foreach ($rawNumbers as $num) {
            $note = str_ireplace($num, '', $note);
        }

        foreach ($stopWords as $sw) {
            $note = preg_replace('/\b' . $sw . '\b/ui', '', $note);
        }

        // Clean up spaces, strip common filler words from the beginning
        $note = trim(preg_replace('/\s+/', ' ', $note));
        $note = preg_replace('/^(buat|untuk|bayar|beli)\s+/ui', '', $note);
        $note = ucfirst(trim($note));

        if (empty($note)) {
            $note = "Catatan Suara (" . date('H:i') . ")";
        }

        // Categorization recommendation
        $detectedCategoryId = null;
        $bestScore = 0;

        foreach ($categories as $cat) {
            $catName = strtolower($cat['name'] ?? '');
            // Check if category name is present in text
            if (!empty($catName) && (str_contains(strtolower($text), $catName) || str_contains(strtolower($note), $catName))) {
                $detectedCategoryId = $cat['id'];
                break;
            }
            
            // Fallback: check common matching keywords
            $keywords = $this->getCategoryKeywords($catName);
            foreach ($keywords as $kw) {
                if (str_contains(strtolower($text), $kw)) {
                    $detectedCategoryId = $cat['id'];
                    break 2;
                }
            }
        }

        return [
            'amount' => (int) $amount,
            'note' => $note,
            'category_id' => $detectedCategoryId,
            'method' => 'local',
        ];
    }

    /**
     * Parse text using Gemini API
     */
    protected function parseWithGemini(string $text, array $categories, string $apiKey): array
    {
        $catList = array_map(fn($c) => "ID: {$c['id']} - Name: {$c['name']}", $categories);
        $catStr = implode(", ", $catList);

        $prompt = "You are a smart financial transaction parser. "
            . "Given this raw Indonesian text: \"$text\", "
            . "Extract the transaction 'amount' (integer), 'note' (string description, cleaned and capitalized), "
            . "and map it to the most appropriate 'category_id' (integer) from this provided list of available categories: [$catStr]. "
            . "If no category matches well, set 'category_id' to null. "
            . "Return only valid JSON. Do not include Markdown markup or explanation.";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
            'contents' => [
                'parts' => [
                    ['text' => $prompt]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json'
            ]
        ]);

        if ($response->successful()) {
            $body = $response->json();
            $candidateText = $body['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $parsed = json_decode($candidateText, true);

            if ($parsed) {
                return [
                    'amount' => (int) ($parsed['amount'] ?? 0),
                    'note' => $parsed['note'] ?? ucfirst($text),
                    'category_id' => $parsed['category_id'] ?? null,
                    'method' => 'gemini',
                ];
            }
        }

        throw new \Exception("Gemini API error or malformed JSON");
    }

    /**
     * Map simple keywords for local categorization
     */
    protected function getCategoryKeywords(string $catName): array
    {
        $map = [
            'makanan' => ['makan', 'beli', 'jajan', 'soto', 'bakso', 'restoran', 'lunch', 'dinner', 'sarapan', 'kopi', 'warung', 'indomaret', 'alfamart'],
            'transportasi' => ['bensin', 'ojek', 'gojek', 'grab', 'taksi', 'parkir', 'bus', 'kereta', 'travel', 'tol', 'service', 'ban'],
            'belanja' => ['beli', 'supermarket', 'mall', 'baju', 'sepatu', 'tas', 'shopee', 'tokopedia', 'lazada', 'skincare'],
            'tagihan' => ['listrik', 'air', 'wifi', 'internet', 'pulsa', 'token', 'pln', 'pdam', 'bpjs', 'pajak', 'langganan', 'netflix', 'spotify'],
            'kesehatan' => ['dokter', 'obat', 'apotek', 'sakit', 'klinik', 'vitamin', 'masker', 'rs'],
            'pendidikan' => ['spp', 'buku', 'sekolah', 'kuliah', 'kursus', 'pelatihan', 'atls'],
            'hiburan' => ['nonton', 'bioskop', 'film', 'liburan', 'game', 'topup', 'ps', 'karaoke'],
            'sedekah' => ['zakat', 'infak', 'sumbangan', 'masjid', 'gereja', 'amal', 'tips'],
        ];

        foreach ($map as $key => $keywords) {
            if (str_contains($catName, $key)) {
                return $keywords;
            }
        }

        return [];
    }
}
