<?php

namespace Deployer;

require __DIR__ . '/provision/php.php';
require __DIR__ . '/provision/user.php';
require __DIR__ . '/provision/setup-nginx.php';
require __DIR__ . '/provision/website.php';

desc('Provision the server');
task('provision', [
    'provision:check',
    'provision:configure',
    'provision:update',
    'provision:upgrade',
    'provision:install',
    'provision:ssh',
    'provision:firewall',
    'provision:setup-nginx',
    'provision:user',
    'provision:php',
    'provision:node',
    'provision:databases',
    'provision:composer',
    'provision:server',
    'provision:website',
    'provision:verify',
]);

desc('Adds repositories and update');
task('provision:update', function () {
    set('remote_user', get('provision_user'));

    // PHP
    run('apt-add-repository ppa:ondrej/php -y', ['env' => ['DEBIAN_FRONTEND' => 'noninteractive']]);

    // Update
    run('apt-get update', ['env' => ['DEBIAN_FRONTEND' => 'noninteractive']]);
})
    ->oncePerNode()
    ->verbose();

desc('Upgrades all packages');
task('provision:upgrade', function () {
    set('remote_user', get('provision_user'));
    run('apt-get upgrade -y', ['env' => ['DEBIAN_FRONTEND' => 'noninteractive'], 'timeout' => 900]);
})
    ->oncePerNode()
    ->verbose();

desc('Installs packages');
task('provision:install', function () {
    set('remote_user', get('provision_user'));
    $packages = [
        'acl',
        'apt-transport-https',
        'build-essential',
        'nginx',
        'certbot',
        'curl',
        'debian-archive-keyring',
        'debian-keyring',
        'fail2ban',
        'gcc',
        'git',
        'libmcrypt4',
        'libpcre3-dev',
        'libsqlite3-dev',
        'make',
        'ncdu',
        'nodejs',
        'pkg-config',
        'python-is-python3',
        'redis',
        'sendmail',
        'sqlite3',
        'ufw',
        'unzip',
        'uuid-runtime',
        'whois',
    ];
    run('apt-get install -y ' . implode(' ', $packages), ['env' => ['DEBIAN_FRONTEND' => 'noninteractive'], 'timeout' => 900]);
})
    ->verbose()
    ->oncePerNode();
