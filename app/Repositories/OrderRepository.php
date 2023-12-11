<?php
    namespace App\Repositories;

    use App\Interfaces\EloquentRepositoryInterface;
    use App\Models\Order;
    use Validator;

    class OrderRepository implements EloquentRepositoryInterface
    {
        /**
         * @var $model object of mod
         */
        public $model;

        /**
            * OrderRepository constructor.
            *
            * @param Order $model
            */
        public function __construct(Affiliate $model)
        {
            $this->model = $model;
        }

        /**
         * create order
         */
        
        public function store($data){
           return $this->model->create($data);
        }

        /**
         * update order
         */
        
        public function update($data,$id){
            return $this->model->find($id)->update($data);
        }

        /**
         * data validations
         */
        public function validations($data,$rules){
            $validations = Validator::make($data,$rules);
            if($validations->fails()){
                return ['success'=>false,"errors"=>$validations->errors()];
            }
            return ['success'=>true];
        }

    }