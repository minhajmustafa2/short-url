<?php

namespace AshAllenDesign\ShortURL\Tests\Unit\Controllers;

use AshAllenDesign\ShortURL\Events\ShortURLVisited;
use AshAllenDesign\ShortURL\Models\ShortURL;
use AshAllenDesign\ShortURL\Models\ShortURLVisit;
use AshAllenDesign\ShortURL\Tests\Unit\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class ShortURLControllerTest extends TestCase
{
    #[Test]
    public function request_is_aborted_with_http_404_if_the_short_url_cannot_be_found(): void
    {
        $this->get('/short/INVALID')->assertNotFound();
    }

    #[Test]
    public function visitor_is_redirected_to_the_destination_url(): void
    {
        ShortURL::create([
            'destination_url' => 'https://google.com',
            'default_short_url' => config('short-url.default_url').'/short/12345',
            'url_key' => '12345',
            'single_use' => true,
            'track_visits' => true,
            'redirect_status_code' => 301,
            'activated_at' => now()->subMinute(),
        ]);

        $this->get('/short/12345')->assertStatus(301)->assertRedirect('https://google.com');
    }

    /** @test */
    public function request_is_aborted_if_custom_routing_is_enabled_but_the_default_route_has_been_used()
    {
        Config::set('short-url.disable_default_route', true);

        ShortURL::create([
            'destination_url'      => 'https://google.com',
            'default_short_url'    => config('app.url').'/short/12345',
            'url_key'              => '12345',
            'single_use'           => true,
            'track_visits'         => true,
            'redirect_status_code' => 301,
            'activated_at'         => now()->subMinute(),
        ]);

        $this->get('/short/12345')->assertNotFound();
    }

    #[Test]
    public function event_is_dispatched_when_the_short_url_is_visited(): void
    {
        Event::fake();

        $shortURL = ShortURL::create([
            'destination_url' => 'https://google.com',
            'default_short_url' => config('short-url.default_url').'/short/12345',
            'url_key' => '12345',
            'single_use' => true,
            'track_visits' => true,
            'redirect_status_code' => 301,
            'track_ip_address' => true,
            'track_operating_system' => true,
            'track_operating_system_version' => false,
            'track_browser' => true,
            'track_browser_version' => true,
            'track_referer_url' => false,
            'track_device_type' => true,
            'activated_at' => now()->subMinute(),
            'deactivated_at' => null,
        ]);

        $this->get('/short/12345')->assertStatus(301)->assertRedirect('https://google.com');

        // Get the visit that was just logged.
        $visit = ShortURLVisit::first();

        Event::assertDispatched(ShortURLVisited::class, function (ShortURLVisited $event) use ($shortURL, $visit) {
            if ($shortURL->toArray() != $event->shortURL->fresh()->toArray()) {
                return false;
            }

            if ($visit->toArray() != $event->shortURLVisit->fresh()->toArray()) {
                return false;
            }

            return true;
        });
    }

    #[Test]
    public function visitor_is_redirected_with_correct_status_code(): void
    {
        ShortURL::create([
            'destination_url' => 'https://google.com',
            'default_short_url' => config('short-url.default_url').'/short/12345',
            'url_key' => '12345',
            'single_use' => true,
            'track_visits' => true,
            'redirect_status_code' => 302,
            'activated_at' => now()->subMinute(),
        ]);

        $this->get('/short/12345')->assertStatus(302)->assertRedirect('https://google.com');
    }

    #[Test]
    public function request_is_aborted_if_the_activation_date_is_in_the_future(): void
    {
        ShortURL::create([
            'destination_url' => 'https://google.com',
            'default_short_url' => config('short-url.default_url').'/short/12345',
            'url_key' => '12345',
            'single_use' => true,
            'track_visits' => true,
            'redirect_status_code' => 302,
            'activated_at' => now()->addMinute(),
            'deactivated_at' => null,
        ]);

        $this->get('/short/12345')->assertNotFound();
    }

    #[Test]
    public function request_is_aborted_if_the_deactivation_date_is_in_the_past(): void
    {
        ShortURL::create([
            'destination_url' => 'https://google.com',
            'default_short_url' => config('short-url.default_url').'/short/12345',
            'url_key' => '12345',
            'single_use' => true,
            'track_visits' => true,
            'redirect_status_code' => 302,
            'activated_at' => now()->subMinutes(2),
            'deactivated_at' => now()->subMinute(),
        ]);

        $this->get('/short/12345')->assertNotFound();
    }

    #[Test]
    public function visitor_is_redirected_to_the_destination_url_if_the_deactivation_date_is_in_the_future(): void
    {
        ShortURL::create([
            'destination_url' => 'https://google.com',
            'default_short_url' => config('short-url.default_url').'/short/12345',
            'url_key' => '12345',
            'single_use' => true,
            'track_visits' => true,
            'redirect_status_code' => 302,
            'activated_at' => now()->subMinute(),
            'deactivated_at' => now()->addMinute(),
        ]);

        $this->get('/short/12345')->assertStatus(302)->assertRedirect('https://google.com');
    }

}
