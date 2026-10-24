<?php

namespace Omnidrive\Webdav;

use League\Flysystem\WebDAV\WebDAVAdapter;
use Omnidrive\Config;
use Omnidrive\Drive;
use Omnidrive\Exception\InvalidConfigException;
use Omnidrive\Flysystem\FlysystemDriveFactory;
use Sabre\DAV\Client;

/**
 * A collection of a WebDAV server - Nextcloud, ownCloud, Apache's mod_dav,
 * nginx, any - through league/flysystem-webdav (sabre/dav's client, cURL):
 *
 *   (new WebdavDriveFactory())->create(['base_uri' => 'https://cloud.example.org/remote.php/dav/files/site/', 'username' => 'site', 'password' => '...']);
 *
 * Options: base_uri (required: the collection behind the drive's paths, with
 * its trailing slash; Nextcloud's and ownCloud's is
 * https://<host>/remote.php/dav/files/<user>/), username and password (an
 * application password on Nextcloud), auth (basic, digest, ntlm or any: the
 * scheme asked first - "any" lets cURL find it at the cost of a request), prefix
 * (every path under it), proxy, manual_copy and manual_move (a server that does
 * not do COPY or MOVE: the file read and written again), public_url (else the
 * file's WebDAV address, served to whoever the server serves it to).
 *
 * WebDAV keeps no visibility of its own: writing with one is accepted and
 * ignored, reading or changing it is a DriveException. Nothing is sent until
 * the first operation.
 */
final class WebdavDriveFactory extends FlysystemDriveFactory
{
    private const AUTH = ['basic' => Client::AUTH_BASIC, 'digest' => Client::AUTH_DIGEST, 'ntlm' => Client::AUTH_NTLM, 'any' => Client::AUTH_BASIC | Client::AUTH_DIGEST | Client::AUTH_NTLM];

    protected function populate(Config $c): void
    {
        $c->defaults([
            'omnidrive.factory_name' => 'webdav',
            'omnidrive.factory_title' => 'A WebDAV server (Nextcloud, ownCloud...)',
            'omnidrive.required_options' => ['base_uri'],
            'auth' => 'basic',
            'prefix' => '',
            'manual_copy' => false,
            'manual_move' => false,
        ]);
    }

    protected function build(Config $c, string $name): Drive
    {
        $auth = strtolower((string) $c->string('auth'));
        if (!isset(self::AUTH[$auth])) {
            throw new InvalidConfigException(\sprintf('The "webdav" drive\'s "auth" is one of %s, not "%s".', implode(', ', array_keys(self::AUTH)), $auth));
        }
        // sabre's client is given the server's origin, the base address's path goes into the
        // prefix: the adapter strips the prefix from the addresses a listing answers with, which
        // carry the whole path (Nextcloud's /remote.php/dav/files/<user>/...).
        $base = (string) $c->string('base_uri');
        $parts = parse_url($base);
        if (!\is_array($parts) || !isset($parts['scheme'], $parts['host'])) {
            throw new InvalidConfigException(\sprintf('The "webdav" drive\'s base_uri is an absolute address, not "%s".', $base));
        }
        $origin = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '').'/';
        $prefix = implode('/', array_filter([trim(rawurldecode($parts['path'] ?? ''), '/'), trim((string) $c->string('prefix'), '/')], static fn (string $p) => '' !== $p));
        $settings = ['baseUri' => $origin];
        if (null !== $user = $c->string('username')) {
            $settings += ['userName' => $user, 'password' => (string) $c->string('password'), 'authType' => self::AUTH[$auth]];
        }
        if (null !== $proxy = $c->string('proxy')) {
            $settings['proxy'] = $proxy;
        }

        $adapter = new WebDAVAdapter(
            new Client($settings),
            $prefix,
            WebDAVAdapter::ON_VISIBILITY_THROW_ERROR,
            $c->bool('manual_copy'),
            $c->bool('manual_move'),
        );

        return $this->drive($name, $adapter, $c);
    }
}
