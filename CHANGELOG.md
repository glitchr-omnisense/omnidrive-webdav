# Changelog

Every version of omnidrive/webdav, newest first ([Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
[Semantic Versioning](https://semver.org/)). A release's notes are its section here: the release
workflow refuses a tag without one.

## [Unreleased]

## [1.0.0] - 2026-11-13

The first stable version. It requires `omnisense/omnidrive` `^1.0` with no stability flag, so `composer require omnidrive/webdav` installs it in a project with Composer's default minimum-stability; the 1.x series follows semantic versioning. A tag `vX.Y.Z` is released by `.github/workflows/release.yml` once the package's CI passes on it.

Omnidrive WebDAV: a collection of a WebDAV server - Nextcloud, ownCloud, Apache mod_dav, any - as an omnidrive drive, through league/flysystem-webdav (sabre/dav) - basic or digest authentication, a prefix.
