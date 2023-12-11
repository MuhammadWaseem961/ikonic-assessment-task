<?php

    namespace App\Interfaces;
    use Illuminate\Http\Request;

    interface ResponseInterface{
        /**
         * success method
         */
        public function success($data=[],$message='');

        /**
         * error method
         */
        public function error($errors=[],$message='');


        

        
    }