<?php

declare(strict_types=1);

namespace Deployer;

use function Deployer\Support\escape_shell_argument;

set('domain', function () {
    return ask(' Domain: ', get('hostname'));
});

set('public_path', function () {
    return ask(' Public path: ', 'public');
});

desc('Provision website');
task('provision:website', function () {
    set('remote_user', get('provision_user'));

    $domainConf = parse(file_get_contents(__DIR__ . '/nginx/domain.conf'));

    run("echo $'$domainConf' > /etc/nginx/sites-available/{{domain}}.conf");

    if (test('[ -f /etc/nginx/sites-enabled/{{domain}}.conf ]')) {
        run('rm /etc/nginx/sites-enabled/{{domain}}.conf');
    }
    if (test('[ -f /etc/nginx/sites-enabled/default ]')) {
        run('rm /etc/nginx/sites-enabled/default');
    }

    run('ln -s /etc/nginx/sites-available/{{domain}}.conf /etc/nginx/sites-enabled');

    if (test('[ -f /etc/nginx/etc/nginx/dhparam.pem ]')) {
        run('openssl dhparam -out /etc/nginx/dhparam.pem 2048');
    }

    run('mkdir -p /var/www/_letsencrypt');

    run('chown www-data /var/www/_letsencrypt');

    $domain = get('domain');

    run('sed -i -r \'
        s/listen\\s+443 ssl http2;/listen 80;/g;
        s/listen\\s+\\[::\\]:443 ssl http2;/listen [::]:80;/g;
        s/^\\s*(ssl_certificate|ssl_certificate_key|ssl_trusted_certificate)/# \\0/g;
        s/return 301 https:\\/\\/\\{\\{domain\\}\\}\\$request_uri;/# \\0/g
    \' /etc/nginx/sites-available/{{domain}}.conf');

    run('nginx -t && systemctl reload nginx');

    // run("certbot certonly --webroot -d $domain --email info@$domain -w /var/www/_letsencrypt -n --agree-tos --force-renewal");

    run('sed -i -r \'
    s/listen\\s+80;/listen 443 ssl http2;/g;
    s/listen\\s+\\[::\\]:80;/listen [::]:443 ssl http2;/g;
    s/^\\s*#\\s*(ssl_certificate|ssl_certificate_key|ssl_trusted_certificate)/\\1/g;
    s/#\\s*return 301 https:\\/\\/\\{\\{domain\\}\\}\\$request_uri;/return 301 https:\\/\\/\\{\\{domain\\}\\}\\$request_uri;/g
    \' /etc/nginx/sites-available/{{domain}}.conf');

    run('nginx -t && systemctl reload nginx');

    run("echo -e '#!/bin/bash\nnginx -t && systemctl reload nginx' | sudo tee /etc/letsencrypt/renewal-hooks/post/nginx-reload.sh");
    run('chmod a+x /etc/letsencrypt/renewal-hooks/post/nginx-reload.sh');

    run('nginx -t && systemctl reload nginx');

    $restoreBecome = become('deployer');

    run("[ -d {{deploy_path}} ] || mkdir -p {{deploy_path}}");
    run("chown -R deployer:deployer {{deploy_path}}");

    set('deploy_path', run("realpath {{deploy_path}}"));

    $restoreBecome();

    info("Website {{domain}} configured!");
})->limit(1);
