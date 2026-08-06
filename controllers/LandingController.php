<?php
/**
 * Landing Page Controller
 */
require_once ROOT_PATH . 'models/AcademicModel.php';
require_once ROOT_PATH . 'models/GuruModel.php';

class LandingController {

    public function index() {
        $academicModel = new AcademicModel();
        $guruModel = new GuruModel();

        $jurusanList = $academicModel->getJurusan();
        $guruList = array_slice($guruModel->getAll(), 0, 4);

        require_once ROOT_PATH . 'views/landing/index.php';
    }
}
