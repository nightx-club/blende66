<?php

namespace Tests\Feature;

use Tests\TestCase;

class MarketingTest extends TestCase
{
    public function test_blende_6_homepage_uses_local_brand_content(): void
    {
        $this->get(route('marketing.home'))
            ->assertOk()
            ->assertSee('Echte Momente.')
            ->assertSee('Lina-Theresa Dick')
            ->assertSee(asset('images/blende6/hero.jpg'), false)
            ->assertSee(asset('images/blende6-logo.png'), false);
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
