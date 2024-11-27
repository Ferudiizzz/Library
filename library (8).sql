-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2024 at 01:07 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `alert_id` int(11) NOT NULL,
  `alert_type` enum('overdue','due_soon','low_copies','system') NOT NULL,
  `message` text NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `priority` enum('high','medium','low') DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `borrower_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `author_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authors`
--

INSERT INTO `authors` (`author_id`, `name`) VALUES
(4, 'Ferdinnad');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `author_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `publication_year` year(4) DEFAULT NULL,
  `available_copies` int(11) NOT NULL,
  `status` enum('available','borrowed') DEFAULT 'available',
  `total_copies` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `author_id`, `category_id`, `publication_year`, `available_copies`, `status`, `total_copies`) VALUES
(4, 'YAWA', 4, 19, '2005', 13, 'available', 12);

-- --------------------------------------------------------

--
-- Table structure for table `borrowingtransactions`
--

CREATE TABLE `borrowingtransactions` (
  `transaction_id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `borrow_date` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `status` enum('borrowed','returned','overdue') NOT NULL,
  `book_condition` enum('good','damaged','lost') DEFAULT 'good'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowingtransactions`
--

INSERT INTO `borrowingtransactions` (`transaction_id`, `borrower_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `status`, `book_condition`) VALUES
(35, 3, 4, '2024-11-24 20:07:09', '2024-12-01 20:07:09', '2024-11-24 20:07:12', '', 'good');

--
-- Triggers `borrowingtransactions`
--
DELIMITER $$
CREATE TRIGGER `check_overdue_books` BEFORE UPDATE ON `borrowingtransactions` FOR EACH ROW BEGIN
    IF NEW.status = 'borrowed' AND NEW.due_date < NOW() THEN
        SET NEW.status = 'overdue';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(1, 'Fiction'),
(2, 'Non-fiction'),
(3, 'Science'),
(4, 'Fiction'),
(5, 'Non-fiction'),
(6, 'Science Fiction'),
(7, 'Fantasy'),
(8, 'Mystery'),
(9, 'Thriller'),
(10, 'Romance'),
(11, 'Horror'),
(12, 'Biography'),
(13, 'Self-help'),
(14, 'Children\'s'),
(15, 'Historical Fiction'),
(16, 'Young Adult'),
(17, 'Poetry'),
(18, 'Cookbook'),
(19, 'Art'),
(20, 'Philosophy'),
(21, 'Health'),
(22, 'Science'),
(23, 'Travel');

-- --------------------------------------------------------

--
-- Table structure for table `invoicedetails`
--

CREATE TABLE `invoicedetails` (
  `detail_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `borrower_id` int(11) DEFAULT NULL,
  `issue_date` datetime NOT NULL,
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penalties`
--

CREATE TABLE `penalties` (
  `penalty_id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `borrower_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `overdue_days` int(11) DEFAULT NULL,
  `book_condition` enum('good','damaged','lost') DEFAULT NULL,
  `penalty_fee` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `payment_date` datetime DEFAULT NULL,
  `penalty_reason` enum('overdue','damage','loss') NOT NULL DEFAULT 'overdue'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penalties`
--

INSERT INTO `penalties` (`penalty_id`, `transaction_id`, `borrower_id`, `book_id`, `overdue_days`, `book_condition`, `penalty_fee`, `created_at`, `status`, `amount_paid`, `payment_date`, `penalty_reason`) VALUES
(23, 35, NULL, NULL, 0, NULL, 50.00, '2024-11-24 12:07:12', 'unpaid', 0.00, NULL, 'overdue');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `membership_status` enum('active','expired','suspended') DEFAULT 'active',
  `membership_expiry` date DEFAULT NULL,
  `max_books` int(11) DEFAULT 3,
  `current_borrowed` int(11) DEFAULT 0,
  `total_penalties` decimal(10,2) DEFAULT 0.00,
  `membership_type` enum('regular','premium','vip') DEFAULT 'regular',
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `first_name`, `middle_name`, `last_name`, `address`, `contact_number`, `photo_path`, `created_at`, `membership_status`, `membership_expiry`, `max_books`, `current_borrowed`, `total_penalties`, `membership_type`, `email`) VALUES
(3, 'Ferdinnad', NULL, 'Baculado', NULL, '09510875309', NULL, '2024-11-21 23:30:31', 'active', NULL, 3, 0, 0.00, 'regular', 'rio@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`alert_id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `borrower_id` (`borrower_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`author_id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `borrowingtransactions`
--
ALTER TABLE `borrowingtransactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `fk_borrower` (`borrower_id`),
  ADD KEY `fk_book` (`book_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `invoicedetails`
--
ALTER TABLE `invoicedetails`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `fk_borrower_id` (`borrower_id`);

--
-- Indexes for table `penalties`
--
ALTER TABLE `penalties`
  ADD PRIMARY KEY (`penalty_id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `borrower_id` (`borrower_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `alert_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `author_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `borrowingtransactions`
--
ALTER TABLE `borrowingtransactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `invoicedetails`
--
ALTER TABLE `invoicedetails`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penalties`
--
ALTER TABLE `penalties`
  MODIFY `penalty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `borrowingtransactions` (`transaction_id`),
  ADD CONSTRAINT `alerts_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `alerts_ibfk_3` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `borrowingtransactions`
--
ALTER TABLE `borrowingtransactions`
  ADD CONSTRAINT `borrowingtransactions_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `borrowingtransactions_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`),
  ADD CONSTRAINT `fk_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`),
  ADD CONSTRAINT `fk_borrower` FOREIGN KEY (`borrower_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `invoicedetails`
--
ALTER TABLE `invoicedetails`
  ADD CONSTRAINT `invoicedetails_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`),
  ADD CONSTRAINT `invoicedetails_ibfk_2` FOREIGN KEY (`transaction_id`) REFERENCES `borrowingtransactions` (`transaction_id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_borrower_id` FOREIGN KEY (`borrower_id`) REFERENCES `borrowingtransactions` (`transaction_id`),
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `penalties`
--
ALTER TABLE `penalties`
  ADD CONSTRAINT `penalties_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `borrowingtransactions` (`transaction_id`),
  ADD CONSTRAINT `penalties_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `penalties_ibfk_3` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
