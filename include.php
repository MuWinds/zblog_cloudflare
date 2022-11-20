<?php
define('zblogofcloudflare_Version', '1.0');

require_once dirname(__FILE__) . '/config/cdn.php';
require_once dirname(__FILE__) . '/includes/plugin_function.php';
RegisterPlugin("zblogofcloudflare","ActivePlugin_zblogofcloudflare");
global $zbp;