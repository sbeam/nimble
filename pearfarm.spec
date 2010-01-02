<?php

$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
             ->setName('nimble')
             ->setChannel('jetviper21.pearfarm.org')
             ->setSummary('Small Lightweight MVC framework')
             ->setDescription('Small Lightweight MVC framework based on nicedog')
             ->setReleaseVersion('0.0.1')
             ->setReleaseStability('alpha')
             ->setApiVersion('0.0.1')
             ->setApiStability('alpha')
             ->setLicense(Pearfarm_PackageSpec::LICENSE_MIT)
             ->setNotes('Initial release.')
             ->addMaintainer('lead', 'Scott Davis', 'jetviper21', 'jetviper21@gmail.com')
             ->addMaintainer('lead', 'John Bintz', 'johnbintz', 'john@coswellproductions.com')
             ->addGitFiles()
             ->addExecutable('bin/nimble')
             ;