<?php

namespace Omnidrive\Webdav\Tests;

use Omnidrive\Exception\DriveException;
use Omnidrive\Exception\InvalidConfigException;
use Omnidrive\Webdav\WebdavDriveFactory;
use PHPUnit\Framework\TestCase;

final class WebdavDriveTest extends TestCase
{
    public function testNothingConnectsUntilTheFirstOperation(): void
    {
        $drive = (new WebdavDriveFactory())->create(['base_uri' => 'http://192.0.2.1/dav', 'username' => 'site', 'password' => 'x'], 'cloud');

        self::assertSame('cloud', $drive->name());
        self::assertSame(['local' => false, 'publicUrls' => true, 'temporaryUrls' => false], $drive->capabilities()->toArray());
        self::assertSame('http://192.0.2.1/dav/a%20b/c.pdf', $drive->publicUrl('a b/c.pdf'), 'the file\'s WebDAV address, its path encoded');

        $nextcloud = (new WebdavDriveFactory())->create(['base_uri' => 'https://cloud.example.org:8443/remote.php/dav/files/Jane%20Doe/', 'prefix' => 'site']);
        self::assertSame('https://cloud.example.org:8443/remote.php/dav/files/Jane%20Doe/site/a.pdf', $nextcloud->publicUrl('a.pdf'), 'the base address\'s path decoded once, encoded once');
    }

    public function testAPublicAddressIsThePrefixGiven(): void
    {
        $drive = (new WebdavDriveFactory())->create(['base_uri' => 'https://cloud.example.org/remote.php/dav/files/site/', 'prefix' => 'uploads', 'public_url' => 'https://cdn.example.org']);

        self::assertSame('https://cdn.example.org/a/b.pdf', $drive->publicUrl('a/b.pdf'));
    }

    public function testAServerThatDoesNotAnswerIsADriveExceptionOfTheOperation(): void
    {
        $drive = (new WebdavDriveFactory())->create(['base_uri' => 'http://127.0.0.1:9/dav/'], 'cloud');

        $this->expectException(DriveException::class);
        $drive->read('a.txt');
    }

    public function testTheBaseAddressIsRequiredAndTheSchemeKnown(): void
    {
        try {
            (new WebdavDriveFactory())->create(['base_uri' => 'https://dav.example.org/', 'auth' => 'kerberos']);
            self::fail('an unknown scheme');
        } catch (InvalidConfigException $e) {
            self::assertStringContainsString('basic, digest, ntlm, any', $e->getMessage());
        }
        try {
            (new WebdavDriveFactory())->create(['base_uri' => '/remote.php/dav/']);
            self::fail('a relative address');
        } catch (InvalidConfigException $e) {
            self::assertStringContainsString('an absolute address', $e->getMessage());
        }
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('The "webdav" drive needs: base_uri.');
        (new WebdavDriveFactory())->create([]);
    }
}
