<?php

declare(strict_types=1);

/**
 * ==========================================
 * Bolt - Meta Class ========================
 * ==========================================
 */

namespace celionatti\Bolt\Illuminate\Meta;

class Meta
{
    /**
     * Generate an SEO-friendly meta title.
     */
    public static function MetaTitle(string $title, string $content): string
    {
        // Extract primary keywords from content
        $keywords = self::extractKeywords($content, 3);

        // Check if keywords are present in the title, if not, append them
        foreach ($keywords as $keyword) {
            if (stripos($title, $keyword) === false) {
                $title .= " - " . ucfirst($keyword);
            }
        }

        // Limit title to 60 characters for SEO best practices
        return strlen($title) > 60 ? substr($title, 0, 57) . '...' : $title;
    }

    /**
     * Generate a meta description based on the article content.
     */
    public static function MetaDescription(string $content): string
    {
        // Clean HTML tags and get plain text
        $cleanedContent = strip_tags($content);

        // Summarize content to get first few sentences
        $summary = self::summarizeContent($cleanedContent);

        // Check if the summary is more than 160 characters; trim without cutting off words or sentences
        if (strlen($summary) > 160) {
            $summary = self::trimSentence($summary, 160);
        }

        return $summary;
    }

    /**
     * Generate meta keywords by extracting the most important words and phrases.
     */
    public static function MetaKeywords(string $title, string $content): string
    {
        // Combine the title and content for keyword extraction
        $text = strtolower($title . ' ' . $content);

        // Clean the text: remove special characters, extra spaces, and HTML tags
        $text = preg_replace('/[^a-z0-9\s]+/', '', strip_tags($text));

        // Split the text into individual words
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        // Remove stopwords and keep only words with more than 2 characters
        $stopWords = self::getStopWords();
        $filteredWords = array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });

        // Count word frequency
        $wordCount = array_count_values($filteredWords);
        arsort($wordCount); // Sort by frequency in descending order

        // Take the top 10 most frequent keywords
        $topKeywords = array_slice(array_keys($wordCount), 0, 10);

        // Join keywords into a comma-separated string
        return implode(', ', $topKeywords);
    }

    /**
     * Extract the most important keywords from the content using NLP-like logic.
     */
    protected static function extractKeywords(string $text, int $limit = 10): array
    {
        // Clean text by removing special characters, and converting to lowercase
        $text = strtolower(preg_replace('/[^a-z0-9\s]+/', '', strip_tags($text)));

        // Split text into words
        $words = explode(' ', $text);

        // Remove stopwords
        $stopWords = self::getStopWords();
        $filteredWords = array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });

        // Count word frequencies
        $wordCount = array_count_values($filteredWords);
        arsort($wordCount);

        // Return the top $limit keywords
        return array_slice(array_keys($wordCount), 0, $limit);
    }

    /**
     * Summarize the content by extracting key sentences or sections.
     */
    protected static function summarizeContent(string $content): string
    {
        // Split content into sentences using punctuation
        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);

        // Combine the first two sentences for a summary
        return isset($sentences[0]) ? $sentences[0] . (isset($sentences[1]) ? ' ' . $sentences[1] : '') : '';
    }

    /**
     * Trim the content to fit within a given length without cutting off words or sentences.
     */
    protected static function trimSentence(string $text, int $maxLength): string
    {
        // Trim only after complete sentences or words, ensuring we don't cut mid-word or sentence
        if (strlen($text) > $maxLength) {
            $trimmedText = substr($text, 0, $maxLength);

            // Ensure we end with a complete sentence or word
            if (preg_match('/.*[.!?](\s+|$)/', $trimmedText, $matches)) {
                return rtrim($trimmedText) . '...';
            }

            // Otherwise trim to the last space so no word is cut
            return substr($trimmedText, 0, strrpos($trimmedText, ' ')) . '...';
        }

        return $text;
    }

    /**
     * Get a list of common stopwords to exclude from keyword extraction.
     */
    protected static function getStopWords(): array
    {
        return [
            'the', 'and', 'a', 'is', 'of', 'to', 'in', 'on', 'with', 'for', 'it', 'by', 'at', 'be', 'this',
            'that', 'which', 'as', 'but', 'or', 'are', 'from', 'has', 'had', 'were', 'was', 'have', 'also',
            'an', 'its', 'not', 'can', 'will', 'about', 'more', 'there', 'their', 'so', 'some', 'brings', 'together'
        ];
    }
}