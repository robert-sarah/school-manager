<?php
namespace App\Controllers;

use App\Core\BaseController;

class GradeController extends BaseController {
    private $gradeModel;
    
    public function __construct() {
        parent::__construct();
        $this->gradeModel = new \App\Models\Grade();
    }
    
    public function index() {
        $this->requirePermission('view_grades');
        
        $classId = $_GET['class_id'] ?? null;
        $subjectId = $_GET['subject_id'] ?? null;
        
        $exams = $this->gradeModel->getExams($classId, $subjectId);
        $classes = (new \App\Models\Class_())->getAllClasses();
        $subjects = (new \App\Models\Subject())->getAllSubjects();
        
        $this->view('grades/index', [
            'exams' => $exams,
            'classes' => $classes,
            'subjects' => $subjects,
            'selectedClass' => $classId,
            'selectedSubject' => $subjectId
        ]);
    }
    
    public function create() {
        $this->requirePermission('manage_grades');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'subject_id' => $_POST['subject_id'],
                'class_id' => $_POST['class_id'],
                'exam_date' => $_POST['exam_date'],
                'total_marks' => $_POST['total_marks'],
                'passing_marks' => $_POST['passing_marks']
            ];
            
            if ($this->gradeModel->addExam($data)) {
                $this->setFlash('success', 'Exam created successfully');
                redirect('/grades');
            } else {
                $this->setFlash('error', 'Failed to create exam');
            }
        }
        
        $classes = (new \App\Models\Class_())->getAllClasses();
        $subjects = (new \App\Models\Subject())->getAllSubjects();
        
        $this->view('grades/create', [
            'classes' => $classes,
            'subjects' => $subjects
        ]);
    }
    
    public function markGrades($examId) {
        $this->requirePermission('manage_grades');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $grades = [];
            foreach ($_POST['grades'] as $studentId => $grade) {
                $grades[] = [
                    'student_id' => $studentId,
                    'marks' => $grade['marks'],
                    'remarks' => $grade['remarks'] ?? null
                ];
            }
            
            try {
                $this->gradeModel->addGrades($examId, $grades);
                $this->setFlash('success', 'Grades added successfully');
                redirect("/grades/view/$examId");
            } catch (\Exception $e) {
                $this->setFlash('error', 'Failed to add grades');
            }
        }
        
        $exam = $this->gradeModel->getExams(null, null, $examId)[0];
        $students = (new \App\Models\Student())->getStudentsByClass($exam['class_id']);
        $existingGrades = $this->gradeModel->getClassGrades($exam['class_id'], $examId);
        
        $this->view('grades/mark', [
            'exam' => $exam,
            'students' => $students,
            'existingGrades' => $existingGrades
        ]);
    }
    
    public function view($examId) {
        $this->requirePermission('view_grades');
        
        $exam = $this->gradeModel->getExams(null, null, $examId)[0];
        $grades = $this->gradeModel->getClassGrades($exam['class_id'], $examId);
        
        $this->view('grades/view', [
            'exam' => $exam,
            'grades' => $grades
        ]);
    }
    
    public function studentReport($studentId) {
        $this->requirePermission('view_grades');
        
        $student = (new \App\Models\Student())->getStudentById($studentId);
        $grades = $this->gradeModel->getStudentGrades($studentId);
        $classReport = $this->gradeModel->generateReport(
            $studentId,
            $student['class_id'],
            getCurrentSemesterId()
        );
        
        $this->view('grades/report', [
            'student' => $student,
            'grades' => $grades,
            'report' => $classReport
        ]);
    }
}
?>
