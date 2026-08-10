<?php
/**
 * ProfanityFilterHelper.php
 * Automated Content Moderation & Sensor Engine for Realtime Chat
 * Filters Profanity, Pornography, Vulgarity, Violence, and Leetspeak evasions.
 */

class ProfanityFilterHelper {

    /**
     * List of inappropriate words across Indonesian, English, Vulgar, Pornographic, and Violence categories.
     */
    private static $badWords = [
        // --- Indonesian Profanity & Bad Words ---
        'anjing', 'anjingg', 'anjrit', 'anjir', 'anjay', 'asua', 'asu', 'babi', 'bangsat', 'bangsatk',
        'kontol', 'kontolnya', 'memek', 'memeknya', 'pantek', 'pepek', 'itil', 'peler', 'ngentot', 'ngentod',
        'jembut', 'goblok', 'goblog', 'tolol', 'kampret', 'bajingan', 'bego', 'begok', 'jancok', 'jancuk',
        'modar', 'perek', 'lonte', 'silit', 'sange', 'tetek', 'toket', 'ngaceng', 'crot', 'bokep', 'porno',
        'banci', 'bencong', 'pantas', 'kentu', 'tempik', 'peli', 'beler', 'kimak', 'puki', 'pukimak',

        // --- English Profanity & Vulgar Terms ---
        'fuck', 'fucking', 'fucker', 'shit', 'shitting', 'bitch', 'bitches', 'asshole', 'bastard',
        'cunt', 'dick', 'pussy', 'cock', 'motherfucker', 'porn', 'porno', 'nude', 'nudes', 'naked',
        'slut', 'whore', 'boobs', 'penis', 'vagina', 'masturbate', 'orgasm', 'hentai',

        // --- Violence & Threat Words ---
        'membunuh', 'pembantaian', 'bacok', 'gorok', 'penganiayaan', 'suicide', 'slaughter'
    ];

    /**
     * Leetspeak / Substitution patterns for obfuscated words
     */
    private static $leetSubstitutions = [
        '0' => 'o',
        '1' => 'i',
        '3' => 'e',
        '4' => 'a',
        '5' => 's',
        '7' => 't',
        '8' => 'b',
        '@' => 'a',
        '$' => 's',
        '*' => 'u'
    ];

    /**
     * Filter text and replace any bad words with masked stars (e.g., a***g, f***k, k***l)
     */
    public static function filter(?string $text): string {
        if (empty($text)) {
            return '';
        }

        $filteredText = $text;

        foreach (self::$badWords as $word) {
            if (strlen($word) < 2) continue;

            // Build regex pattern that accounts for spaces, dots, or symbols inserted between characters (e.g., k.o.n.t.o.l or a.n.j.i.n.g)
            $chars = str_split($word);
            $regexPattern = '/\b';
            foreach ($chars as $i => $char) {
                // Allow optional leetspeak or separator between characters
                $leetChar = self::getLeetPattern($char);
                if ($i > 0) {
                    $regexPattern .= '[\s\._\-\*]*';
                }
                $regexPattern .= $leetChar;
            }
            $regexPattern .= '\b/i';

            $filteredText = preg_replace_callback($regexPattern, function($matches) {
                $matchedWord = $matches[0];
                $len = mb_strlen($matchedWord);
                if ($len <= 2) {
                    return str_repeat('*', $len);
                }
                $firstChar = mb_substr($matchedWord, 0, 1);
                $lastChar = mb_substr($matchedWord, -1, 1);
                return $firstChar . str_repeat('*', $len - 2) . $lastChar;
            }, $filteredText);
        }

        return $filteredText;
    }

    /**
     * Check if text contains any inappropriate bad words
     */
    public static function containsBadWords(?string $text): bool {
        if (empty($text)) return false;
        $filtered = self::filter($text);
        return $filtered !== $text;
    }

    /**
     * Get regex pattern for character including common leetspeak substitutions
     */
    private static function getLeetPattern(string $char): string {
        $c = strtolower($char);
        switch ($c) {
            case 'a': return '[aA4@]';
            case 'b': return '[bB8]';
            case 'e': return '[eE3]';
            case 'i': return '[iI1!]';
            case 'l': return '[lL1|]';
            case 'o': return '[oO0]';
            case 's': return '[sS5$]';
            case 't': return '[tT7+]';
            case 'u': return '[uU\*]';
            default: return preg_quote($c, '/');
        }
    }
}
