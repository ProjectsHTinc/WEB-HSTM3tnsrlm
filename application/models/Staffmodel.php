<?php
Class Staffmodel extends CI_Model
{
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('mailmodel');
		$this->load->model('smsmodel');
	}


    function checkemail($email){
	$select="SELECT * FROM edu_staff_details Where email='$email'";
	$result=$this->db->query($select);
		if($result->num_rows()>0){
			echo "false";
		}else{
			echo "true";
		}
    }

	function checkmobile($mobile){
	$select="SELECT * FROM edu_staff_details Where phone='$mobile'";
	$result=$this->db->query($select);
		if($result->num_rows()>0){
			echo "false";
		}else{
			echo "true";
		}
	}

	function checkemail_edit($email,$staff_id){
	$select="SELECT * FROM edu_staff_details Where email='$email' AND id!='$staff_id'";
	$result=$this->db->query($select);
		if($result->num_rows()>0){
			echo "false";
		}else{
			echo "true";
		}
	}
	
	function checkmobile_edit($mobile,$staff_id){
	$select="SELECT * FROM edu_staff_details Where phone='$mobile' AND id!='$staff_id'";
	$result=$this->db->query($select);
		if($result->num_rows()>0){
			echo "false";
		}else{
		  echo "true";
		}
	}

    function create_staff_details($select_role,$name,$address,$email,$class_tutor,$mobile,$sec_phone,$sex,$dob,$nationality,$religion,$community_class,$community,$qualification,$status,$staff_prof_pic,$user_id) {

		$select="SELECT * FROM edu_staff_details WHERE email='$email' AND phone='$mobile'";
		$result=$this->db->query($select);
		
       if($result->num_rows()>0){
			 $data = array(
				 "status" => "already"
			 );
			return $data;
       }else{
			$insert="INSERT INTO edu_staff_details (pia_id,role_type,name,sex,dob,nationality,religion,community_class,community,address,email,phone,sec_phone,profile_pic,trade_batch_id,qualification,status,created_by,created_at) VALUES('$user_id','$select_role','$name','$sex','$dob','$nationality','$religion','$community_class','$community','$address','$email','$mobile','$sec_phone','$staff_prof_pic','$class_tutor','$qualification','$status','$user_id',NOW())";
			$result=$this->db->query($insert);
			$insert_id = $this->db->insert_id();
			
			$digits = 6;
			$OTP = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
			$md5pwd=md5($OTP);
			$user_name = $mobile;
					
			$user_table="INSERT INTO edu_users (name,user_name,user_password,user_pic,user_type,user_master_id,pia_id,created_date,status,last_login_date) VALUES('$name','$user_name','$md5pwd','$staff_prof_pic','$select_role','$insert_id','$user_id',NOW(),'Active',NOW())";
			$result_user=$this->db->query($user_table);
				
			$subject ='M3 - Staff Login Details';
			$htmlContent = '<html>
							<head> <title></title>
							</head>
							<body>
							<p>Hi  '.$name.'</p>
							<p>Staff Login Details</p>
							<p>Username: '.$user_name.'</p>
							<p>Password: '.$OTP.'</p>
							<p></p>
							<p><a href="'.base_url() .'">Click here to Login</a></p>
							</body>
							</html>';
							
			$smsContent = 'Hi  '.$name.' Your Account Username : '.$user_name.' Password '.$OTP.'';
			
			$this->mailmodel->sendEmail($email,$subject,$htmlContent);
			$this->smsmodel->sendSMS($mobile,$smsContent);
			
			/* // Set content-type header for sending HTML email
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			// Additional headers
			$headers .= 'From: info<info@happysanz.com>' . "\r\n";
			mail($email,$subject,$htmlContent,$headers); */
			  
            if ($result_user) {
                $data = array("status" => "success");
            } else {
                $data = array("status" => "failed");
            }
			 return $data;
       }
   }

    function get_all_staff_details($user_id){
      $select="SELECT * FROM edu_staff_details WHERE pia_id='$user_id' ORDER BY id desc";
      $result=$this->db->query($select);
      return $result->result();
    }

    function get_all_staff_trainer($user_id){
      $select="SELECT * FROM edu_staff_details WHERE pia_id='$user_id' AND role_type='4' ORDER BY id desc";
      $result=$this->db->query($select);
      return $result->result();
    }

    function get_all_staff_mobilizer($user_id){
		
		$select="SELECT
					A.*,
					B.user_master_id
				FROM
					edu_staff_details A,
					edu_users B
				WHERE
					A.pia_id = '$user_id' AND A.role_type = '5' AND A.id=B.user_master_id
				ORDER BY
					id
				DESC";
      //echo $select="SELECT * FROM edu_staff_details WHERE pia_id='$user_id' AND role_type='5' ORDER BY id desc";
      $result=$this->db->query($select);
      return $result->result();
    }
    function get_all_staff_details_by_id($staff_id){
      $id=base64_decode($staff_id)/98765;
      $select="SELECT
				A.*,
				B.user_master_id,
				B.user_id
			FROM
				edu_staff_details A,
				edu_users B
			WHERE
				A.id = '$id' AND B.user_type = '5' AND A.id = B.user_master_id";
      $result=$this->db->query($select);
      return $result->result();
    }


