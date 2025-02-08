<?php

namespace Deployer;


desc('Sets up Nginx');
task('provision:setup-nginx', function () {
    set('remote_user', get('provision_user'));

    run('systemctl enable nginx');
    run('systemctl start nginx');

    $nginxConf = file_get_contents(__DIR__ . '/nginx/nginx.conf');

    run("echo $'$nginxConf' > /etc/nginx/nginx.conf");

    run("mkdir -p /etc/nginx/nginxconfig.io");

    $generalConf = file_get_contents(__DIR__ . '/nginx/nginxconfig.io/general.conf');
    $letsencryptConf = file_get_contents(__DIR__ . '/nginx/nginxconfig.io/letsencrypt.conf');
    $fastcgiConf = file_get_contents(__DIR__ . '/nginx/nginxconfig.io/php_fastcgi.conf');
    $securityConf = file_get_contents(__DIR__ . '/nginx/nginxconfig.io/security.conf');

    run("echo $'$generalConf' > /etc/nginx/nginxconfig.io/general.conf");
    run("echo $'$letsencryptConf' > /etc/nginx/nginxconfig.io/letsencrypt.conf");
    run("echo $'$fastcgiConf' > /etc/nginx/nginxconfig.io/php_fastcgi.conf");
    run("echo $'$securityConf' > /etc/nginx/nginxconfig.io/security.conf");

    run('mkdir -p /etc/nginx/sites-available');
    run('mkdir -p /etc/nginx/sites-enabled');

    run('chmod 644 -R /etc/nginx/sites-available');
    run('chmod 644 -R /etc/nginx/sites-enabled');
    run('chmod 644 -R /etc/nginx/nginxconfig.io');
    run('chmod 644 -R /etc/nginx/nginxconfig.io');
    run('chmod 644 /etc/nginx/nginx.conf');
})
    ->oncePerNode()
    ->verbose();
