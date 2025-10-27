$router = new \App\Core\Router();
// Routes principales
$router->get('/', 'HomeController@index');
$router->get('/dashboard', 'DashboardController@index');

// Authentication
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->get('/password/reset', 'AuthController@passwordResetForm');
$router->post('/password/reset', 'AuthController@passwordReset');

// Users
$router->group(['prefix' => 'users', 'middleware' => 'auth'], function($router) {
    $router->get('/', 'UserController@index');
    $router->get('/create', 'UserController@create');
    $router->post('/', 'UserController@store');
    $router->get('/{id}', 'UserController@show');
    $router->get('/{id}/edit', 'UserController@edit');
    $router->put('/{id}', 'UserController@update');
    $router->delete('/{id}', 'UserController@delete');
    $router->get('/profile', 'UserController@profile');
    $router->post('/profile', 'UserController@updateProfile');
});

// Classes
$router->group(['prefix' => 'classes', 'middleware' => 'auth'], function($router) {
    $router->get('/', 'ClassController@index');
    $router->get('/create', 'ClassController@create');
    $router->post('/', 'ClassController@store');
    $router->get('/{id}', 'ClassController@show');
    $router->get('/{id}/edit', 'ClassController@edit');
    $router->put('/{id}', 'ClassController@update');
    $router->delete('/{id}', 'ClassController@delete');
    $router->get('/{id}/students', 'ClassController@students');
    $router->post('/{id}/students', 'ClassController@addStudent');
    $router->delete('/{id}/students/{student_id}', 'ClassController@removeStudent');
});

// Courses
$router->group(['prefix' => 'courses', 'middleware' => 'auth'], function($router) {
    $router->get('/', 'CourseController@index');
    $router->get('/create', 'CourseController@create');
    $router->post('/', 'CourseController@store');
    $router->get('/{id}', 'CourseController@show');
    $router->get('/{id}/edit', 'CourseController@edit');
    $router->put('/{id}', 'CourseController@update');
    $router->delete('/{id}', 'CourseController@delete');
});

// Attendance
$router->group(['prefix' => 'attendance', 'middleware' => 'auth'], function($router) {
    $router->get('/', 'AttendanceController@index');
    $router->get('/mark', 'AttendanceController@markForm');
    $router->post('/mark', 'AttendanceController@mark');
    $router->get('/report', 'AttendanceController@report');
    $router->get('/export', 'AttendanceController@export');
});

// Grades
$router->group(['prefix' => 'grades', 'middleware' => 'auth'], function($router) {
    $router->get('/', 'GradeController@index');
    $router->get('/create', 'GradeController@create');
    $router->post('/', 'GradeController@store');
    $router->get('/{id}/edit', 'GradeController@edit');
    $router->put('/{id}', 'GradeController@update');
    $router->delete('/{id}', 'GradeController@delete');
    $router->get('/report', 'GradeController@report');
    $router->get('/export', 'GradeController@export');
});

// Schedule
$router->group(['prefix' => 'schedule', 'middleware' => 'auth'], function($router) {
    $router->get('/', 'ScheduleController@index');
    $router->get('/create', 'ScheduleController@create');
    $router->post('/', 'ScheduleController@store');
    $router->get('/{id}/edit', 'ScheduleController@edit');
    $router->put('/{id}', 'ScheduleController@update');
    $router->delete('/{id}', 'ScheduleController@delete');
    $router->get('/export', 'ScheduleController@export');
});

// Library
$router->group(['prefix' => 'library', 'middleware' => 'auth'], function($router) {
    $router->get('/', 'LibraryController@index');
    $router->get('/books/create', 'LibraryController@addBook');
    $router->post('/books', 'LibraryController@storeBook');
    $router->get('/books/{id}', 'LibraryController@viewBook');
    $router->get('/books/{id}/edit', 'LibraryController@editBook');
    $router->put('/books/{id}', 'LibraryController@updateBook');
    $router->delete('/books/{id}', 'LibraryController@deleteBook');
    $router->post('/loans', 'LibraryController@loanBook');
    $router->post('/returns/{id}', 'LibraryController@returnBook');
    $router->get('/my-loans', 'LibraryController@myLoans');
    $router->get('/overdue', 'LibraryController@overdueLoans');
});

// Events
$router->group(['prefix' => 'events', 'middleware' => 'auth'], function($router) {
    $router->get('/', 'EventController@index');
    $router->get('/create', 'EventController@create');
    $router->post('/', 'EventController@store');
    $router->get('/{id}', 'EventController@show');
    $router->get('/{id}/edit', 'EventController@edit');
    $router->put('/{id}', 'EventController@update');
    $router->delete('/{id}', 'EventController@delete');
    $router->get('/calendar', 'EventController@calendar');
});

// Payments
$router->group(['prefix' => 'payments', 'middleware' => 'auth'], function($router) {
    $router->get('/', 'PaymentController@index');
    $router->get('/create', 'PaymentController@create');
    $router->post('/', 'PaymentController@store');
    $router->get('/{id}', 'PaymentController@show');
    $router->get('/invoice/{id}', 'PaymentController@invoice');
    $router->get('/report', 'PaymentController@report');
});

// Reports
$router->group(['prefix' => 'reports', 'middleware' => ['auth', 'admin']], function($router) {
    $router->get('/', 'ReportController@index');
    $router->get('/students', 'ReportController@students');
    $router->get('/attendance', 'ReportController@attendance');
    $router->get('/grades', 'ReportController@grades');
    $router->get('/payments', 'ReportController@payments');
    $router->get('/export/{type}', 'ReportController@export');
});

// Settings
$router->group(['prefix' => 'settings', 'middleware' => ['auth', 'admin']], function($router) {
    $router->get('/', 'SettingController@index');
    $router->post('/', 'SettingController@update');
    $router->get('/backup', 'SettingController@backup');
    $router->post('/restore', 'SettingController@restore');
    $router->get('/logs', 'SettingController@logs');
});

// API Routes
$router->group(['prefix' => 'api', 'middleware' => 'api'], function($router) {
    $router->post('/login', 'Api\AuthController@login');
    $router->get('/users', 'Api\UserController@index');
    $router->get('/classes', 'Api\ClassController@index');
    $router->get('/attendance', 'Api\AttendanceController@index');
    $router->get('/grades', 'Api\GradeController@index');
    $router->get('/schedule', 'Api\ScheduleController@index');
    $router->get('/events', 'Api\EventController@index');
});

// Error handlers
$router->set404(function() {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    echo View::render('errors/404');
});

$router->set500(function() {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
    echo View::render('errors/500');
});
