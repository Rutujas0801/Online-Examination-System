# ExamPortal: Online Examination System

Core PHP (PDO), MySQL, HTML5, CSS3 and vanilla JavaScript. No frameworks, no libraries.

## Install (XAMPP)

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy this folder into `htdocs`, so the path is `C:\xampp\htdocs\online-examination\`.
3. Open http://localhost/phpmyadmin, go to the **Import** tab, choose `database/exam_db.sql` and click **Import**. This creates the `exam_db` database, all tables and the sample data.
4. Database settings are in `config/config.php`. XAMPP defaults (user `root`, empty password) work as they are. Change `BASE_URL` only if you rename the folder.
5. Open http://localhost/online-examination/

## Demo logins

| Role    | Email             | Password   |
|---------|-------------------|------------|
| Admin   | admin@exam.com    | admin123   |
| Student | student@exam.com  | student123 |

Passwords are stored as bcrypt hashes (`password_hash`). Change the admin password for real use.
New students can register on the Register page. Admin accounts are only created in the database.

## Folder structure

```
config/config.php      database settings, pass mark, timer grace period
includes/functions.php database connection (PDO), sessions, CSRF, role checks, helpers
includes/header.php    shared page top (navigation, flash messages)
includes/footer.php    shared page bottom (confirm dialog, scripts)
admin/                 dashboard, questions, exams, students, results
student/               dashboard, instructions, exam, submit, result, history
assets/css/style.css   all styling (light and dark theme)
assets/js/app.js       theme toggle, confirm dialog, button loading state
assets/js/exam.js      question navigation, palette, countdown timer
database/exam_db.sql   tables and sample data
login.php register.php logout.php index.php
```

## Database

| Table | Purpose |
|-------|---------|
| users | name, unique email, password hash, role (admin / student) |
| questions | question text, options A to D, correct option |
| exams | title, duration in minutes, total marks |
| exam_questions | which questions belong to which exam (many-to-many) |
| exam_starts | server-side start time, so refreshing or logging out cannot reset the timer |
| results | one row per student per exam (`UNIQUE (user_id, exam_id)` blocks a second attempt) |
| student_answers | the option picked for each question, used for the answer review |

## How the main parts work

- **Login**: `login.php` checks the password with `password_verify`, then regenerates the session id. Every protected page starts with `require_role('admin')` or `require_role('student')`.
- **Exam start**: the Start button saves `NOW()` in `exam_starts`. `exam.php` works out the seconds left from that, so the timer survives a refresh.
- **Exam page**: `exam.php` never selects the `correct_option` column, so correct answers are not in the page source.
- **Marking**: `submit.php` loads the correct answers from MySQL, compares them with the letters the student sent, calculates score and percentage, and stores everything in one transaction. Marks per question = total marks / number of questions.
- **Timer end**: `exam.js` submits the form automatically at 0:00. The server also checks the elapsed time; answers arriving more than 30 seconds after the deadline are ignored.
- **Retakes**: not allowed. An admin can delete a result on the Results page, which lets that student attempt the exam again.
- **Security**: PDO prepared statements, output escaping with `e()`, CSRF token on every POST form, role-based access, students can only open their own results.

## Manual test checklist

1. Log in as admin: dashboard counts show 1 student, 25 questions, 5 exams.
2. Add, edit and delete a question. Create an exam, tick questions, save, then edit it.
3. Log out. Register a new student (try a duplicate email, a short password, mismatched passwords).
4. Log in as the student, open an exam, read the instructions, start it, answer some questions, use the number palette, submit and confirm.
5. Check the result page (score, percentage, review). Try opening the exam again: you are sent to the result.
6. Start an exam with a 1 minute duration and wait: it submits itself at 0:00.
7. As a student, open `/online-examination/admin/index.php`: you are redirected with an error message.
8. As admin, open Results, filter by exam or student, delete a result, and confirm the student can retake it.
