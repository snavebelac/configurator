<?php

namespace App\Helpers;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Allowlist sanitiser for editor-authored HTML.
 *
 * Terms bodies are written in a rich-text editor and then rendered on the
 * public, unauthenticated proposal page. Everything that comes out of the
 * browser is treated as hostile: an allowlist is built from scratch and
 * anything not on it is discarded, rather than trying to strip known-bad
 * markup.
 *
 * The element list below deliberately mirrors what the editor is configured to
 * produce (see resources/js/editor.js). If you enable a node there, allow it
 * here too — otherwise authors will write something that silently vanishes on
 * save.
 */
class HtmlSanitiser
{
    /**
     * The only elements a terms body may contain.
     *
     * @var list<string>
     */
    private const ALLOWED_ELEMENTS = [
        'p', 'br',
        'h2', 'h3', 'h4',
        'strong', 'em', 'u', 's',
        'ul', 'ol', 'li',
        'blockquote',
        'hr',
    ];

    private HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $config = new HtmlSanitizerConfig;

        foreach (self::ALLOWED_ELEMENTS as $element) {
            // No attributes: there is no legitimate reason for a terms
            // document to carry classes, styles or ids, and allowing them
            // widens the surface for nothing.
            $config = $config->allowElement($element, []);
        }

        // Links are the one exception, and are heavily constrained.
        $config = $config
            ->allowElement('a', ['href'])
            ->allowLinkSchemes(['http', 'https', 'mailto'])
            ->forceAttribute('a', 'rel', 'noopener noreferrer nofollow')
            ->forceAttribute('a', 'target', '_blank');

        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function sanitise(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $clean = trim($this->sanitizer->sanitize($html));

        // The editor emits an empty paragraph for empty content; treat that as
        // nothing at all so "is this set written yet?" stays a simple check.
        if ($clean === '' || $clean === '<p></p>' || $clean === '<p><br></p>') {
            return null;
        }

        return $clean;
    }
}
