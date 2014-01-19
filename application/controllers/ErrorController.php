<?php

class ErrorController extends Zend_Controller_Action
{

    public function init()
    {
        $assetsDomain = Zend_Registry::get('config')->assetsDomain;

        $this->view->headScript()->appendFile($assetsDomain . '/js/vendor/modernizr-2.6.2.min.js');

        if (APPLICATION_ENV != 'development') {
            $this->view->headScript()->appendFile($assetsDomain . '/js/track.js');
        }

        $this->view->headLink()->appendStylesheet($assetsDomain . '/css/normalize.css');
        $this->view->headLink()->appendStylesheet($assetsDomain . '/css/h5bp.css');
        $this->view->headLink()->appendStylesheet($assetsDomain . '/css/main.css');
        $this->view->headLink()->appendStylesheet($assetsDomain . '/css/sizes.css');
    }

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // TODO: create a normal 404 page
                header('Location: /');
                die();

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';
                break;
        }

        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->log($this->view->message, $priority, $errors->exception);
            $log->log('Request Parameters', $priority, $errors->request->getParams());
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request = $errors->request;
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }
}