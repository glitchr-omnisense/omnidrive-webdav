# omnidrive/webdav

A collection of a WebDAV server - Nextcloud, ownCloud, Apache's mod_dav, nginx, any - as an
[omnidrive](https://github.com/glitchr-omnisense/omnidrive) drive, through league/flysystem-webdav
(sabre/dav's client, cURL).

```sh
composer require omnisense/omnidrive omnidrive/webdav
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

## Usage policy

`policy.json` (omnisense/contracts' schema, named in `composer.json` as `extra.omnisense.policy`;
read on 2026-11-10): No third party of the package's: the WebDAV server the site names (Apache,
Nextcloud...). No limit (`[]`); a rented server's operator's terms are the site's to add. The key is
the server's account. Nothing is retried. Nothing in the drive counts calls; [the family's
page](https://github.com/glitchr-omnisense/omnidrive/blob/1.x/docs/policies.md) has every
provider's.

## Responsibility

Unofficial; affiliated with none of the servers it reaches, nor with The PHP League, sabre/dav, Nextcloud or ownCloud. Each server keeps its own terms; whoever runs this package answers for the credentials it holds, the data it stores and the access a public address gives. MIT licence.
