<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Materi extends CI_Controller {
	var $username = '';
	var $mapel = '';

	public function __construct()
    {
        parent::__construct();

        // MODEL
        $this->load->model("Siswa_model");

        // JS

		$data['js'] = '';
		$data['validasi'] = '';

        // CHECK LOGIN
        if(!$this->session->userdata('level')){
        	$this->session->set_flashdata("error","Anda harus login untuk mengakses halaman ini ");
        	redirect("../auth/masuk");
        }
        // CHECK MAPEL
        if(!$this->session->userdata('mapel')){
			redirect("beranda");
		}

        $this->mapel 	= $this->session->userdata('mapel');
        $this->username = $this->session->userdata('username');
        $this->userid   = $this->session->userdata('userid');
    }

	public function index(){
		$id 	=	$this->Siswa_model->get_current_materi();
		if(!$id){
			// JIKA USER BARU PERTAMA KALI MENGAKSES MATA KULIAH INI
			$id =	$this->Siswa_model->get_first_materi($this->mapel);
			// SET PROGRESS KE MATERI AWAL
			set_progress($id,'','','');	
		}
		
		redirect("materi/activity/$id");
	}

	public function activity($id)
	{
		if(!$this->session->userdata('mapel')){
			redirect("beranda");
		}
		$data['js'] = '';
		$data['validasi'] = array($this->load->view('template/js/dynamic_tabkonten', NULL, TRUE));
		$data['modal'] = '';

		//MAPEL PILIHAN SESUAI KELAS
		$data['materi'] = $this->Siswa_model->get_mapel_by_konten($id);
		$data['konten'] = $this->Siswa_model->get_konten_detail($id);

		$this->load->view('template/header');
		$this->load->view('materi/index', $data);
		$this->load->view('template/footer');
	}

	public function upload_tugas(){
		if($_POST){
			$tugas 				= 	'';
			$submateri_id 		=	$this->input->post('submateri');
			$kontenmateri_id 	=	$this->input->post('kontenmateri');
			$tipekonten 		=	$this->input->post('tipekonten');

			$folder 	= $this->username;

			$config['upload_path']          = realpath('./../')."/upload/tugas/".$folder;	
	    	$config['allowed_types']        = 'pdf|mp4|webm|oggv|zip|rar|jpg|doc|docx';
	    	$config['file_name']			= $submateri_id.'-'.$_FILES['uptugas']['name'];

	        $this->load->library('upload');
	        $this->upload->initialize($config);

	        if($this->upload->do_upload('uptugas'))
	        {
                $tugas = $folder.'/'.$this->upload->data('file_name');
				chmod($config['upload_path'].'/'.$isi, 0777); // note that it's usually changed to 0755

				// SET PROGRESS
				if($tipekonten == 'class'){
					set_progress($submateri_id,$tugas,'','');
				}
				else{
					set_progress($submateri_id,'','tugas','');
				}
				$this->session->set_flashdata('message','<label class="label label-success clues">Upload berhasil, tunggu proses penilaian.</label>');
	        }
	        else{
	        	$error = array('error' => $this->upload->display_errors());
	        	
	        	$this->session->set_flashdata('message','<label class="label label-danger clues">Upload gagal. '.strip_tags($error['error']).'</label>');	
	        }	
		}
		redirect('materi/activity/'.$kontenmateri_id);
	}
	// TUGAS
		// JIKA UPLOAD TUGAS
			// SET PROGRESS DENGAN TUGAS_ID
		// JIKA TUGAS SUDAH DINILAI DOSEN
			// SET PROGRESS DENGAN SUBMATERI BERIKUTNYA

	public function komentar(){
		$subyek		=	$this->input->post('subyek');
		$komentar	=	$this->input->post('komentar');
		$kontenmateri	=	$this->input->post('kontenmateri');

		$data_komentar	= array("user_id" 		=> $this->userid,
								"level"			=> $this->session->userdata('level'),
								"kontenmateri_id" => $kontenmateri,
								"subyek"	=> $subyek,
								"deskripsi"	=> $komentar,
								"tanggal"	=> date("Y-m-d H:i:s")
								);
		if($this->db->insert('komentar', $data_komentar)){
			echo "OK";
		}
		redirect("materi/activity/".$kontenmateri);
	}
}
