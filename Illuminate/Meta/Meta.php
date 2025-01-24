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
    public static function metaTitle(string $title, string $content, int $maxLength = 60): string
    {
        // Extract primary keywords from content
        $keywords = self::extractKeywords($content, 3);

        // Combine title with relevant keywords
        foreach ($keywords as $keyword) {
            if (stripos($title, $keyword) === false) {
                $title .= " - " . ucfirst($keyword);
            }
        }

        // Limit title length for SEO best practices
        return self::truncateText($title, $maxLength);
    }

    /**
     * Generate a meta description based on the article content.
     */
    public static function metaDescription(string $content, int $maxLength = 160): string
    {
        // Clean HTML tags and get plain text
        $cleanedContent = strip_tags($content);

        // Summarize content to get first few sentences
        $summary = self::summarizeContent($cleanedContent);

        // Trim summary to specified length
        return self::truncateText($summary, $maxLength);
    }

    /**
     * Generate meta keywords by extracting the most important words and phrases.
     */
    public static function metaKeywords(string $title, string $content, int $limit = 10): string
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
        arsort($wordCount);

        // Take the top keywords
        $topKeywords = array_slice(array_keys($wordCount), 0, $limit);

        // Join keywords into a comma-separated string
        return implode(', ', $topKeywords);
    }

    /**
     * Generate canonical meta tag URL
     */
    public static function canonicalUrl(string $baseUrl, ?string $additionalPath = null): string
    {
        $url = rtrim($baseUrl, '/');
        if ($additionalPath) {
            $url .= '/' . trim($additionalPath, '/');
        }
        return $url;
    }

    /**
     * Extract the most important keywords from the content.
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
     * Summarize the content by extracting key sentences.
     */
    protected static function summarizeContent(string $content): string
    {
        // Split content into sentences using punctuation
        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);

        // Combine the first two sentences for a summary
        return isset($sentences[0]) ? $sentences[0] . (isset($sentences[1]) ? ' ' . $sentences[1] : '') : '';
    }

    /**
     * Truncate text to a specific length without cutting words or sentences.
     */
    protected static function truncateText(string $text, int $maxLength): string
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }

        // Trim to max length
        $truncated = substr($text, 0, $maxLength);

        // Ensure we end with a complete word
        $lastSpace = strrpos($truncated, ' ');
        if ($lastSpace !== false) {
            $truncated = substr($truncated, 0, $lastSpace);
        }

        return trim($truncated) . '...';
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

    /**
     * Generate complete meta tags for SEO
     */
    public static function generateMetaTags(array $options): string
    {
        $tags = [];

        // Title tag
        if (isset($options['title'])) {
            $tags[] = "<title>{$options['title']}</title>";
        }

        // Description tag
        if (isset($options['description'])) {
            $tags[] = "<meta name=\"description\" content=\"{$options['description']}\">";
        }

        // Keywords tag
        if (isset($options['keywords'])) {
            $tags[] = "<meta name=\"keywords\" content=\"{$options['keywords']}\">";
        }

        // Canonical URL
        if (isset($options['canonical'])) {
            $tags[] = "<link rel=\"canonical\" href=\"{$options['canonical']}\">";
        }

        // Open Graph tags
        if (isset($options['og'])) {
            $og = $options['og'];
            if (isset($og['title'])) {
                $tags[] = "<meta property=\"og:title\" content=\"{$og['title']}\">";
            }
            if (isset($og['description'])) {
                $tags[] = "<meta property=\"og:description\" content=\"{$og['description']}\">";
            }
            if (isset($og['image'])) {
                $tags[] = "<meta property=\"og:image\" content=\"{$og['image']}\">";
            }
        }

        return implode("\n", $tags);
    }
}