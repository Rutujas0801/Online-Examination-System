-- Online Examination System: database, tables and sample data
-- Import with phpMyAdmin (Import tab) or: mysql -u root < exam_db.sql
CREATE DATABASE IF NOT EXISTS exam_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE exam_db;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','student') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE questions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question_text TEXT NOT NULL,
  option_a VARCHAR(255) NOT NULL,
  option_b VARCHAR(255) NOT NULL,
  option_c VARCHAR(255) NOT NULL,
  option_d VARCHAR(255) NOT NULL,
  correct_option ENUM('A','B','C','D') NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE exams (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  duration_minutes SMALLINT UNSIGNED NOT NULL,
  total_marks SMALLINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE exam_questions (
  exam_id INT UNSIGNED NOT NULL,
  question_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (exam_id, question_id),
  FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- One row per student per exam: the UNIQUE key blocks duplicate attempts
CREATE TABLE results (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  exam_id INT UNSIGNED NOT NULL,
  correct_count SMALLINT UNSIGNED NOT NULL,
  total_questions SMALLINT UNSIGNED NOT NULL,
  score DECIMAL(6,2) NOT NULL,
  total_marks SMALLINT UNSIGNED NOT NULL,
  percentage DECIMAL(5,2) NOT NULL,
  submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY one_attempt (user_id, exam_id),
  INDEX (exam_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Server-side start time, so the timer cannot be reset by refreshing or logging out
CREATE TABLE exam_starts (
  user_id INT UNSIGNED NOT NULL,
  exam_id INT UNSIGNED NOT NULL,
  started_at DATETIME NOT NULL,
  PRIMARY KEY (user_id, exam_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Needed for the answer review on the result page
CREATE TABLE student_answers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  result_id INT UNSIGNED NOT NULL,
  question_id INT UNSIGNED NOT NULL,
  selected_option ENUM('A','B','C','D') NULL,
  is_correct TINYINT(1) NOT NULL,
  UNIQUE KEY one_answer (result_id, question_id),
  FOREIGN KEY (result_id) REFERENCES results(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Demo accounts (passwords are bcrypt hashes of admin123 / student123)
INSERT INTO users (name,email,password,role) VALUES
('Administrator','admin@exam.com','$2b$12$ZxjhDJ1Id3Q4Rq68XkMD9.Ofn5xETZUQcZN20UDSpy/UsC3/zZz6O','admin'),
('Demo Student','student@exam.com','$2b$12$80AQJRbmLypCDOyNQgJ0MO15a2My8g3hODGW2B0yK/CZodcv2RA8S','student');

INSERT INTO questions (question_text,option_a,option_b,option_c,option_d,correct_option) VALUES
('Which HTML tag creates a hyperlink?','<link>','<a>','<href>','<url>','B'),
('Which SQL statement removes rows from a table?','DROP','REMOVE','DELETE','CUT','C'),
('What does CRUD stand for?','Create, Read, Update, Delete','Copy, Run, Undo, Drop','Create, Run, Use, Delete','Connect, Read, Update, Display','A'),
('Which HTTP method is normally used to create a resource?','GET','PUT','POST','PATCH','C'),
('Which CSS property changes the text colour?','font-color','color','text-style','fgcolor','B'),
('Which SQL clause filters rows?','ORDER BY','GROUP BY','WHERE','LIMIT','C'),
('Which key uniquely identifies each row in a table?','Foreign key','Primary key','Index key','Secondary key','B'),
('Which command adds new rows to a table?','INSERT','ADD ROW','APPEND','PUT','A'),
('Which JOIN returns only rows that match in both tables?','LEFT JOIN','FULL JOIN','INNER JOIN','CROSS JOIN','C'),
('Which SQL function counts rows?','SUM()','COUNT()','TOTAL()','AVG()','B'),
('Which keyword declares a block-scoped variable?','var','let','dim','def','B'),
('What does === check in JavaScript?','Value only','Type only','Value and type','Reference only','C'),
('Which array method adds an item to the end?','push()','pop()','shift()','unshift()','A'),
('What does typeof null return?','null','undefined','object','number','C'),
('Which method converts a JSON string into an object?','JSON.stringify()','JSON.parse()','JSON.object()','JSON.decode()','B'),
('Which HTTP status code means resource not found?','200','301','404','500','C'),
('Which HTTP method replaces an existing resource?','GET','PUT','DELETE','HEAD','B'),
('Which data format is most common in REST APIs?','JSON','BMP','MP3','EXE','A'),
('How should passwords be stored in a database?','Plain text','Hashed with salt','Base64 only','Reversible XOR','B'),
('Which is a popular Node.js web framework?','Django','Express','Laravel','Flask','B'),
('Which command records staged changes in Git history?','git push','git commit','git save','git add','B'),
('Which command uploads commits to a remote repository?','git push','git pull','git clone','git fetch','A'),
('What is unit testing?','Testing the whole system at once','Testing small parts in isolation','Testing UI colours','Testing network speed','B'),
('A software bug is best described as?','A new feature','A flaw causing wrong behaviour','A database table','A test report','B'),
('Which file tells Git which files to ignore?','.gitkeep','.gitignore','.gitconfig','ignore.txt','B');

INSERT INTO exams (title,duration_minutes,total_marks) VALUES
('Web and CRUD Basics',5,5),
('Databases and SQL',5,5),
('JavaScript Fundamentals',5,5),
('Back End and APIs',5,5),
('Git and Testing',5,5);

INSERT INTO exam_questions (exam_id,question_id) VALUES
(1,1),
(1,2),
(1,3),
(1,4),
(1,5),
(2,6),
(2,7),
(2,8),
(2,9),
(2,10),
(3,11),
(3,12),
(3,13),
(3,14),
(3,15),
(4,16),
(4,17),
(4,18),
(4,19),
(4,20),
(5,21),
(5,22),
(5,23),
(5,24),
(5,25);
