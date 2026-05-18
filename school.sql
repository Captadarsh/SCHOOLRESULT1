-- ============================================================
--  SMART SCHOOL RESULT & PERFORMANCE MANAGEMENT SYSTEM
--  Database : school_db
--  Phase    : 1 — Foundation
--  IMPORTANT: Never rename this database. All phases use school_db.
-- ============================================================

CREATE DATABASE IF NOT EXISTS school_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE school_db;

-- ============================================================
-- TABLE 1: users
-- Stores admin, teacher, principal accounts
-- role: 1=Admin  2=Teacher  3=Principal
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id           INT(11)      NOT NULL AUTO_INCREMENT,
    full_name    VARCHAR(150) NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    username     VARCHAR(80)  NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,          -- bcrypt hashed
    role         TINYINT(1)   NOT NULL DEFAULT 2, -- 1=Admin,2=Teacher,3=Principal
    phone        VARCHAR(20)  DEFAULT NULL,
    profile_pic  VARCHAR(255) DEFAULT 'default.png',
    is_active    TINYINT(1)   NOT NULL DEFAULT 1,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_role (role),
    KEY idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 2: sessions (academic year)
-- e.g. 2024-2025
-- ============================================================
CREATE TABLE IF NOT EXISTS sessions (
    id           INT(11)     NOT NULL AUTO_INCREMENT,
    session_name VARCHAR(20) NOT NULL,           -- e.g. 2024-2025
    is_current   TINYINT(1)  NOT NULL DEFAULT 0,
    created_at   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_session (session_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 3: classes
-- e.g. Class 1, Class 2 ... Class 12
-- ============================================================
CREATE TABLE IF NOT EXISTS classes (
    id           INT(11)     NOT NULL AUTO_INCREMENT,
    class_name   VARCHAR(50) NOT NULL,           -- e.g. Class 10
    class_order  INT(3)      NOT NULL DEFAULT 0, -- for sorting
    is_active    TINYINT(1)  NOT NULL DEFAULT 1,
    created_at   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_class_name (class_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 4: sections
-- e.g. Section A, B, C linked to a class
-- ============================================================
CREATE TABLE IF NOT EXISTS sections (
    id           INT(11)     NOT NULL AUTO_INCREMENT,
    class_id     INT(11)     NOT NULL,
    section_name VARCHAR(10) NOT NULL,           -- e.g. A, B, C
    is_active    TINYINT(1)  NOT NULL DEFAULT 1,
    created_at   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_class_section (class_id, section_name),
    KEY fk_section_class (class_id),
    CONSTRAINT fk_section_class FOREIGN KEY (class_id)
        REFERENCES classes (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 5: students
-- ============================================================
CREATE TABLE IF NOT EXISTS students (
    id             INT(11)      NOT NULL AUTO_INCREMENT,
    student_name   VARCHAR(150) NOT NULL,
    roll_number    VARCHAR(30)  NOT NULL,
    class_id       INT(11)      NOT NULL,
    section_id     INT(11)      NOT NULL,
    session_id     INT(11)      NOT NULL,
    father_name    VARCHAR(150) DEFAULT NULL,
    mother_name    VARCHAR(150) DEFAULT NULL,
    dob            DATE         DEFAULT NULL,
    gender         ENUM('Male','Female','Other') DEFAULT 'Male',
    phone          VARCHAR(20)  DEFAULT NULL,
    address        TEXT         DEFAULT NULL,
    photo          VARCHAR(255) DEFAULT 'default_student.png',
    is_active      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roll_class_session (roll_number, class_id, session_id),
    KEY fk_student_class (class_id),
    KEY fk_student_section (section_id),
    KEY fk_student_session (session_id),
    CONSTRAINT fk_student_class    FOREIGN KEY (class_id)   REFERENCES classes  (id) ON DELETE RESTRICT,
    CONSTRAINT fk_student_section  FOREIGN KEY (section_id) REFERENCES sections (id) ON DELETE RESTRICT,
    CONSTRAINT fk_student_session  FOREIGN KEY (session_id) REFERENCES sessions (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 6: subjects
-- Subject linked to a class
-- ============================================================
CREATE TABLE IF NOT EXISTS subjects (
    id             INT(11)     NOT NULL AUTO_INCREMENT,
    class_id       INT(11)     NOT NULL,
    subject_name   VARCHAR(100) NOT NULL,
    subject_code   VARCHAR(20)  DEFAULT NULL,
    total_marks    INT(5)       NOT NULL DEFAULT 100,
    passing_marks  INT(5)       NOT NULL DEFAULT 33,  -- per subject
    is_active      TINYINT(1)  NOT NULL DEFAULT 1,
    created_at     DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY fk_subject_class (class_id),
    CONSTRAINT fk_subject_class FOREIGN KEY (class_id)
        REFERENCES classes (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 7: exams
-- e.g. Unit Test 1, Mid Term, Final Exam
-- ============================================================
CREATE TABLE IF NOT EXISTS exams (
    id           INT(11)      NOT NULL AUTO_INCREMENT,
    exam_name    VARCHAR(100) NOT NULL,
    exam_type    ENUM('Unit Test','Mid Term','Final','Annual','Other') NOT NULL DEFAULT 'Final',
    session_id   INT(11)      NOT NULL,
    class_id     INT(11)      NOT NULL,
    exam_date    DATE         DEFAULT NULL,
    is_active    TINYINT(1)  NOT NULL DEFAULT 1,
    created_at   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY fk_exam_session (session_id),
    KEY fk_exam_class   (class_id),
    CONSTRAINT fk_exam_session FOREIGN KEY (session_id) REFERENCES sessions (id) ON DELETE RESTRICT,
    CONSTRAINT fk_exam_class   FOREIGN KEY (class_id)   REFERENCES classes  (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 8: marks
-- Core table — one row per student per subject per exam
-- Auto-calculated fields: percentage, grade, pass_fail, rank_position
-- ============================================================
CREATE TABLE IF NOT EXISTS marks (
    id              INT(11)        NOT NULL AUTO_INCREMENT,
    student_id      INT(11)        NOT NULL,
    subject_id      INT(11)        NOT NULL,
    exam_id         INT(11)        NOT NULL,
    class_id        INT(11)        NOT NULL,
    section_id      INT(11)        NOT NULL,
    session_id      INT(11)        NOT NULL,
    teacher_id      INT(11)        NOT NULL,   -- who entered the marks
    marks_obtained  DECIMAL(6,2)   NOT NULL DEFAULT 0.00,
    total_marks     INT(5)         NOT NULL DEFAULT 100,
    percentage      DECIMAL(5,2)   GENERATED ALWAYS AS
                      (ROUND((marks_obtained / total_marks) * 100, 2)) STORED,
    grade           VARCHAR(5)     DEFAULT NULL,  -- filled by PHP on save
    pass_fail       ENUM('Pass','Fail') DEFAULT NULL,
    remarks         TEXT           DEFAULT NULL,
    rank_position   INT(5)         DEFAULT NULL,  -- filled by PHP after all saves
    entered_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_marks (student_id, subject_id, exam_id),
    KEY fk_marks_student  (student_id),
    KEY fk_marks_subject  (subject_id),
    KEY fk_marks_exam     (exam_id),
    KEY fk_marks_class    (class_id),
    KEY fk_marks_section  (section_id),
    KEY fk_marks_session  (session_id),
    KEY fk_marks_teacher  (teacher_id),
    CONSTRAINT fk_marks_student  FOREIGN KEY (student_id)  REFERENCES students (id) ON DELETE RESTRICT,
    CONSTRAINT fk_marks_subject  FOREIGN KEY (subject_id)  REFERENCES subjects (id) ON DELETE RESTRICT,
    CONSTRAINT fk_marks_exam     FOREIGN KEY (exam_id)     REFERENCES exams    (id) ON DELETE RESTRICT,
    CONSTRAINT fk_marks_class    FOREIGN KEY (class_id)    REFERENCES classes  (id) ON DELETE RESTRICT,
    CONSTRAINT fk_marks_section  FOREIGN KEY (section_id)  REFERENCES sections (id) ON DELETE RESTRICT,
    CONSTRAINT fk_marks_session  FOREIGN KEY (session_id)  REFERENCES sessions (id) ON DELETE RESTRICT,
    CONSTRAINT fk_marks_teacher  FOREIGN KEY (teacher_id)  REFERENCES users    (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 9: teacher_class_access
-- Controls which teacher can access which class+section
-- ============================================================
CREATE TABLE IF NOT EXISTS teacher_class_access (
    id           INT(11)    NOT NULL AUTO_INCREMENT,
    teacher_id   INT(11)    NOT NULL,
    class_id     INT(11)    NOT NULL,
    section_id   INT(11)    NOT NULL,
    session_id   INT(11)    NOT NULL,
    created_at   DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_access (teacher_id, class_id, section_id, session_id),
    KEY fk_access_teacher  (teacher_id),
    KEY fk_access_class    (class_id),
    KEY fk_access_section  (section_id),
    CONSTRAINT fk_access_teacher  FOREIGN KEY (teacher_id)  REFERENCES users    (id) ON DELETE CASCADE,
    CONSTRAINT fk_access_class    FOREIGN KEY (class_id)    REFERENCES classes  (id) ON DELETE CASCADE,
    CONSTRAINT fk_access_section  FOREIGN KEY (section_id)  REFERENCES sections (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 10: grades_config
-- Configurable grade thresholds (admin can edit)
-- ============================================================
CREATE TABLE IF NOT EXISTS grades_config (
    id           INT(11)       NOT NULL AUTO_INCREMENT,
    grade_label  VARCHAR(5)    NOT NULL,   -- A+, A, B+, B, C, D, F
    min_percent  DECIMAL(5,2)  NOT NULL,
    max_percent  DECIMAL(5,2)  NOT NULL,
    gpa_points   DECIMAL(3,1)  DEFAULT 0.0,
    remark       VARCHAR(50)   DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 11: attendance
-- Monthly attendance linked to student (used in dashboard analytics)
-- ============================================================
CREATE TABLE IF NOT EXISTS attendance (
    id              INT(11)    NOT NULL AUTO_INCREMENT,
    student_id      INT(11)    NOT NULL,
    session_id      INT(11)    NOT NULL,
    month           TINYINT(2) NOT NULL,   -- 1-12
    year            YEAR       NOT NULL,
    total_days      INT(3)     NOT NULL DEFAULT 0,
    days_present    INT(3)     NOT NULL DEFAULT 0,
    attendance_pct  DECIMAL(5,2) GENERATED ALWAYS AS
                      (ROUND((days_present / total_days) * 100, 2)) STORED,
    created_at      DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_attendance (student_id, session_id, month, year),
    KEY fk_att_student (student_id),
    KEY fk_att_session (session_id),
    CONSTRAINT fk_att_student FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE,
    CONSTRAINT fk_att_session FOREIGN KEY (session_id) REFERENCES sessions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 12: audit_log
-- Tracks all important actions for security/admin review
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_log (
    id           INT(11)      NOT NULL AUTO_INCREMENT,
    user_id      INT(11)      DEFAULT NULL,
    action       VARCHAR(255) NOT NULL,
    table_name   VARCHAR(50)  DEFAULT NULL,
    record_id    INT(11)      DEFAULT NULL,
    ip_address   VARCHAR(45)  DEFAULT NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user   (user_id),
    KEY idx_action (action(50))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Grade configuration (standard Indian school grading)
INSERT INTO grades_config (grade_label, min_percent, max_percent, gpa_points, remark) VALUES
('A+',  90.00, 100.00, 10.0, 'Outstanding'),
('A',   80.00,  89.99,  9.0, 'Excellent'),
('B+',  70.00,  79.99,  8.0, 'Very Good'),
('B',   60.00,  69.99,  7.0, 'Good'),
('C',   50.00,  59.99,  6.0, 'Average'),
('D',   33.00,  49.99,  5.0, 'Below Average'),
('F',    0.00,  32.99,  0.0, 'Fail');

-- Current academic session
INSERT INTO sessions (session_name, is_current) VALUES
('2024-2025', 1),
('2023-2024', 0);

-- Default admin account
-- Username: admin | Password: Admin@123
INSERT INTO users (full_name, email, username, password, role, is_active) VALUES
(
  'School Administrator',
  'admin@school.com',
  'admin',
  '$2y$12$6T5.5Qp6AujZpPCt.7bCXuSsMhEdKgxnhMD1wGf.P.LjzKi8zALuC',
  1,
  1
);

-- Default principal account
-- Username: principal | Password: Principal@123
INSERT INTO users (full_name, email, username, password, role, is_active) VALUES
(
  'School Principal',
  'principal@school.com',
  'principal',
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC.CLZoRWy3fTMFpDb2W',
  3,
  1
);

-- Sample classes
INSERT INTO classes (class_name, class_order) VALUES
('Class 1',  1), ('Class 2',  2), ('Class 3',  3),
('Class 4',  4), ('Class 5',  5), ('Class 6',  6),
('Class 7',  7), ('Class 8',  8), ('Class 9',  9),
('Class 10', 10),('Class 11', 11),('Class 12', 12);

-- Sample sections for Class 10 (id=10), Class 11 (id=11), Class 12 (id=12)
INSERT INTO sections (class_id, section_name) VALUES
(10, 'A'), (10, 'B'), (10, 'C'),
(11, 'A'), (11, 'B'),
(12, 'A'), (12, 'B');

-- Sample subjects for Class 10
INSERT INTO subjects (class_id, subject_name, subject_code, total_marks, passing_marks) VALUES
(10, 'Mathematics',        'MATH10',  100, 33),
(10, 'Science',            'SCI10',   100, 33),
(10, 'English',            'ENG10',   100, 33),
(10, 'Hindi',              'HIN10',   100, 33),
(10, 'Social Studies',     'SST10',   100, 33),
(10, 'Computer Science',   'CS10',    100, 33);

-- Sample subjects for Class 12
INSERT INTO subjects (class_id, subject_name, subject_code, total_marks, passing_marks) VALUES
(12, 'Physics',            'PHY12',   100, 33),
(12, 'Chemistry',          'CHEM12',  100, 33),
(12, 'Mathematics',        'MATH12',  100, 33),
(12, 'English',            'ENG12',   100, 33),
(12, 'Biology',            'BIO12',   100, 33);

-- ============================================================
-- END OF school.sql
-- ============================================================
