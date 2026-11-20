<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBannerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_a_banner_that_appears_on_the_homepage(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.banners.index'))
            ->assertOk()
            ->assertSee('Create a campaign');

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Homepage campaigns');

        $this->actingAs($admin)
            ->post(route('admin.banners.store'), [
                'title' => 'Pizza night',
                'subtitle' => 'Cheesy favourites delivered fresh.',
                'image' => UploadedFile::fake()->createWithContent(
                    'pizza.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a0+8AAAAASUVORK5CYII=', true),
                ),
                'url' => '/?search=Pizza',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $banner = Banner::firstOrFail();
        Storage::disk('public')->assertExists($banner->image);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Pizza night')
            ->assertSee('Explore dishes')
            ->assertSee('href="/?search=Pizza"', false);

        $this->get('/?search=Pizza')
            ->assertOk()
            ->assertSee('name="search" value="Pizza"', false);
    }

    public function test_admin_cannot_publish_a_javascript_banner_destination(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.banners.index'))
            ->post(route('admin.banners.store'), [
                'title' => 'Unsafe destination',
                'image' => UploadedFile::fake()->createWithContent(
                    'banner.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a0+8AAAAASUVORK5CYII=', true),
                ),
                'url' => 'javascript:alert(1)',
                'sort_order' => 1,
            ])
            ->assertSessionHasErrors('url');

        $this->assertDatabaseMissing('banners', ['title' => 'Unsafe destination']);
    }
}
