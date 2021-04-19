<?php


if (!function_exists('application_path')) {
    function application_path($app = '')
    {
        return base_path('applications/' . ucfirst($app));
    }
}
