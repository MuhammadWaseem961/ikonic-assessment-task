<?php
    namespace App\Repositories;

    use App\Interfaces\ResponseInterface;

    class ResponseRepository implements ResponseInterface
    {
        /**
         * success response
         */
        
        public function success($data=[],$message=''){
           return response()->json(['success'=>true,"message"=>$message,"data"=>$data]);
        }

        /**
         * error response
         */
        
        public function error($errors=[],$message=''){
            return response()->json(['success'=>false,"message"=>$message,"errors"=>$errors]);
        }

    }