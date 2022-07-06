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

    private array $config;

    public function __construct(LSHSearcher $searcher, array $config = [])
    {
        $this->searcher = $searcher;
        $this->config = $config;
    }

    /**
     * Given some HTML, automatically link to related content using LSH.
     *
     * @param string $html
     * @param array|null $config
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function autolink(string $html, array $config = null): string
    {
        // Find all the links in the HTML
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new DOMXPath($doc);

        // We're going to replace any link with a href that starts with '#search' with a link to the top search result.
        $links = collect($xpath->query('//a[starts-with(@href, "#search")]'));

        $queries = $links->map(function ($link) use ($doc) {
            $href = $link->getAttribute('href');
            // If the href is exactly '#search', then we'll use the text of the link as the query.
            if (str_starts_with($href, '#search:')) {
                $query = explode(':', $href, 2)[1];
            } else {
                $query = $link->nodeValue;
            }

            return $query;
        })->toArray();

        $config = $config ?? $this->config;
        $results = $this->searcher->topResult($queries, ...$config);

        foreach ($links as $i => $link) {
            $result = $results[$queries[$i]];
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
