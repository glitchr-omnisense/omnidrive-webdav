# omnidrive/webdav

A collection of a WebDAV server - Nextcloud, ownCloud, Apache's mod_dav, nginx, any - as an
[omniscient/omnidrive](https://github.com/glitchr-studio/omnidrive) drive, through
league/flysystem-webdav (`WebDAVAdapter`, sabre/dav's client over cURL). Nextcloud's and ownCloud's
collection of a user is `https://<host>/remote.php/dav/files/<user>/`.

## Installation

```sh
composer require omniscient/omnidrive omnidrive/webdav
```

Requires PHP 8.2 with cURL, `omniscient/omnidrive` (1.x) and `league/flysystem-webdav` (^3.0,
`sabre/dav`).

## Credentials

The server's account: `username` and `password` - on Nextcloud an application password (its
settings, Security). Sent only when `username` is set, with the `auth` scheme.

## Options

`new WebdavDriveFactory()`; factory name `webdav`.

| Option | Type | Default | Required | |
|---|---|---|---|---|
| `base_uri` | string | - | yes | the collection behind the drive's paths, an absolute address |
| `username`, `password` | string | none | no | |
| `auth` | `basic`, `digest`, `ntlm`, `any` | `basic` | no | the scheme asked first; `any` lets cURL find it at the cost of a request |
| `prefix` | string | `''` | no | every path under it |
| `proxy` | string | none | no | |
| `manual_copy`, `manual_move` | bool | `false` | no | a server without COPY or MOVE: the file read and written again |
| `public_url` | string or list | none | no | the prefix files are served from, or several (sharded) |
| `visibility` | `public`/`private` | ignored | no | a write's visibility when it does not say |
| `directory_visibility` | `public`/`private` | ignored | no | the same for directories |
| `retain_visibility` | bool | `true` | no | whether a copy keeps its source's visibility |

## Capabilities

| Capability | |
|---|---|
| `local` | no |
| `publicUrls` | yes: the file's WebDAV address (served to whoever the server serves it to - with credentials on Nextcloud), or `public_url` |
| `temporaryUrls` | no |
| visibility per file | no: a write's is ignored, reading or changing one is a `DriveException` |
| checksum | any algorithm `hash()` knows (the file downloaded and hashed) |

## Behaviour

`base_uri`'s origin (`scheme://host:port/`) is sabre's base address; its path goes into the
adapter's prefix, before `prefix`: a listing's addresses carry the whole path, which the adapter
strips only from its prefix. Reads and writes are `GET` and `PUT`, listings `PROPFIND`, directories
`MKCOL`, copies and moves `COPY` and `MOVE` (or read and written again with `manual_copy` /
`manual_move`). Nothing is sent until the first operation.

## Example

```php
<?php

require __DIR__.'/vendor/autoload.php';

use Omnidrive\Exception\DriveException;
use Omnidrive\Registry;
use Omnidrive\Webdav\WebdavDriveFactory;

$drives = new Registry([new WebdavDriveFactory()], [
    'cloud' => ['factory' => 'webdav', 'options' => ['base_uri' => 'http://webdav/', 'username' => 'omnidrive', 'password' => 'omnidrive-harness', 'prefix' => 'site']],
]);
$cloud = $drives->get('cloud');

$cloud->write('reports/2026-10.csv', "month,visits\n2026-10,1200\n", ['visibility' => 'private']);   // the visibility is ignored
echo json_encode($cloud->capabilities()->toArray()), "\n";
echo $cloud->publicUrl('reports/2026-10.csv'), "\n";
echo $cloud->size('reports/2026-10.csv'), ' ', $cloud->mimeType('reports/2026-10.csv'), "\n";
echo json_encode($cloud->metadata('reports/2026-10.csv')->toArray()['visibility']), "\n";
try {
    $cloud->visibility('reports/2026-10.csv');
} catch (DriveException $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}
$cloud->deleteDirectory('reports');
```

```
{"local":false,"publicUrls":true,"temporaryUrls":false}
http://webdav/site/reports/2026-10.csv
26 text/csv
null
Omnidrive\Exception\DriveException: The "cloud" drive could not read or set the visibility of "reports/2026-10.csv": Unable to retrieve the visibility for file at location: reports/2026-10.csv. WebDAV does not support this operation.
```

(run on 2026-10-25 in omnidrive's harness against its Apache mod_dav, basic authentication)

## Errors

The server's refusals are `DriveException`s; a missing file read is a `FileNotFoundException`
(asking a missing file's visibility too). An `auth` that is not one of the four, or a `base_uri`
that is not absolute, is an `InvalidConfigException`.

## Verified, and not

Verified (2026-10-24 and 2026-10-25, omnidrive's harness, PHP 8.4): the contract's tests against
Apache's mod_dav (bytemark/webdav, basic authentication) and, on 2026-10-24, against Nextcloud (the
nextcloud:apache image of that day, `--profile nextcloud`), a file's WebDAV address fetched with
the account's credentials; in plain PHP (`bare --live`). Not verified: ownCloud, nginx's WebDAV,
digest and NTLM authentication, a proxy.

## Responsibility

An independent, unofficial client, not affiliated with The PHP League, sabre/dav, Nextcloud or ownCloud. Whoever runs it is responsible for the data it stores and the
access it gives (a public or temporary address serves the file to whoever holds it); see the core's
Responsibility section ([omniscient/omnidrive](https://github.com/glitchr-studio/omnidrive#responsibility)).
