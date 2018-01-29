<?php
use \Model\Users;
use Firebase\JWT\JWT;
class Controller_Users extends Controller_Base
{
    private  $idAdmin = 1;
    private  $idUser = 2;

    private function newUser($input)
    {
    		$user = new Model_Users();
            $user->userName = $input['userName'];
            $user->password = $input['password'];
            $user->email = $input['email'];
            $user->id_device = $input['id_device'];
            $user->id_role = $this->idUser;
            return $user;
    }

    private function saveUser($user)
    {
    	$userExists = Model_Users::find('all', 
    								array('where' => array(
    													array('email', '=', $user->email)
    														)
    									)
    							);
    	if(empty($userExists)){
    		$userToSave = $user;
    		$userToSave->save();
    		$json = $this->response(array(
                    'code' => 201,
                    'message' => 'Usuario creado',
                    'name' => $user->userName
                ));
    		return $json;
    	}else{
    		$json = $this->response(array(
                    'code' => 204,
                    'message' => 'Usuario ya registrado'
                ));
    		return $json;
    	}
    }

    public function post_register()
    {
        try {
            if ( !isset($_POST['userName']) || !isset($_POST['password']) || !isset($_POST['email'])) 
            {
                $json = $this->response(array(
                    'code' => 400,
                    'message' => 'Algun paramentro esta vacio'
                ));
                return $json;
            }if(!empty($_POST['userName']) && !empty($_POST['password']) && !empty($_POST['email'])){
				$input = $_POST;
	            $newUser = $this->newUser($input);
	           	$json = $this->saveUser($newUser);
	            return $json;
	        }else{
	        	$json = $this->response(array(
                    'code' => 400,
                    'message' => 'Algun campo vacio'
                ));
                return $json;
	        }
        }catch (Exception $e){
            $json = $this->response(array(
                'code' => 500,
                'message' =>  $e->getMessage()
            ));
            return $json;
        }      
    }

    public function post_login()
    {	try{
	        if ( !isset($_POST['userName']) || !isset($_POST['password']) ) {
	            $json = $this->response(array(
	                    'code' => 400,
	                    'message' => 'alguno de los datos esta vacio'
	                ));
	                return $json;
	        }else if( !empty($_POST['userName']) && !empty($_POST['password'])){
	            $input = $_POST;
	            $user = Model_Users::find('all', 
		            						array('where' => array(
		            							array('userName', '=', $input['userName']), 
		            							array('password', '=', $input['password'])
		            							)
		            						)
		            					);
	            
	            if(!empty($user))
	            {
	            	$user = reset($user);
	            	$userName = $user->userName;
	            	$password = $user->password;
	            	$id = $user->id;
	            	$email = $user->email;
	            	$id_role = $user->id_role;
	                $token = $this->encodeToken($userName, $password, $id, $email, $id_role);
	                $json = $this->response(array(
	                    'code' => 200,
	                    'message' => 'Log In correcto',
	                    'token' => $token
	                    ));
	                return $json; 
	        	}else{
	        		$json = $this->response(array(
	                    'code' => 400,
	                    'message' => 'Algun dato erroneo'
	                ));
	                return $json;
	            	}
	            }else{
	        		$json = $this->response(array(
	                    'code' => 400,
	                    'message' => 'No se permiten cadenas de texto vacias'
	                ));
	                return $json;
	            	}
	        	
	    	}catch(Exception $e){
	    		 $json = $this->response(array(
	                'code' => 500,
	                'message' =>  $e->getMessage()
	            ));
	            return $json;
	    	}
	}
	
	public function post_forgotPassword()
	{
		try{
			$input = $_POST;
			if ( !isset($_POST['userName']) || !isset($_POST['password']) ) {
	            $json = $this->response(array(
	                    'code' => 400,
	                    'message' => 'alguno de los datos esta vacio'
	                ));
	                return $json;
	        }else if( !empty($_POST['userName']) && !empty($_POST['password'])){
		    	$user = Model_Users::find('all', 
		           					array('where' => array(
		           							array('userName', '=', $input['userName']), 
		           							array('email', '=', $input['email'])
		           							)
		           						)
		           					);
		    if($user != null){
		    	$user = reset($user);
		    	$userName = $user->userName;
	            $password = $user->password;
	            $id = $user->id;
	            $email = $user->email;
	            $id_role = $user->id_role;
		    	$token = $this->encodeToken($userName, $password, $id, $email, $id_role);
		        $json = $this->response(array(
		                    'code' => 200,
		                    'message' => 'Usuario encontrado, se puede cambiar la password',
		                    'token' => $token
		                    ));
		                return $json;
		    }else{
		    	 $json = $this->response(array(
		                    'code' => 400,
		                    'message' => 'Usuario no encontrado.',
		                    'data' => $token
		                    ));
		                return $json;
		    	}
			}
		}catch(Exception $e){
		    		 $json = $this->response(array(
		                'code' => 500,
		                'message' =>  $e->getMessage()
		            ));
		            return $json;
		    	}
	}

	public function post_changePassword()
	{
		$newPassword = $_POST['newPassword'];
		if( isset($newPassword)) {
			$decodeToken = $this->decodeToken();
			$user = Model_Users::find('all', 
			            					array('where' => array(
			            							array('userName', '=', $decodeToken->userName), 
			            							array('password', '=', $decodeToken->email)
			            							)
			            						)
			            					);
			if(isset($newPassword)){
				$user = reset($user);
				$query = DB::update($user);
				$query -> value('password', $newPassword);
				$query -> execute();
				$json = $this->response(array(
			                    'code' => 200,
			                    'message' => 'Contraseña modificada correctamente',
			                    'token' => $token
			                    ));
			                return $json;
			}else{
				$json = $this->response(array(
			                    'code' => 400,
			                    'message' => 'Campos vacios',
			                    'data' => ""
			                    ));
			                return $json;
			}
		}else{
			$json = $this->response(array(
			                    'code' => 400,
			                    'message' => 'password vacia, por favor rellenela',
			                    'data' => ""
			                    ));
			                return $json;
		}

	}
}
