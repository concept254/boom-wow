<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class IconsTest extends TestCase
{
    public function test_icon_component_renders_an_inline_svg_with_classes(): void
    {
        $html = Blade::render('<x-icon name="phone" class="h-6 w-6 text-brand-orange" />');

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('h-6 w-6 text-brand-orange', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
    }

    public function test_unknown_icon_name_falls_back_instead_of_breaking_the_page(): void
    {
        $this->assertStringContainsString('<circle', Blade::render('<x-icon name="does-not-exist" />'));
    }

    public function test_every_icon_in_the_set_is_valid_markup_and_navigation_icons_exist(): void
    {
        $icons = config('icons');
        $this->assertGreaterThan(70, count($icons));
        foreach (['dashboard', 'store', 'bag', 'calendar-check', 'users', 'settings', 'shield', 'activity', 'x-social', 'whatsapp'] as $name) {
            $this->assertArrayHasKey($name, $icons);
        }
        foreach ($icons as $name => $svg) {
            $this->assertNotFalse(simplexml_load_string('<svg xmlns="http://www.w3.org/2000/svg">'.$svg.'</svg>'), "Icon {$name} is not valid SVG");
        }
    }

    public function test_business_icon_is_chosen_from_its_name_or_category(): void
    {
        $this->assertSame('scissors', (new BusinessUnit(['name' => 'Glow Salon']))->icon_name);
        $this->assertSame('wine', (new BusinessUnit(['name' => 'Cellar', 'type' => 'Liquor store']))->icon_name);
        $this->assertSame('shirt', (new BusinessUnit(['name' => 'Clothing', 'type' => 'Clothing store']))->icon_name);
        $this->assertSame('laptop', (new BusinessUnit(['name' => 'IT Consultants']))->icon_name);
        $this->assertSame('building', (new BusinessUnit(['name' => 'Property Management']))->icon_name);
        $this->assertSame('ticket', (new BusinessUnit(['name' => 'Events Management']))->icon_name);
        $this->assertSame('store', (new BusinessUnit(['name' => 'Pet Shop']))->icon_name);
    }

    public function test_public_pages_render_icons(): void
    {
        $this->get('/')->assertOk()->assertSee('<svg', false);
        $this->get('/contact')->assertOk()->assertSee('stroke-linecap="round"', false);
    }
}
