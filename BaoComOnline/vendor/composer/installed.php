<?php return array(
    'root' => array(
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => NULL,
        'name' => '__root__',
        'dev' => true,
    ),
    'versions' => array(
        '__root__' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => NULL,
            'dev_requirement' => false,
        ),
        'facebook/webdriver' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'php-webdriver/webdriver' => array(
            'pretty_version' => '1.13.1',
            'version' => '1.13.1.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../php-webdriver/webdriver',
            'aliases' => array(),
            'reference' => '6dfe5f814b796c1b5748850aa19f781b9274c36c',
            'dev_requirement' => false,
        ),
        'symfony/polyfill-mbstring' => array(
            'pretty_version' => 'v1.27.0',
            'version' => '1.27.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/polyfill-mbstring',
            'aliases' => array(),
            'reference' => '8ad114f6b39e2c98a8b0e3bd907732c207c2b534',
            'dev_requirement' => false,
        ),
        'symfony/process' => array(
            'pretty_version' => 'v6.0.11',
            'version' => '6.0.11.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/process',
            'aliases' => array(),
            'reference' => '44270a08ccb664143dede554ff1c00aaa2247a43',
            'dev_requirement' => false,
        ),
    ),
);
