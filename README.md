# omnidrive/webdav

A collection of a WebDAV server - Nextcloud, ownCloud, Apache's mod_dav, nginx, any - as an
[omnidrive](https://github.com/glitchr-studio/omnidrive) drive, through league/flysystem-webdav
(sabre/dav's client, cURL).

```sh
composer require omniscient/omnidrive omnidrive/webdav
```

```php
use Omnidrive\Registry;
use Omnidrive\Webdav\WebdavDriveFactory;

$drives = new Registry([new WebdavDriveFactory()], [
    'cloud' => ['factory' => 'webdav', 'options' => ['base_uri' => 'https://cloud.example.org/remote.php/dav/files/site/', 'username' => 'site', 'password' => getenv('NEXTCLOUD_APP_PASSWORD'), 'prefix' => 'uploads']],
]);
```

| Option | Default | |
|---|---|---|
| `base_uri` | required | the collection behind the drive's paths; Nextcloud's and ownCloud's is `https://<host>/remote.php/dav/files/<user>/` |
| `username`, `password` | | an application password on Nextcloud |
| `auth` | `basic` | `basic`, `digest`, `ntlm`, or `any` (cURL finds it, at the cost of a request) |
| `prefix` | | every path under it |
| `proxy` | | |
| `manual_copy`, `manual_move` | false | a server without COPY or MOVE: the file read and written again |
| `public_url` | the file's WebDAV address | |
| `visibility`, `directory_visibility`, `retain_visibility` | | as every drive |

The base address's path goes into the adapter's prefix and sabre's client is given the server's
origin: a listing's addresses carry the whole path, which the adapter strips only from its prefix.
WebDAV keeps no visibility: a write's is ignored, reading or changing one is a `DriveException`.
`capabilities()`: not local, public addresses (the file's WebDAV address, served to whoever the
server serves it to - with credentials on Nextcloud), no temporary address. Nothing is sent until
the first operation.

## Verified, and not

- Verified (2026-10-24, omnidrive's harness, PHP 8.4): the contract's tests against Apache's mod_dav
  (bytemark/webdav, basic authentication) and against Nextcloud (the nextcloud:apache image of that
  day, SQLite, the admin's password; `--profile nextcloud` in the harness), a file's WebDAV address
  fetched with the account's credentials; in plain PHP (`bare --live`); a server that does not
  answer is a `DriveException` of the operation asked.
- Not verified: ownCloud, nginx's WebDAV, digest and NTLM authentication, a proxy.

License: MIT. Unofficial; not affiliated with The PHP League, sabre/dav, Nextcloud or ownCloud.
