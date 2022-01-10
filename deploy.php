<?php

namespace Deployer;

// Include the Laravel & rsync recipes
require 'recipe/laravel.php';
require 'recipe/rsync.php';
require 'recipe/common.php';


// add('recipes', ['laravel']);



function artisan($command, $options = [])
{
    return function () use ($command, $options) {

        // Ensure the artisan command is available on the current version.
        $versionTooEarly = array_key_exists('min', $options)
            && laravel_version_compare($options['min'], '<');

        $versionTooLate = array_key_exists('max', $options)
            && laravel_version_compare($options['max'], '>');

        if ($versionTooEarly || $versionTooLate) {
            return;
        }

        // Ensure we warn or fail when a command relies on the ".env" file.
        if (in_array('failIfNoEnv', $options) && !test('[ -s {{release_or_current_path}}/.env ]')) {
            throw new \Exception('Your .env file is empty! Cannot proceed.');
        }

        if (in_array('skipIfNoEnv', $options) && !test('[ -s {{release_or_current_path}}/.env ]')) {
            warning("Your .env file is empty! Skipping...</>");
            return;
        }

        $artisan = '{{release_or_current_path}}/artisan';

        // Run the artisan command.
        $output = run("{{bin/php}} $artisan $command");

        // Output the results when appropriate.
        if (in_array('showOutput', $options)) {
            writeln("<info>$output</info>");
        }
    };
}

function laravel_version_compare($version, $comparator)
{
    return version_compare(get('laravel_version'), $version, $comparator);
}


set('writable_use_sudo', false);

set('application', 'clafiya-api');
set('ssh_multiplexing', true); // Speed up deployment
set('keep_releases', 5);
set('rsync_src', function () {
    return __DIR__; // If your project isn't in the root, you'll need to change this.
});

set('shared_files', ['.env']);
set('shared_dirs', [ // Shared dirs
    'storage/app',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
]);

// Configuring the rsync exclusions.
// You'll want to exclude anything that you don't want on the production server.
add('rsync', [
 'exclude' => [
            '.git',
            '/.env',
            '/storage/framework/',
            '/storage/logs/',
            '/vendor/',
            '/node_modules/',
            '.gitlab-ci.yml',
            'deploy.php',
        ],
]);

// Set up a deployer task to copy secrets to the server.
// Grabs the dotenv file from the github secret
task('deploy:secrets', function () {
    file_put_contents(__DIR__ . '/.env', getenv('DOT_ENV'));
    upload('.env', get('deploy_path') . '/shared');
});


// Hosts
host('clafiya-api') // Name of the server
    ->hostname('137.184.142.235') // Hostname or IP address
    ->stage('production') // Deployment stage (production, staging, etc)
    ->user('root') // SSH user
    ->set('deploy_path', '/var/www/html/clafiya'); // Deploy path



after('deploy:failed', 'deploy:unlock'); // Unlock after failed deploy

set('shared_files', ['.env']); // Shared Files
set('writable_dirs', ['storage', 'vendor']); // Chmod stuff


desc('Deploy the application');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'rsync', // Deploy code & built assets
    'deploy:secrets', // Deploy secrets
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',

//     'artisan:passport:install',
    'artisan:storage:link', // |
    'artisan:view:cache',   // |
    'artisan:config:cache', // | Laravel specific steps
    'artisan:optimize',     // |
    'artisan:migrate',      // |
    // 'artisan:passport:keys​', // For Passport

    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);

task('artisan:passport:keys', artisan('passport:keys'));
