<?php

/*
| -------------------------------------------------------------------------
| Sentry
| -------------------------------------------------------------------------
*/

//Do not edit if possible, since this is the default setting.
$config['sentry_path'] = BASEPATH . '../vendor/sentry/sentry/lib/Raven/Autoloader.php';
$config['sentry_logging_levels'] = array('INFO', 'WARNING', 'DEBUG', 'ERROR', 'FATAL');
$config['sentry_logging_level'] = 4 - $config['log_threshold'];
$config['sentry_log_threshold'] = $config['sentry_logging_levels'][$config['sentry_logging_level']];
$config['sentry_enviroments'] = ENVIRONMENT;

$config['sentry_client'] = 'https://b0c54bcb06b24725bdf726b65420fe94:8ef74e734fa34234a469a22e0203ac4f@sentry.io/124965';
$config['sentry_config'] = array();
