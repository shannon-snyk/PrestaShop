<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use PrestaShop\PrestaShop\Adapter\ContainerBuilder;

ob_start();

require_once dirname(__FILE__) . '/../config/config.inc.php';

global $kernel;
if (null === $kernel) {
    $kernel = new AppKernel(_PS_ENV_, _PS_MODE_DEV_);
    $kernel->boot();
}

// Cart is needed for some requests
Context::getContext()->cart = new Cart();
Context::getContext()->container = ContainerBuilder::getContainer('webservice', _PS_MODE_DEV_);
Context::getContext()->currency = Context::getContext()->currency ?? new Currency((int) Configuration::get('PS_CURRENCY_DEFAULT'));

//set http auth headers for apache+php-cgi work around
if (isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Basic\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
    list($name, $password) = explode(':', base64_decode($matches[1]));
    $_SERVER['PHP_AUTH_USER'] = strip_tags($name);
}

//set http auth headers for apache+php-cgi work around if variable gets renamed by apache
if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && preg_match('/Basic\s+(.*)$/i', $_SERVER['REDIRECT_HTTP_AUTHORIZATION'], $matches)) {
    list($name, $password) = explode(':', base64_decode($matches[1]));
    $_SERVER['PHP_AUTH_USER'] = strip_tags($name);
}

// Use for image management (using the POST method of the browser to simulate the PUT method)
$method = $_REQUEST['ps_method'] ?? $_SERVER['REQUEST_METHOD'];

if (isset($_SERVER['PHP_AUTH_USER'])) {
    $key = $_SERVER['PHP_AUTH_USER'];
} elseif (isset($_GET['ws_key'])) {
    $key = $_GET['ws_key'];
} else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Welcome to PrestaShop Webservice, please enter the authentication key as the login. No password required."');
    die('401 Unauthorized');
}

$input_xml = null;

// if a XML is in PUT or in POST
if (($_SERVER['REQUEST_METHOD'] == 'PUT') || ($_SERVER['REQUEST_METHOD'] == 'POST')) {
    $putresource = fopen('php://input', 'rb');
    while ($putData = fread($putresource, 1024)) {
        $input_xml .= $putData;
    }
    fclose($putresource);
}
if (isset($input_xml) && strncmp($input_xml, 'xml=', 4) == 0) {
    $input_xml = substr($input_xml, 4);
}

$params = $_GET;
unset($params['url']);

$class_name = WebserviceKey::getClassFromKey($key);
$bad_class_name = false;
if (!class_exists($class_name)) {
    $bad_class_name = $class_name;
    $class_name = 'WebserviceRequest';
}
// fetch the request
WebserviceRequest::$ws_current_classname = $class_name;
$request = call_user_func([$class_name, 'getInstance']);

$result = $request->fetch($key, $method, $_GET['url'], $params, $bad_class_name, $input_xml);

// display result
if (ob_get_length() != 0) {
    header('Content-Type: application/javascript');
} // Useful for debug...

// Manage cache
if (isset($_SERVER['HTTP_LOCAL_CONTENT_SHA1']) && $_SERVER['HTTP_LOCAL_CONTENT_SHA1'] == $result['content_sha1']) {
    $result['status'] = $_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified';
}

if (is_array($result['headers'])) {
    foreach ($result['headers'] as $param_value) {
        header($param_value);
    }
}
if (isset($result['type'])) {
    //	header($result['content_sha1']);
    if (!isset($_SERVER['HTTP_LOCAL_CONTENT_SHA1']) || $_SERVER['HTTP_LOCAL_CONTENT_SHA1'] != $result['content_sha1']) {
        echo $result['content'];
    }
}
ob_end_flush();
