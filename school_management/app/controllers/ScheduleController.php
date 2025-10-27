<?php
namespace App\Controllers;

use App\Core\BaseController;

class ScheduleController extends BaseController {
    private $scheduleModel;
    
    public function __construct() {
        parent::__construct();
        $this->scheduleModel = new \App\Models\Schedule();
    }
    
    public function index() {
        $this->requirePermission('view_schedule');
        
        $classId = $_GET['class_id'] ?? null;
        $teacherId = $_GET['teacher_id'] ?? null;
        
        $schedule = [];
        if ($classId) {
            $schedule = $this->scheduleModel->getClassSchedule($classId);
        } elseif ($teacherId) {
            $schedule = $this->scheduleModel->getTeacherSchedule($teacherId);
        }
        
        $classes = (new \App\Models\Class_())->getAllClasses();
        $teachers = (new \App\Models\User())->getTeachers();
        
        $this->view('schedule/index', [
            'schedule' => $schedule,
            'classes' => $classes,
            'teachers' => $teachers,
            'selectedClass' => $classId,
            'selectedTeacher' => $teacherId
        ]);
    }
    
    public function create() {
        $this->requirePermission('manage_schedule');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $timeSlotData = [
                'start_time' => $_POST['start_time'],
                'end_time' => $_POST['end_time'],
                'day_of_week' => $_POST['day_of_week']
            ];
            
            $timeSlotId = $this->scheduleModel->createTimeSlot($timeSlotData);
            
            if ($timeSlotId) {
                $scheduleData = [
                    'class_id' => $_POST['class_id'],
                    'subject_id' => $_POST['subject_id'],
                    'teacher_id' => $_POST['teacher_id'],
                    'time_slot_id' => $timeSlotId,
                    'room_number' => $_POST['room_number']
                ];
                
                // Vérifier les conflits
                if ($this->scheduleModel->checkConflicts(
                    $timeSlotId,
                    $scheduleData['teacher_id'],
                    $scheduleData['class_id']
                )) {
                    $this->setFlash('error', 'Schedule conflict detected');
                    redirect('/schedule/create');
                }
                
                if ($this->scheduleModel->createSchedule($scheduleData)) {
                    $this->setFlash('success', 'Schedule created successfully');
                    redirect('/schedule');
                }
            }
            
            $this->setFlash('error', 'Failed to create schedule');
        }
        
        $classes = (new \App\Models\Class_())->getAllClasses();
        $subjects = (new \App\Models\Subject())->getAllSubjects();
        $teachers = (new \App\Models\User())->getTeachers();
        $timeSlots = $this->scheduleModel->getTimeSlots();
        
        $this->view('schedule/create', [
            'classes' => $classes,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'timeSlots' => $timeSlots
        ]);
    }
    
    public function edit($id) {
        $this->requirePermission('manage_schedule');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'class_id' => $_POST['class_id'],
                'subject_id' => $_POST['subject_id'],
                'teacher_id' => $_POST['teacher_id'],
                'time_slot_id' => $_POST['time_slot_id'],
                'room_number' => $_POST['room_number']
            ];
            
            if ($this->scheduleModel->checkConflicts(
                $data['time_slot_id'],
                $data['teacher_id'],
                $data['class_id']
            )) {
                $this->setFlash('error', 'Schedule conflict detected');
                redirect("/schedule/edit/$id");
            }
            
            if ($this->scheduleModel->updateSchedule($id, $data)) {
                $this->setFlash('success', 'Schedule updated successfully');
                redirect('/schedule');
            } else {
                $this->setFlash('error', 'Failed to update schedule');
            }
        }
        
        $schedule = $this->scheduleModel->getScheduleById($id);
        $classes = (new \App\Models\Class_())->getAllClasses();
        $subjects = (new \App\Models\Subject())->getAllSubjects();
        $teachers = (new \App\Models\User())->getTeachers();
        $timeSlots = $this->scheduleModel->getTimeSlots();
        
        $this->view('schedule/edit', [
            'schedule' => $schedule,
            'classes' => $classes,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'timeSlots' => $timeSlots
        ]);
    }
    
    public function delete($id) {
        $this->requirePermission('manage_schedule');
        
        if ($this->scheduleModel->deleteSchedule($id)) {
            $this->setFlash('success', 'Schedule deleted successfully');
        } else {
            $this->setFlash('error', 'Failed to delete schedule');
        }
        
        redirect('/schedule');
    }
}
?>
