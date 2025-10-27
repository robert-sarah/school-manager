<?php
namespace App\Controllers;

use App\Core\BaseController;

class EventController extends BaseController {
    private $eventModel;
    
    public function __construct() {
        parent::__construct();
        $this->eventModel = new \App\Models\Event();
    }
    
    public function index() {
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'event_type' => $_GET['event_type'] ?? null
        ];
        
        $events = hasPermission('view_all_events') 
            ? $this->eventModel->getAllEvents($filters)
            : $this->eventModel->getUserEvents($_SESSION['user_id']);
        
        $this->view('events/index', [
            'events' => $events,
            'eventTypes' => $this->eventModel->getEventTypes(),
            'filters' => $filters
        ]);
    }
    
    public function create() {
        $this->requirePermission('manage_events');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $participants = [];
            
            if (!empty($_POST['users'])) {
                foreach ($_POST['users'] as $userId) {
                    $participants[] = [
                        'id' => $userId,
                        'type' => 'user'
                    ];
                }
            }
            
            if (!empty($_POST['classes'])) {
                foreach ($_POST['classes'] as $classId) {
                    $participants[] = [
                        'id' => $classId,
                        'type' => 'class'
                    ];
                }
            }
            
            $eventData = [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'event_type' => $_POST['event_type'],
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'location' => $_POST['location'],
                'created_by' => $_SESSION['user_id'],
                'is_public' => isset($_POST['is_public']),
                'participants' => $participants
            ];
            
            try {
                $eventId = $this->eventModel->createEvent($eventData);
                $this->setFlash('success', 'Event created successfully');
                redirect('/events');
            } catch (\Exception $e) {
                $this->setFlash('error', 'Failed to create event');
                redirect('/events/create');
            }
        }
        
        $users = (new \App\Models\User())->getAllUsers();
        $classes = (new \App\Models\Class_())->getAllClasses();
        
        $this->view('events/create', [
            'eventTypes' => $this->eventModel->getEventTypes(),
            'users' => $users,
            'classes' => $classes
        ]);
    }
    
    public function edit($id) {
        $this->requirePermission('manage_events');
        
        $event = $this->eventModel->getEvent($id);
        
        if (!$event) {
            $this->setFlash('error', 'Event not found');
            redirect('/events');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $participants = [];
            
            if (!empty($_POST['users'])) {
                foreach ($_POST['users'] as $userId) {
                    $participants[] = [
                        'id' => $userId,
                        'type' => 'user'
                    ];
                }
            }
            
            if (!empty($_POST['classes'])) {
                foreach ($_POST['classes'] as $classId) {
                    $participants[] = [
                        'id' => $classId,
                        'type' => 'class'
                    ];
                }
            }
            
            $eventData = [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'event_type' => $_POST['event_type'],
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'location' => $_POST['location'],
                'is_public' => isset($_POST['is_public']),
                'participants' => $participants
            ];
            
            try {
                $this->eventModel->updateEvent($id, $eventData);
                $this->setFlash('success', 'Event updated successfully');
                redirect('/events');
            } catch (\Exception $e) {
                $this->setFlash('error', 'Failed to update event');
                redirect("/events/edit/$id");
            }
        }
        
        $users = (new \App\Models\User())->getAllUsers();
        $classes = (new \App\Models\Class_())->getAllClasses();
        
        $this->view('events/edit', [
            'event' => $event,
            'eventTypes' => $this->eventModel->getEventTypes(),
            'users' => $users,
            'classes' => $classes
        ]);
    }
    
    public function view($id) {
        $event = $this->eventModel->getEvent($id);
        
        if (!$event) {
            $this->setFlash('error', 'Event not found');
            redirect('/events');
        }
        
        $this->view('events/view', [
            'event' => $event
        ]);
    }
    
    public function delete($id) {
        $this->requirePermission('manage_events');
        
        if ($this->eventModel->deleteEvent($id)) {
            $this->setFlash('success', 'Event deleted successfully');
        } else {
            $this->setFlash('error', 'Failed to delete event');
        }
        
        redirect('/events');
    }
    
    public function calendar() {
        $events = hasPermission('view_all_events')
            ? $this->eventModel->getAllEvents()
            : $this->eventModel->getUserEvents($_SESSION['user_id']);
            
        // Formater les événements pour FullCalendar
        $calendarEvents = array_map(function($event) {
            return [
                'id' => $event['id'],
                'title' => $event['title'],
                'start' => $event['start_date'],
                'end' => $event['end_date'],
                'url' => "/events/view/{$event['id']}",
                'backgroundColor' => $this->getEventColor($event['event_type'])
            ];
        }, $events);
        
        $this->view('events/calendar', [
            'events' => json_encode($calendarEvents)
        ]);
    }
    
    private function getEventColor($eventType) {
        $colors = [
            'academic' => '#4CAF50',
            'sports' => '#2196F3',
            'cultural' => '#9C27B0',
            'holiday' => '#FF9800',
            'exam' => '#F44336',
            'meeting' => '#607D8B',
            'other' => '#795548'
        ];
        
        return $colors[$eventType] ?? '#795548';
    }
}
?>