/* 
	function update_staff_details_to_id ($select_role,$name,$address,$email,$class_tutor,$mobile,$sec_phone,$sex,$dob,$nationality,$religion,$community_class,$community,$qualification,$status,$staff_prof_pic,$user_id,$staff_id){

		$update = "UPDATE edu_staff_details SET role_type='$select_role',name='$name',sex='$sex',address='$address',email='$email',trade_batch_id='$class_tutor',phone='$mobile',sec_phone='$sec_phone',dob='$dob',nationality='$nationality',religion='$religion',community_class='$community',community='$community',qualification='$qualification',status='$status',profile_pic='$staff_prof_pic',updated_at=NOW(),updated_by='$user_id' WHERE id='$staff_id'";
		$result=$this->db->query($update);

		$update_user="UPDATE edu_users SET name='$name',user_type='$select_role', user_pic='$staff_prof_pic', status='$status' WHERE user_master_id='$staff_id'";
		$result_user=$this->db->query($update_user);
	  
		  if ($result_user) {
			  $data = array("status" => "success");
		  } else {
			  $data = array("status" => "failed");
		  }
			  return $data;
    }
 */

	function update_staff_details_to_id($select_role,$name,$address,$email,$class_tutor,$mobile,$sec_phone,$sex,$dob,$nationality,$religion,$community_class,$community,$qualification,$status,$staff_prof_pic,$user_id,$staff_id){
   
		$sQuery = "SELECT * FROM edu_staff_details WHERE id = '$staff_id'";
		$user_result = $this->db->query($sQuery);
		$ress = $user_result->result();
		if($user_result->num_rows()>0)
		{
			foreach ($user_result->result() as $rows)
			{
				$old_phone_number = $rows->phone;
			}
		}

		$update = "UPDATE edu_staff_details SET role_type='$select_role',name='$name',sex='$sex',address='$address',email='$email',trade_batch_id='$class_tutor',phone='$mobile',sec_phone='$sec_phone',dob='$dob',nationality='$nationality',religion='$religion',community_class='$community_class',community='$community',qualification='$qualification',status='$status',profile_pic='$staff_prof_pic',updated_at=NOW(),updated_by='$user_id' WHERE id='$staff_id'";
		$result=$this->db->query($update);
		
		if ($old_phone_number != $mobile){
			$update_user="UPDATE edu_users SET user_name='$mobile',name='$name',user_type='$select_role',status='$status' WHERE user_master_id='$staff_id' AND user_type != '2'";
			$result_user=$this->db->query($update_user);
			
			$subject ='M3 - Staff Login - Username Updated';
			$htmlContent = '<html>
							<head> <title></title>
							</head>
							<body>
							<p>Hi  '.$name.'</p>
							<p>Login Details</p>
							<p>Username: '.$mobile.'</p>
							<p></p>
							<p><a href="'.base_url() .'">Click here to Login</a></p>
							</body>
							</html>';
			
			$smsContent = 'Hi  '.$name.' Your Account Username : '.$mobile.' is updated.';
			
			$this->mailmodel->sendEmail($email,$subject,$htmlContent);
			$this->smsmodel->sendSMS($mobile,$smsContent);			
							
							
/* 			// Set content-type header for sending HTML email
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			// Additional headers
			$headers .= 'From: info<info@happysanz.com>' . "\r\n";
			mail($email,$subject,$htmlContent,$headers); */
			
		}else {
			 $update_user="UPDATE edu_users SET name='$name',user_type='$select_role',status='$status' WHERE user_master_id='$staff_id' AND user_type != '2'";
			$result_user=$this->db->query($update_user);
		}

			if ($result_user) {
			  $data = array("status" => "success");
			} else {
			  $data = array("status" => "failed");
			}
			  return $data;
    }
	
	function get_work_type(){
      $select="SELECT * FROM work_type_master WHERE status='Active' ORDER BY id";
      $result=$this->db->query($select);
      return $result->result();
    }
	
	function get_mob_tasks($staff_id,$task_date){
      $select="SELECT * FROM edu_task WHERE user_id='$staff_id' AND task_date = '$task_date' AND pia_id !=0 ORDER BY id";
      $result=$this->db->query($select);
      return $result->result();
    }
	
	function checkmob_task($task_date,$mob_id)
	{
		$date = date_create($task_date);
		$sdate = date_format($date,"Y-m-d");

		$sql="SELECT * FROM edu_task WHERE user_id ='$mob_id' AND task_date = '$sdate' ";
		$resultset=$this->db->query($sql);
		$res=$resultset->result();
		if(empty($res))
		{
				$data=array("status" =>"Nill");
				return $data;
		}else{
				foreach($res as $rows){
					$task_id[]=$rows->id;$task_title[]=$rows->task_title;
				}
				$data=array("status" =>"Success","task_id" =>$task_id,"task_title" =>$task_title);
				return $data; 
		}
	}
	
	function check_task_id($task_title_id)
	{
	$sql="SELECT * FROM edu_task WHERE id ='$task_title_id'";
	$resultset=$this->db->query($sql);
	$res=$resultset->result();
		if(empty($res))
		{
				$data=array("status" =>"Nill");
				return $data;
		}else{
				foreach($res as $rows){
					$task_desc =$rows->task_description;
				}
				$data=array("status" =>"Success","task_desc" =>$task_desc);
				return $data; 
		}
	}

	function add_mob_job($task_date,$select_type,$task_title_id,$task_desc,$t_title,$t_desc,$mob_id,$user_id)
	{
		$date = date_create($task_date);
		$sdate = date_format($date,"Y-m-d");
		$status = "Active";
		
		if ($select_type == '2'){
			
			$check_task ="SELECT * FROM edu_task WHERE id='$task_title_id' ";
			$resultset=$this->db->query($check_task);
			$res=$resultset->result();
			if($resultset->num_rows()!=0)
			{
				foreach($res as $rows){
					$mob_task_title =$rows->task_title;
					$task_description=$rows->task_description;
				}
			}
			$check_job ="SELECT * FROM mobilizer_attendance WHERE attendance_date='$sdate' AND mobilizer_id='$mob_id' AND work_type_id = '$select_type' AND task_id = '$task_title_id'";
			$result=$this->db->query($check_job);
			if($result->num_rows()==0)
			{
				$query = "INSERT INTO mobilizer_attendance(mobilizer_id,pia_id,work_type_id,task_id,title,comments,attendance_date,status,created_at,created_by)VALUES('$mob_id','$user_id','$select_type','$task_title_id','$mob_task_title','$task_description','$sdate','$status',NOW(),'$user_id')";
				$resultset=$this->db->query($query);
				$data= array("status"=>"Success");
				return $data;
			}else{
				$data= array("status"=>"Already");
				return $data;
			}
		} else {
			
			$check_job ="SELECT * FROM mobilizer_attendance WHERE attendance_date='$sdate' AND mobilizer_id='$mob_id' AND work_type_id = '$select_type'";
			$result=$this->db->query($check_job);
			if($result->num_rows()==0)
			{
				$query = "INSERT INTO mobilizer_attendance(mobilizer_id,pia_id,work_type_id,title,comments,attendance_date,status,created_at,created_by)VALUES('$mob_id','$user_id','$select_type','$t_title','$t_desc','$sdate','$status',NOW(),'$user_id')";
				$resultset=$this->db->query($query);
				$data= array("status"=>"Success");
				return $data;
			}else{
				$data= array("status"=>"Already");
				return $data;
			}
			
		}
	}

}
?>
