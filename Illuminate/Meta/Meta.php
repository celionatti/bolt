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
        $keywords = self::extractKeywords($title . ' ' . $content, 3);

        // Check if keywords are present in title, if not, append them
        foreach ($keywords as $keyword) {
            if (stripos($title, $keyword) === false) {
                $title .= " - " . ucfirst($keyword);
            }
        }

        // Limit title to 60 characters
        return strlen($title) > 60 ? substr($title, 0, 57) . '...' : $title;
    }

    /**
     * Generate a meta description based on content and NLP-based summarization.
     */
    public static function MetaDescription(string $content): string
    {
        // Clean HTML tags and get plain text
        $cleanedContent = strip_tags($content);

        // Get a summary of the content (using the first 2 sentences as an example)
        $summary = self::summarizeContent($cleanedContent);

        // Limit to 160 characters
        return strlen($summary) > 160 ? substr($summary, 0, 157) . '...' : $summary;
    }

    /**
     * Generate meta keywords by extracting the most important words and phrases.
     */
    public static function MetaKeywords(string $title, string $content): string
    {
        // Extract 10 most relevant keywords from title and content
        $keywords = self::extractKeywords($title . ' ' . $content, 10);

        // Create a comma-separated string of keywords
        return implode(', ', $keywords);
    }

    /**
     * Extract most important keywords using NLP-like logic.
     */
    protected static function extractKeywords(string $text, int $limit = 10): array
    {
        // Clean text by removing special characters and converting to lowercase
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

        // Return top $limit keywords
        return array_slice(array_keys($wordCount), 0, $limit);
    }

    /**
     * Summarize content by extracting key sentences or sections.
     */
    protected static function summarizeContent(string $content): string
    {
        // Split content into sentences
        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);

        // Return the first two sentences as a summary
        return isset($sentences[0]) ? $sentences[0] . (isset($sentences[1]) ? ' ' . $sentences[1] : '') : '';
    }

    /**
     * Get a list of common stopwords to exclude from keyword extraction.
     */
    protected static function getStopWords(): array
    {
        return [
            'the', 'and', 'a', 'is', 'of', 'to', 'in', 'on', 'with', 'for', 'it', 'by', 'at', 'be', 'this',
            'that', 'which', 'as', 'but', 'or', 'are', 'from', 'has', 'had', 'were', 'was', 'have', 'also',
            'an', 'its', 'not', 'can', 'will', 'about', 'more', 'there', 'their', 'so', 'some'
        ];
    }

    /**
     * Find synonyms to add variety to keywords (optional, placeholder function).
     */
    protected static function findSynonyms(string $keyword): array
    {
        // This can be improved by integrating an API for synonyms (e.g., Datamuse, WordNet, etc.)
        $synonyms = [
            'article' => ['post', 'blog', 'story'],
            'content' => ['text', 'material', 'information'],
            // Add more manually or use an API for real-time synonyms
        ];

        return $synonyms[$keyword] ?? [$keyword];
    }
}