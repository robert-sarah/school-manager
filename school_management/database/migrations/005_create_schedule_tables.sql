-- Create time_slots table
CREATE TABLE IF NOT EXISTS time_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    day_of_week INT NOT NULL, -- 1=Monday, 7=Sunday
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create schedules table
CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    time_slot_id INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id),
    FOREIGN KEY (time_slot_id) REFERENCES time_slots(id)
);

-- Add indexes for better performance
CREATE INDEX idx_schedule_class ON schedules(class_id);
CREATE INDEX idx_schedule_teacher ON schedules(teacher_id);
CREATE INDEX idx_schedule_subject ON schedules(subject_id);
CREATE INDEX idx_schedule_timeslot ON schedules(time_slot_id);
CREATE INDEX idx_timeslot_day ON time_slots(day_of_week);
CREATE INDEX idx_timeslot_time ON time_slots(start_time, end_time);
