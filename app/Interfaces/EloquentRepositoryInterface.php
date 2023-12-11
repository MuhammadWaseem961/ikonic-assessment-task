<?php

    namespace App\Interfaces;

    interface EloquentRepositoryInterface{
        
         /**
         * store model record
         */
        public function store($data);

        /**
         * update model record
         */
        public function update($data,$id);

        /**
         * data validations
         */
        public function validations($data,$rules);
        
    }