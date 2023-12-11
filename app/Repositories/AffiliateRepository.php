<?php
    namespace App\Repositories;

    use App\Interfaces\EloquentRepositoryInterface;
    use App\Models\Affiliate;
    use Validator;

    class AffiliateRepository implements EloquentRepositoryInterface
    {
        /**
         * @var $model object of mod
         */
        public $model;

        /**
            * AffiliateRepository constructor.
            *
            * @param Affiliate $model
            */
        public function __construct(Affiliate $model)
        {
            $this->model = $model;
        }

        /**
         * create user
         */
        
        public function store($data){
           $affiliate =  $this->model->create($data);
           return $this->model->with(['user','merchant'])->where('id',$affiliate->id)->first();
        }

        /**
         * update user
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