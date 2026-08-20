<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The field primitives lay themselves out with a class passed by the caller —
 * grid column spans in the modals, spacing on the terms editor. The wrapper
 * used to drop `class` on the floor, so every one of those call sites was
 * silently laying out wrong.
 */
class FormFieldComponentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Rendering a field outside a request means no error bag has been
        // shared, and every one of these components asks the bag whether its
        // field failed validation.
        View::share('errors', new ViewErrorBag);
    }

    /** @return array<string, array<int, string>> */
    public static function fieldComponents(): array
    {
        return [
            'field' => ['field'],
            'select-field' => ['select-field'],
            'checkbox-field' => ['checkbox-field'],
            'textarea-field' => ['textarea-field'],
        ];
    }

    #[Test]
    #[DataProvider('fieldComponents')]
    public function it_puts_a_caller_supplied_class_on_the_field_wrapper(string $component): void
    {
        $html = Blade::render(
            '<x-'.$component.' label="Name" name="name" class="sm:col-span-2" />',
        );

        $this->assertStringContainsString('sm:col-span-2', $html);
    }

    #[Test]
    public function it_keeps_the_id_off_the_wrapper_and_on_the_input(): void
    {
        $html = Blade::render('<x-field label="Name" name="name" id="custom" class="mb-6" />');

        $this->assertStringContainsString('id="custom"', $html);
        $this->assertStringContainsString('mb-6', $html);

        // The wrapper is the first element; it must not carry the id, or the
        // label's `for` would point at a div.
        $this->assertStringNotContainsString('<div id="custom"', $html);
    }
}
