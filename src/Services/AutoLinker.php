<?php

namespace SiteOrigin\ScoutLSH\Services;

use DOMDocument;
use DOMXPath;

/**
 * A class that takes links inside HTML, and converts them to links using search.
 */
class AutoLinker
{
    /**
     * @var \SiteOrigin\ScoutLSH\Services\LSHSearcher
     */
    private LSHSearcher $searcher;

    /**
     * @var \SiteOrigin\ScoutLSH\Services\TextEncoder
     */
    private TextEncoder $encoder;

    private array $config;

    public function __construct(LSHSearcher $searcher, TextEncoder $encoder, array $config = [])
    {
        $this->searcher = $searcher;
        $this->encoder = $encoder;
        $this->config = $config;
    }

    public function autolink(string $html, array $config = null): string
    {
        // Find all the links in the HTML
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new DOMXPath($doc);

        // We're going to replace any link with a href that starts with '#search' with a link to the top search result.
        $links = collect($xpath->query('//a[starts-with(@href, "#search")]'));

        $config = $config ?? $this->config;
        $results = $this->searcher->topResult(
            $links->map(fn ($link) => $link->textContent)->toArray(),
            ...$config
        );

        foreach ($links as $link) {
            $result = $results[$link->textContent];
            if (is_null($result) || empty($result->url)) {
                // Unwrap this link because we don't have a page to link to
                $span = $doc->createTextNode($link->textContent);
                $link->parentNode->replaceChild($span, $link);
            } else {
                $link->setAttribute('href', $result->url);
            }
        }

        return $doc->saveHTML();
    }
}
