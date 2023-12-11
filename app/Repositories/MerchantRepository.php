<?php
    namespace App\Repositories;

    use App\Interfaces\EloquentRepositoryInterface;
    use App\Models\Merchant;
    use Validator;

    class MerchantRepository implements EloquentRepositoryInterface
    {
        /**
         * @var $model object of mod
         */
        public $model;

        /**
            * MerchantRepository constructor.
            *
            * @param Merchant $model
            */
        public function __construct(Merchant $model)
        {
            $this->model = $model;
        }

        /**
         * create merchant
         */
        
        public function store($data){
           return $this->model->create($data);
        }

        /**
         * update merchant
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

        /**
         * check record using domain
         */
        public function findByDomain($domain){
            return $this->model->where('domain',$domain)->first();
        }
    }