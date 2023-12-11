<?php
    namespace App\Repositories;

    use App\Interfaces\EloquentRepositoryInterface;
    use App\Models\User;
    use Validator;

    class UserRepository implements EloquentRepositoryInterface
    {
        /**
         * @var $model object of mod
         */
        public $model;

        /**
            * UserRepository constructor.
            *
            * @param User $model
            */
        public function __construct(User $model)
        {
            $this->model = $model;
        }

        /**
         * create user
         */
        
        public function store($data){
           return $this->model->create($data);
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

        /**
         * check record using email
         */
        public function findByEmail($email){
            return $this->model->where('email',$email)->first();
        }

        /**
         * check record using email
         */
        public function findByConditions($condtions){
            return $this->model->where($condtions)->first();
        }
    }