Project Overview

The Student Allowance Tracker is a simple web-based application developed using PHP and MySQL, designed to help students monitor and manage 
their daily expenses. The system allows users to register and log in, record their transactions (e.g., food, transport, school materials),
and automatically calculates the remaining balance based on a fixed allowance. It provides an organized and practical way for students to be more financially aware and responsible.

---

System Architecture / Design

Technologies Used:

Frontend: HTML, CSS 
Backend: PHP (with sessions)
Database: MySQL
Local Server: XAMPP

System Flow:

1. User registers and logs in.
2. On login, the Dashboard displays:

    * Current balance
    * List of past transactions

3. Users can add new transaction, and the system:

   * Saves the data in the database
   * Recalculates the remaining balance

Database Tables:

users-
  Fields: `id`, `name`, `email`, `password`,`created_at`
  CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


transcations-
  Fields: `id`, `user_id`, `date`, `description`, `amount`,`created_at`
  CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

Allowance--
  Fields: `id`, `user_id`, `amount`, `period`
  CREATE TABLE allowance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    period ENUM('daily', 'weekly', 'monthly') DEFAULT 'monthly',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

---

Screenshots with Descriptions of Key Features

Replace placeholder text with actual screenshots once available.

1.Login Page

[Login Screenshot]

> Allows users to enter their credentials. Validates the user via PHP sessions and MySQL.

2.Dashboard

[Dashboard Screenshot]

> Displays current balance and recent expenses. Makes use of SQL SUM to calculate remaining funds.

3.Add Transaction

[Add Transaction Screenshot]

> Users can log a new transaction by entering the date, description, and amount spent.

4.Transaction History

[Transaction History Screenshot]

> A table showing all past expenses, filtered per user, and ordered by date.

---

Challenges Encountered and How You Solved Them

 1. User Authentication

 Problem: 
 Solution:


---

Future Enhancements

1. Categorize Transactions

   * Add expense categories (Food, Transport, School, etc.) for better tracking.

---

