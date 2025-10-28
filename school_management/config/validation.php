<?php
return [
    // Règles de validation pour les utilisateurs
    'user' => [
        'create' => [
            'username' => ['required', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
            'role' => ['required', 'in:admin,teacher,student,parent,librarian'],
            'name' => ['required', 'min:2', 'max:100'],
            'phone' => ['nullable', 'regex:/^\+?[0-9]{10,15}$/'],
            'address' => ['nullable', 'max:255']
        ],
        'update' => [
            'email' => ['email', 'unique:users,email'],
            'password' => ['nullable', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
            'name' => ['min:2', 'max:100'],
            'phone' => ['nullable', 'regex:/^\+?[0-9]{10,15}$/'],
            'address' => ['nullable', 'max:255']
        ]
    ],

    // Règles pour les étudiants
    'student' => [
        'create' => [
            'registration_number' => ['required', 'unique:students,registration_number'],
            'class_id' => ['required', 'exists:classes,id'],
            'parent_id' => ['required', 'exists:users,id'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'in:M,F'],
            'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'emergency_contact' => ['required', 'regex:/^\+?[0-9]{10,15}$/']
        ],
        'update' => [
            'class_id' => ['exists:classes,id'],
            'parent_id' => ['exists:users,id'],
            'date_of_birth' => ['date'],
            'gender' => ['in:M,F'],
            'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'emergency_contact' => ['regex:/^\+?[0-9]{10,15}$/']
        ]
    ],

    // Règles pour les enseignants
    'teacher' => [
        'create' => [
            'employee_id' => ['required', 'unique:teachers,employee_id'],
            'subjects' => ['required', 'array'],
            'qualification' => ['required', 'max:255'],
            'experience_years' => ['required', 'numeric', 'min:0'],
            'joining_date' => ['required', 'date']
        ],
        'update' => [
            'subjects' => ['array'],
            'qualification' => ['max:255'],
            'experience_years' => ['numeric', 'min:0'],
            'joining_date' => ['date']
        ]
    ],

    // Règles pour les classes
    'class' => [
        'create' => [
            'name' => ['required', 'max:50'],
            'section' => ['required', 'max:10'],
            'capacity' => ['required', 'numeric', 'min:1'],
            'teacher_id' => ['required', 'exists:teachers,id']
        ],
        'update' => [
            'name' => ['max:50'],
            'section' => ['max:10'],
            'capacity' => ['numeric', 'min:1'],
            'teacher_id' => ['exists:teachers,id']
        ]
    ],

    // Règles pour les notes
    'grade' => [
        'create' => [
            'student_id' => ['required', 'exists:students,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'exam_date' => ['required', 'date'],
            'remarks' => ['nullable', 'max:255']
        ],
        'update' => [
            'grade' => ['numeric', 'min:0', 'max:100'],
            'exam_date' => ['date'],
            'remarks' => ['nullable', 'max:255']
        ]
    ],

    // Règles pour les présences
    'attendance' => [
        'create' => [
            'student_id' => ['required', 'exists:students,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:present,absent,late'],
            'remarks' => ['nullable', 'max:255']
        ],
        'update' => [
            'status' => ['in:present,absent,late'],
            'remarks' => ['nullable', 'max:255']
        ]
    ],

    // Règles pour les événements
    'event' => [
        'create' => [
            'title' => ['required', 'max:100'],
            'description' => ['required'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'location' => ['nullable', 'max:255'],
            'type' => ['required', 'in:academic,sports,cultural,other']
        ],
        'update' => [
            'title' => ['max:100'],
            'end_date' => ['date', 'after:start_date'],
            'location' => ['nullable', 'max:255'],
            'type' => ['in:academic,sports,cultural,other']
        ]
    ],

    // Règles pour les messages
    'message' => [
        'create' => [
            'recipient_id' => ['required', 'exists:users,id'],
            'subject' => ['required', 'max:100'],
            'content' => ['required'],
            'priority' => ['required', 'in:low,normal,high']
        ]
    ],

    // Règles pour les paiements
    'payment' => [
        'create' => [
            'student_id' => ['required', 'exists:students,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer'],
            'description' => ['required', 'max:255'],
            'status' => ['required', 'in:pending,completed,failed']
        ],
        'update' => [
            'amount' => ['numeric', 'min:0'],
            'payment_date' => ['date'],
            'payment_method' => ['in:cash,card,bank_transfer'],
            'status' => ['in:pending,completed,failed']
        ]
    ]
];