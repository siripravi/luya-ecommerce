<?php

use luya\Config;

$config = new Config('myproject', dirname(__DIR__), [
    'siteTitle' => 'My Project',
    'defaultRoute' => 'cms',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        // See all options: https://luya.io/api/luya-admin-Module
        'admin' => [
            'class' => 'luya\admin\Module',
            'secureLogin' => false, // when enabling secure login, the mail component must be proper configured otherwise the auth token mail will not send.
            'strongPasswordPolicy' => false, // If enabled, the admin user passwords require strength input with special chars, lower, upper, digits and numbers
            'interfaceLanguage' => 'en', // Admin interface default language.
            'autoBootstrapQueue' => true, // Enables the fake cronjob by default, read more about queue/scheduler: https://luya.io/guide/app-queue
        ],
        // See all frontend CMS options: https://luya.io/api/luya-cms-frontend-Module
        'cms' => [
            'class' => 'luya\cms\frontend\Module',
            'contentCompression' => true, // compressing the cms output (removing white spaces and newlines)
        ],
        // See all admin CMS options: https://luya.io/api/luya-cms-admin-Module
        'cmsadmin' => [
            'class' => 'luya\cms\admin\Module',
        ],
        'ecommerce' => 'siripravi\ecommerce\frontend\Module',
        'ecommerceadmin' => 'siripravi\ecommerce\admin\Module',
        'galleryadmin' => 'luya\gallery\admin\Module',
        'cartadmin' => 'siripravi\shopcart\admin\Module',
        'user' => [
            'class' => 'siripravi\authhelper\Module',
            //   'layout' => '@app/themes/cakeBaker/views/layouts/auth',
            'modelMap' => [
                'RegistrationForm' => \siripravi\authhelper\models\RegistrationForm::class,
                'RecoveryForm' => \siripravi\authhelper\models\RecoveryForm::class,
                'LoginForm' => \siripravi\authhelper\models\LoginForm::class,
                'SettingsForm' => \siripravi\authhelper\models\SettingsForm::class,
                'Profile' => \siripravi\authhelper\models\Profile::class,
                'User' => \siripravi\authhelper\models\User::class,

            ],
            'controllerMap' => [
                'registration' => \siripravi\authhelper\controllers\RegistrationController::class,
                'settings' => \siripravi\authhelper\controllers\SettingsController::class,
                'security' => \siripravi\authhelper\controllers\SecurityController::class,
                'recovery' => \siripravi\authhelper\controllers\RecoveryController::class
            ],
        ],
        'api' => [
            'class' => 'luya\headless\cms\api\Module',
        ],
        'forms' => [
            'class' => 'luya\forms\Module',
            // 'useAppViewPath' => true,
            //'viewMap' => ['block/*' =>'@app/views/blocks/']

        ],
    ],
    'components' => [
        // https://www.yiiframework.com/doc/api/2.0/yii-db-connection
        'db' => [
            'class' => 'yii\db\Connection',
            'charset' => 'utf8',
        ],
        'jwt' => [
            'class' => 'luya\admin\components\Jwt',
            'key' => 'MySecretJwtKey',
            'apiUserEmail' => 'jwtapiuser@luya.io',
            'identityClass' => 'app\modules\userauth\models\ApiUser',
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => app\models\User::class,
            'enableAutoLogin' => true,
            'enableSession' => true,
            'identityCookie' => [
                'name'     => '_frontendIdentity',
                'path'     => '/',
                'httpOnly' => true,
            ],
            'on afterLogin' => function () {
                //  if (Yii::$app->cart->saveToDataBase) Yii::$app->cart->transportSessionDataToDB();
            },
            'on afterConfirm' => function () {
                //  if (Yii::$app->cart->saveToDataBase) Yii::$app->cart->transportSessionDataToDB();
            },
        ],
        'forms' => [
            'class' => 'app\components\Forms'
        ],
        // https://luya.io/guide/luya-mail
        'mail' => [
            'isSMTP' => false,
            'from' => 'test@luya.io',
            'fromName' => 'test@luya.io',
        ],
        /*
         * The composition component handles your languages and they way your urls will look like. The composition components will
         * automatically add the language prefix which is defined in `default` to your url (the language part in the url  e.g. "yourdomain.com/en/homepage").
         *
         * hidden: (boolean) If this website is not multi lingual you can hide the composition, other whise you have to enable this.
         * default: (array) Contains the default setup for the current language, this must match your language system configuration.
         * 
         * see https://luya.io/guide/concept-composition
         */
        'composition' => [
            'hidden' => true, // no languages in your url (most case for pages which are not multi lingual)
            'default' => ['langShortCode' => 'en'], // the default language for the composition should match your default language shortCode in the language table.
        ],
        // I18n Guide https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                ],
            ],
        ],
    ]
]);

/************ LOCAL ************/

$config->env(Config::ENV_LOCAL, function (Config $config) {
    // ensure Yii constants are set correctly
    $config->callback(function () {
        define('YII_DEBUG', true);
        define('YII_ENV', 'local');
    });

    // Some example for MAMP on OSX, Windows or by default with Docker
    // 'dsn' => 'mysql:host=localhost;dbname=DB_NAME',
    // 'dsn' => 'mysql:host=localhost;dbname=DB_NAME;unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock', // OSX MAMP
    // 'dsn' => 'mysql:host=localhost;dbname=DB_NAME;unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock', // OSX XAMPP
    $config->component('db', [
        'dsn' => 'mysql:host=localhost;dbname=luya_ecommerce',
        'username' => 'root',
        'password' => '',
    ]);

    // Yii Debug Toolbar
    // Info: When working with yii\web\Users configuration and the Debug Toolbar is enabled, it will logout the admin users)
    // @see https://github.com/luyadev/luya/issues/1854
    $config->module('debug', [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*'],
    ]);
    $config->module('gii', [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'],
    ]);
    // bootstrap debug and gii
    $config->bootstrap(['debug', 'gii']);
});

/************ PROD ************/

$config->env(Config::ENV_PROD, function (Config $config) {
    // database setup
    $config->component('db', [
        'dsn' => 'mysql:host=localhost;dbname=DB_NAME',
        'username' => '',
        'password' => '',
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 0,
    ]);
    // caching
    $config->component('cache', [
        'class' => 'yii\caching\FileCache'
    ]);

    $config->application([
        'ensureSecureConnection' => true, // https://luya.io/guide/app-security
    ]);
});

return $config;
