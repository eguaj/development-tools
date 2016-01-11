<?php

require_once "initializeAutoloader.php";

use Dcp\DevTools\Deploy\Deploy;
use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

const DEPLOY_CONFIG_FILE = 'deploy.json';

$getopt = new Getopt([
    (new Option('u', 'url', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('Dynacase Control url'),
    (new Option('p', 'port', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('Dynacase Control port')
        ->setDefaultValue(80),
    (new Option('c', 'context', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('name of the context on the target'),
    (new Option(null, 'action', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('action to execute (install|upgrade)')
        ->setValidation(
            function($action) {
                if('install' !== $action && 'upgrade' !== $action) {
                    print "$action is not a valid action.";
                    print "action must be either 'install' or 'upgrade'";
                    return false;
                }
                return true;
            }
        ),
    (new Option('w', 'webinst', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('webinst to deploy. If no webinst provided, a new one will be generated.'),
    (new Option(null, 'additional_args', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('additional arguments to pass to remote wiff command (like --nothing).'),
    (new Option('v', 'verbose', Getopt::NO_ARGUMENT)),
    (new Option(
        's', 'sourcePath', Getopt::REQUIRED_ARGUMENT
    ))
        ->setDescription('path of the module')
        ->setValidation(
            function ($path) {
                if (!is_dir($path)) {
                    print "$path is not a directory";
                    return false;
                }
                return true;
            }
        ),
    (new Option('h', 'help', Getopt::NO_ARGUMENT))
        ->setDescription(
            'show the usage message'
        )
]);
$getopt->setBanner("Usage: %s [options] -- [additional cli options]\n\nadditional cli options are passed directly to the remote wiff command.\n\n");

try {
    $getopt->parse();

    if(isset($getopt['help'])) {
        echo $getopt->getHelpText();
        exit();
    }

    if (!isset($getopt['sourcePath'])) {
        throw new UnexpectedValueException("You need to set the sourcepath of the application with -s or --sourcePath");
    }

    $options = $getopt->getOptions();
    $options['additional_args'] = $getopt->getOperands();

    $deployer = new Deploy($options);
    $result = $deployer->deploy();

    if (!$result['success']) {
        print "\nAn error occured on server.";
        print "\n--- script error:";
        print "\n    " . $result['error'];
        print "\n--- script messages:";
        foreach ($result['warnings'] as $message) {
            print "\n    " . $message;
        }
        if (isset($getopt['v']) && $getopt['v'] > 0
            && isset($result['data'])
        ) {
            print "\n--- raw output:";
            if (is_array($result['data'])) {
                foreach ($result['data'] as $out) {
                    print "\n    " . $out;
                }
            } else {
                print "\n    " . $result['data'];
            }
        }
    } else {
        print "\nsuccess";
        print "\n--- script messages:";
        foreach ($result['warnings'] as $message) {
            print "\n    " . $message;
        }
        if (isset($getopt['v']) && $getopt['v'] > 0
            && isset($result['data'])
        ) {
            print "\n--- raw output:";
            if (is_array($result['data'])) {
                foreach ($result['data'] as $out) {
                    print "\n    " . $out;
                }
            } else {
                print "\n    " . $result['data'];
            }
        }
    }
} catch (UnexpectedValueException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $getopt->getHelpText();
    exit(1);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}