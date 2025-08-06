# Task Management System

A simple yet powerful task management system built with Core PHP, MySQL, and Bootstrap.

## Features

- **User Authentication**: Secure login/registration system
- **Task Management**: Create, read, update, delete tasks
- **Advanced Tracking**: Status, priority, assignments, due dates
- **Team Collaboration**: Assign tasks to team members
- **Responsive UI**: Clean Bootstrap interface

## Setup Instructions

1. **Database Setup**:
   - Import `setup.sql` into your MySQL database
   - Update database credentials in `config/database.php`

2. **Web Server**:
   - Place files in your web server directory (e.g., `htdocs/task_mgmt`)
   - Ensure PHP and MySQL are running

3. **Default Login**:
   - Username: `admin`
   - Password: `password`

## Project Structure

```
task_mgmt/
├── config/
│   └── database.php       # Database configuration
├── models/
│   ├── User.php          # User model
│   └── Task.php          # Task model
├── views/
│   ├── login.php         # Login page
│   ├── register.php      # Registration page
│   ├── dashboard.php     # Main dashboard
│   ├── create_task.php   # Task creation form
│   └── edit_task.php     # Task editing form
├── controllers/
│   ├── auth.php          # Authentication controller
│   └── task_controller.php # Task operations controller
├── index.php             # Main entry point
├── setup.sql             # Database schema
└── README.md             # This file
```

## Task Features

- **Status Tracking**: Pending, In Progress, Completed, Cancelled
- **Priority Levels**: Low, Medium, High, Urgent
- **Team Assignment**: Assign tasks to registered users
- **Due Date Management**: Set and track deadlines
- **Rich Descriptions**: Detailed task information

## Security Features

- Password hashing with PHP's password_hash()
- SQL injection prevention with prepared statements
- Session-based authentication
- Input sanitization and validation

## Usage

1. Register new team members or login with admin account
2. Create tasks with detailed information
3. Assign tasks to team members
4. Track progress with status updates
5. Monitor priorities and due dates
6. Collaborate effectively with your team

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Web server (Apache/Nginx)
- Modern web browser