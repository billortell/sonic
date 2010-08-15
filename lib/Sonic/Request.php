<?php
namespace Sonic;
use \Sonic\Exception;

/**
 * Request object - you know, for handling $_GET, $_POST, and other params
 *
 * @package Sonic
 * @subpackage Request
 * @author Craig Campbell
 */
class Request
{
    /**
     * @var string
     */
    const POST = 'POST';

    /**
     * @var string
     */
    const GET = 'GET';

    /**
     * @var string
     */
    const PARAM = 'PARAM';

    /**
     * @var array
     */
    protected $_caches = array();

    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var Router
     */
    protected $_router;

    /**
     * @var Controller
     */
    protected $_controller;

    /**
     * @var string
     */
    protected $_controller_name;

    /**
     * @var string
     */
    protected $_action;

    /**
     * gets the base uri for the request
     * for example /profile?id=25 would return "/profile"
     *
     * @return string
     */
    public function getBaseUri()
    {
        if (isset($this->_caches['base_uri'])) {
            return $this->_caches['base_uri'];
        }

        // if redirect url is present use that to avoid extra processing
        if (($uri = $this->getServer('REDIRECT_URL')) !== null) {
            $this->_caches['base_uri'] = $uri == '/' ? $uri : rtrim($uri, '/');
            return $this->_caches['base_uri'];
        }

        $bits = explode('?', $this->getServer('REQUEST_URI'));
        $this->_caches['base_uri'] = $bits[0] == '/' ? $bits[0] : rtrim($bits[0], '/');

        return $this->_caches['base_uri'];
    }

    /**
     * gets a server param
     *
     * @param string $name
     * @return mixed
     */
    public function getServer($name)
    {
        if (!isset($_SERVER[$name])) {
            return null;
        }

        return $_SERVER[$name];
    }

    /**
     * gets the router object
     *
     * @return Router
     */
    public function getRouter()
    {
        if ($this->_router === null) {
            $this->_router = new Router($this);
        }

        return $this->_router;
    }

    /**
     * gets the controller name from the router after the routes have been processed
     *
     * @return string
     */
    public function getControllerName()
    {
        if ($this->_controller_name !== null) {
            return $this->_controller_name;
        }

        $this->_controller_name = $this->getRouter()->getController();
        if (!$this->_controller_name) {
            throw new Exception('page not found at ' . $this->getBaseUri(), EXCEPTION::NOT_FOUND);
        }

        return $this->_controller_name;
    }

    /**
     * gets the action name from the Router after the routes have been processed
     *
     * @return string
     */
    public function getAction()
    {
        if ($this->_action !== null) {
            return $this->_action;
        }

        $this->_action = $this->getRouter()->getAction();
        if (!$this->_action) {
            throw new Exception('page not found at ' . $this->getBaseUri(), EXCEPTION::NOT_FOUND);
        }

        return $this->_action;
    }

    /**
     * adds request params
     *
     * @param array
     * @return void
     */
    public function addParams(array $params) {
        foreach ($params as $key => $value) {
            $this->addParam($key, $value);
        }
    }

    /**
     * adds a single request param
     *
     * @param string $key
     * @param mixed $value
     * @return Request
     */
    public function addParam($key, $value)
    {
        $this->_params[$key] = $value;
        return $this;
    }

    /**
     * gets a param from the request
     *
     * @param string $name parameter name
     * @param string $type (GET || POST || PARAM)
     * @return mixed
     */
    public function getParam($name, $type = self::PARAM)
    {
        switch ($type) {
            case self::POST:
                if (isset($_POST[$name])) {
                    return $_POST[$name];
                }
                break;
            case self::GET:
                if (isset($_GET[$name])) {
                    return $_GET[$name];
                }
                break;
            default:
                if (isset($this->_params[$name])) {
                    return $this->_params[$name];
                }
                break;
        }

        return null;
    }

    /**
     * gets all params of a certain type
     *
     * @param string $type
     * @return array
     */
    public function getParams($type = self::PARAM)
    {
        if ($type === self::POST) {
            return $_POST;
        }

        if ($type === self::GET) {
            return $_GET;
        }

        return $this->_params;
    }

    /**
     * gets posted value or all of post
     *
     * @param string
     * @return mixed
     */
    public function getPost($name = null)
    {
        if ($name === null) {
            return $this->getParams(self::POST);
        }
        return $this->getParam($name, self::POST);
    }

    /**
     * was this a post request
     *
     * @return bool
     */
     public function isPost()
     {
         return $this->getServer('REQUEST_METHOD') == 'POST';
     }

    /**
     * was this an ajax request
     *
     * @return bool
     */
    public function isAjax()
    {
        return $this->getServer('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest';
    }
}
