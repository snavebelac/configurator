<?php

namespace Tests\Feature;

use App\Helpers\HtmlSanitiser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Terms bodies are authored in a browser and rendered on a public,
 * unauthenticated page, so everything arriving from the editor is treated as
 * hostile. These pin the allowlist.
 */
class HtmlSanitiserTest extends TestCase
{
    private HtmlSanitiser $sanitiser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sanitiser = new HtmlSanitiser;
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function hostileMarkup(): array
    {
        return [
            'script tag' => ['<p>Fine</p><script>alert(1)</script>'],
            'inline handler' => ['<p onclick="alert(1)">Fine</p>'],
            'img onerror' => ['<img src=x onerror="alert(1)">'],
            'iframe' => ['<p>Fine</p><iframe src="https://evil.test"></iframe>'],
            'javascript: link' => ['<a href="javascript:alert(1)">Click</a>'],
            'data: link' => ['<a href="data:text/html;base64,PHNjcmlwdD4=">Click</a>'],
            'style tag' => ['<style>body{display:none}</style><p>Fine</p>'],
            'inline style' => ['<p style="position:fixed;inset:0">Fine</p>'],
            'form' => ['<form action="https://evil.test"><input name="card"></form>'],
            'object' => ['<object data="https://evil.test"></object>'],
            'svg script' => ['<svg><script>alert(1)</script></svg>'],
        ];
    }

    #[Test]
    #[DataProvider('hostileMarkup')]
    public function hostile_markup_is_stripped(string $input)
    {
        $output = (string) $this->sanitiser->sanitise($input);

        foreach (['<script', 'onclick', 'onerror', '<iframe', 'javascript:', '<style', 'style=', '<form', '<object', '<input'] as $needle) {
            $this->assertStringNotContainsString($needle, $output, "Sanitised output still contains {$needle}");
        }
    }

    #[Test]
    public function the_formatting_the_editor_produces_survives()
    {
        $input = '<h2>Scope</h2>'
            .'<p>The supplier will deliver <strong>the agreed work</strong> and <em>nothing more</em>.</p>'
            .'<h3>Payment</h3>'
            .'<ul><li>Item one</li><li>Item two</li></ul>'
            .'<ol><li>First</li></ol>'
            .'<blockquote><p>Quoted</p></blockquote>'
            .'<hr />';

        $output = (string) $this->sanitiser->sanitise($input);

        foreach (['<h2>', '<h3>', '<strong>', '<em>', '<ul>', '<ol>', '<li>', '<blockquote>', '<hr'] as $needle) {
            $this->assertStringContainsString($needle, $output, "Sanitiser dropped {$needle}");
        }

        $this->assertStringContainsString('Scope', $output);
        $this->assertStringContainsString('Item two', $output);
    }

    #[Test]
    public function safe_links_survive_and_are_hardened()
    {
        $output = (string) $this->sanitiser->sanitise('<p><a href="https://example.test/terms">Our terms</a></p>');

        $this->assertStringContainsString('https://example.test/terms', $output);
        $this->assertStringContainsString('noopener', $output);
        $this->assertStringContainsString('noreferrer', $output);
        $this->assertStringContainsString('_blank', $output);
    }

    #[Test]
    public function mailto_links_are_allowed()
    {
        $output = (string) $this->sanitiser->sanitise('<p><a href="mailto:hi@example.test">Email us</a></p>');

        // The sanitiser entity-encodes the @, which browsers resolve back — so
        // assert the link survived rather than that the bytes are unchanged.
        $this->assertStringContainsString('mailto:hi', $output);
        $this->assertStringContainsString('example.test', $output);
        $this->assertSame(
            'mailto:hi@example.test',
            html_entity_decode(
                preg_match('/href="([^"]+)"/', $output, $m) ? $m[1] : '',
                ENT_QUOTES | ENT_HTML5,
            ),
        );
    }

    #[Test]
    public function classes_and_ids_are_dropped()
    {
        $output = (string) $this->sanitiser->sanitise('<p class="sneaky" id="x" data-foo="bar">Text</p>');

        $this->assertStringNotContainsString('class=', $output);
        $this->assertStringNotContainsString('id=', $output);
        $this->assertStringNotContainsString('data-foo', $output);
        $this->assertStringContainsString('Text', $output);
    }

    #[Test]
    public function empty_content_normalises_to_null()
    {
        $this->assertNull($this->sanitiser->sanitise(null));
        $this->assertNull($this->sanitiser->sanitise(''));
        $this->assertNull($this->sanitiser->sanitise('<p></p>'));
        $this->assertNull($this->sanitiser->sanitise('<script>alert(1)</script>'));
    }
}
