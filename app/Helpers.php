<?php
    if(!function_exists('getPathOf')){
        function getPathOf($key){
            return config('paths.'.$key);
        }
    }