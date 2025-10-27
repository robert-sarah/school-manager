<?php
namespace App\Http\Controllers\Api;

use App\Core\Controller;
use App\Core\Response;
use App\Core\JWTAuth;

class ApiController extends Controller {
    protected $auth;
    protected $response;
    
    public function __construct() {
        $this->auth = new JWTAuth();
        $this->response = new Response();
    }
    
    protected function authenticate() {
        $token = $this->getBearerToken();
        
        if (!$token) {
            return $this->response->unauthorized('Token non fourni');
        }
        
        try {
            $payload = $this->auth->validateToken($token);
            return $payload;
        } catch (\Exception $e) {
            return $this->response->unauthorized($e->getMessage());
        }
    }
    
    protected function getBearerToken() {
        $headers = apache_request_headers();
        
        if (!isset($headers['Authorization'])) {
            return null;
        }
        
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    protected function success($data = null, $message = 'Success', $code = 200) {
        return $this->response->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }
    
    protected function error($message = 'Error', $code = 400, $data = null) {
        return $this->response->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }
    
    protected function validateInput($data, $rules) {
        $validator = new \App\Core\Validator($data);
        
        if (!$validator->validate($rules)) {
            return $this->error('Validation failed', 422, [
                'errors' => $validator->getErrors()
            ]);
        }
        
        return true;
    }
}

class AuthController extends ApiController {
    public function login() {
        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ];
        
        $validation = $this->validateInput($_POST, $rules);
        if ($validation !== true) {
            return $validation;
        }
        
        try {
            $token = $this->auth->attempt($_POST['email'], $_POST['password']);
            
            if (!$token) {
                return $this->error('Invalid credentials', 401);
            }
            
            return $this->success([
                'token' => $token,
                'type' => 'Bearer'
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
    
    public function refresh() {
        try {
            $token = $this->getBearerToken();
            $newToken = $this->auth->refresh($token);
            
            return $this->success([
                'token' => $newToken,
                'type' => 'Bearer'
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 401);
        }
    }
    
    public function me() {
        $payload = $this->authenticate();
        if (!is_array($payload)) {
            return $payload;
        }
        
        $user = (new \App\Models\User())->find($payload['sub']);
        unset($user['password']);
        
        return $this->success($user);
    }
}

class StudentController extends ApiController {
    public function index() {
        $payload = $this->authenticate();
        if (!is_array($payload)) {
            return $payload;
        }
        
        $page = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 20;
        $search = $_GET['search'] ?? '';
        $class = $_GET['class'] ?? null;
        
        $students = (new \App\Models\User())->getStudents($page, $perPage, $search, $class);
        
        return $this->success($students);
    }
    
    public function show($id) {
        $payload = $this->authenticate();
        if (!is_array($payload)) {
            return $payload;
        }
        
        $student = (new \App\Models\User())->find($id);
        if (!$student || $student['role'] !== 'student') {
            return $this->error('Student not found', 404);
        }
        
        // Charger les informations détaillées
        $student['attendance'] = (new \App\Models\Attendance())->getStudentAttendance($id);
        $student['grades'] = (new \App\Models\Grade())->getStudentGrades($id);
        $student['payments'] = (new \App\Models\Payment())->getStudentPayments($id);
        
        return $this->success($student);
    }
}

class GradeController extends ApiController {
    public function index() {
        $payload = $this->authenticate();
        if (!is_array($payload)) {
            return $payload;
        }
        
        $student = $_GET['student'] ?? null;
        $subject = $_GET['subject'] ?? null;
        $period = $_GET['period'] ?? null;
        
        $grades = (new \App\Models\Grade())->getGrades($student, $subject, $period);
        
        return $this->success($grades);
    }
    
    public function store() {
        $payload = $this->authenticate();
        if (!is_array($payload)) {
            return $payload;
        }
        
        $rules = [
            'student_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'grade' => 'required|numeric|min:0|max:20',
            'comment' => 'string|max:255'
        ];
        
        $validation = $this->validateInput($_POST, $rules);
        if ($validation !== true) {
            return $validation;
        }
        
        try {
            $gradeId = (new \App\Models\Grade())->create($_POST);
            $grade = (new \App\Models\Grade())->find($gradeId);
            
            return $this->success($grade, 'Grade added successfully', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}

class AttendanceController extends ApiController {
    public function index() {
        $payload = $this->authenticate();
        if (!is_array($payload)) {
            return $payload;
        }
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $class = $_GET['class'] ?? null;
        
        $attendance = (new \App\Models\Attendance())->getAttendance($date, $class);
        
        return $this->success($attendance);
    }
    
    public function store() {
        $payload = $this->authenticate();
        if (!is_array($payload)) {
            return $payload;
        }
        
        $rules = [
            'student_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late',
            'reason' => 'string|max:255'
        ];
        
        $validation = $this->validateInput($_POST, $rules);
        if ($validation !== true) {
            return $validation;
        }
        
        try {
            $attendanceId = (new \App\Models\Attendance())->mark($_POST);
            $attendance = (new \App\Models\Attendance())->find($attendanceId);
            
            return $this->success($attendance, 'Attendance marked successfully', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
?>
