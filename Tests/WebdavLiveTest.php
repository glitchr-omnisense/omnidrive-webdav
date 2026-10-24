<?php

namespace Omnidrive\Webdav\Tests;

use Omnidrive\Drive;
use Omnidrive\Testing\DriveContractTests;
use Omnidrive\Webdav\WebdavDriveFactory;
use PHPUnit\Framework\TestCase;

/**
 * The contract against a real WebDAV server: the harness's Apache mod_dav
 * (docker compose run --rm omnidrive test), or the one OMNIDRIVE_WEBDAV_* name
 * (a Nextcloud: https://<host>/remote.php/dav/files/<user>/ and an application
 * password). Skipped when none is named.
 */
final class WebdavLiveTest extends TestCase
{
    use DriveContractTests;

    protected function keepsVisibility(): bool
    {
        return false;
    }

    protected function drive(): Drive
    {
        $uri = getenv('OMNIDRIVE_WEBDAV_URI');
        if (false === $uri || '' === $uri) {
            self::markTestSkipped('No WebDAV server named (OMNIDRIVE_WEBDAV_URI).');
        }

        return (new WebdavDriveFactory())->create([
            'base_uri' => $uri,
            'username' => getenv('OMNIDRIVE_WEBDAV_USERNAME') ?: null,
            'password' => getenv('OMNIDRIVE_WEBDAV_PASSWORD') ?: null,
            'auth' => getenv('OMNIDRIVE_WEBDAV_AUTH') ?: 'basic',
        ], 'webdav');
    }

    public function testTheFilesAddressIsServedWithTheServersCredentials(): void
    {
        $drive = $this->drive();
        $drive->write('served/hello.txt', 'over WebDAV');
        $user = (string) getenv('OMNIDRIVE_WEBDAV_USERNAME');
        $context = stream_context_create(['http' => ['header' => 'Authorization: Basic '.base64_encode($user.':'.getenv('OMNIDRIVE_WEBDAV_PASSWORD'))]]);

        try {
            self::assertSame('over WebDAV', @file_get_contents($drive->publicUrl('served/hello.txt'), false, '' === $user ? null : $context));
        } finally {
            $drive->deleteDirectory('served');
        }
    }
}
