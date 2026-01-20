<?php

namespace Tests\Feature;

use App\Http\Controllers\StorageController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected StorageController $storageController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storageController = new StorageController();
        Storage::fake('public');
    }

    public function test_store_image_saves_valid_file()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1200);

        $result = $this->storageController->store_image($file, 'test-folder');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('', $result);
        $this->assertNotNull($result['']);
    }

    public function test_store_image_creates_multiple_versions()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1200);

        $result = $this->storageController->store_image($file, 'test-folder');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('-banner', $result);
        $this->assertArrayHasKey('-medium', $result);
        $this->assertArrayHasKey('-small', $result);
        $this->assertArrayHasKey('-cropped', $result);
    }

    public function test_store_image_fails_with_invalid_file()
    {
        $result = $this->storageController->store_image(null, 'test-folder');

        $this->assertNull($result);
    }

    public function test_store_image_saves_with_avif_extension()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1200);

        $result = $this->storageController->store_image($file, 'test-folder');

        $this->assertStringContainsString('.avif', $result['']);
    }

    public function test_store_image_creates_month_year_directory()
    {
        $file = UploadedFile::fake()->image('test.jpg', 1200);
        $expectedPrefix = date('FY');

        $result = $this->storageController->store_image($file, 'test-folder');

        $this->assertStringContainsString($expectedPrefix, $result['']);
    }
}
