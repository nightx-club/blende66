<?php

namespace Tests\Feature;

use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MarketingTest extends TestCase
{
    use RefreshDatabase;

    public function test_blende_6_homepage_is_the_pin_protected_upload_gallery(): void
    {
        Wedding::create([
            'couple_names' => 'Blende6',
            'slug' => 'blende6',
            'wedding_date' => '2026-08-22',
            'pin_hash' => Hash::make('123456'),
            'welcome_text' => 'Willkommen',
            'is_active' => true,
            'photo_max_mb' => 25,
            'photo_batch_max' => 20,
            'video_max_mb' => 100,
            'video_max_seconds' => 180,
            'video_batch_max' => 5,
        ]);

        $this->get(route('marketing.home'))
            ->assertOk()
            ->assertSee('Galerie-PIN')
            ->assertSeeText('Startseite')
            ->assertSeeText('Datenschutz');
    }

    public function test_complete_local_portfolio_is_rendered(): void
    {
        $response = $this->get(route('marketing.portfolio'))->assertOk();

        $this->assertSame(73, substr_count($response->getContent(), 'data-portfolio-image="'.asset('images/blende6/weddings/')));
        $this->assertSame(49, substr_count($response->getContent(), 'data-portfolio-image="'.asset('images/blende6/portfolio/')));
    }

    public function test_marketing_information_pages_are_available(): void
    {
        $this->get(route('marketing.about'))->assertOk()->assertSee('Wie ich zur Fotografie kam');
        $this->get(route('marketing.shootings'))->assertOk()->assertSee('Du musst nicht wissen');
        $this->get(route('marketing.contact'))->assertOk()->assertSee('info@blende6.de');
        $this->get(route('marketing.imprint'))->assertOk()->assertSee('Steuer-ID');
        $this->get(route('marketing.privacy'))->assertOk()->assertSee('Eventgalerien und Medien-Uploads');
    }
}
