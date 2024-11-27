-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2024 at 02:00 AM
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
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `name`, `middle_name`, `last_name`, `age`, `gender`, `birthday`, `contact_number`, `address`, `photo`, `email`, `password`, `login_attempts`, `status`, `created_at`) VALUES
(3, 'Duterte', 'BAclado', 'baculado', 12, '', '2004-03-30', '09510875309', 'Typi', 'upload/agasf.PNG', 'rio@gmail.com', '12', 0, 'active', '2024-11-25 16:37:49');

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `alert_id` int(11) NOT NULL,
  `alert_type` enum('overdue','due_soon','low_copies','system') NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
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
(1, 'F. Scott Fitzgerald'),
(2, 'George Orwell'),
(3, 'Harper Lee'),
(4, 'Herman Melville'),
(5, 'Jane Austen'),
(6, 'J.D. Salinger'),
(7, 'Homer'),
(8, 'J.R.R. Tolkien'),
(9, 'Leo Tolstoy'),
(10, 'Fyodor Dostoevsky'),
(11, 'Oscar Wilde'),
(12, 'Aldous Huxley'),
(13, 'Joseph Heller'),
(14, 'Kurt Vonnegut'),
(15, 'Dan Brown'),
(16, 'Paulo Coelho'),
(17, 'J.K. Rowling'),
(18, 'Suzanne Collins'),
(19, 'John Green'),
(20, 'Stephen King'),
(21, 'George R.R. Martin'),
(22, 'William Golding'),
(23, 'SE Hinton'),
(24, 'C.S. Lewis'),
(25, 'Margaret Mitchell'),
(26, 'Cormac McCarthy'),
(27, 'Kristin Hannah'),
(28, 'Frances Hodgson Burnett'),
(29, 'Mary Shelley'),
(30, 'Nathaniel Hawthorne'),
(31, 'Bram Stoker'),
(32, 'Sylvia Plath'),
(33, 'Ray Bradbury'),
(34, 'Lois Lowry'),
(35, 'Khaled Hosseini'),
(36, 'Madeleine L\'Engle'),
(37, 'Margaret Atwood'),
(38, 'Alice Walker'),
(39, 'Ernest Hemingway'),
(40, 'John Steinbeck'),
(41, 'Gabriel Garcia Marquez'),
(42, 'H.G. Wells'),
(43, 'Douglas Adams'),
(44, 'Emily Brontë'),
(45, 'Virginia Woolf'),
(46, 'Isabel Allende'),
(47, 'J.R.R. Tolkien');

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
(1, 'The Great Gatsby', 1, 1, '1925', 0, 'available', 5),
(2, '1984', 2, 2, '1949', 0, 'available', 3),
(3, 'To Kill a Mockingbird', 3, 1, '1960', 1, 'available', 4),
(4, 'Moby-Dick', 4, 1, '0000', 6, 'available', 6),
(5, 'Pride and Prejudice', 5, 1, '0000', 1, 'available', 7),
(6, 'The Catcher in the Rye', 6, 2, '1951', 1, 'available', 2),
(7, 'The Odyssey', 7, 1, '0000', 3, 'available', 4),
(8, 'The Hobbit', 8, 4, '1937', 4, 'available', 5),
(9, 'War and Peace', 9, 1, '0000', 3, 'available', 3),
(10, 'Crime and Punishment', 10, 1, '0000', 3, 'available', 6),
(11, 'Brave New World', 11, 2, '1932', 4, 'available', 6),
(12, 'Catch-22', 12, 2, '1961', 4, 'available', 5),
(13, 'The Da Vinci Code', 13, 4, '2003', 8, 'available', 8),
(14, 'The Alchemist', 14, 2, '1988', 10, 'available', 10),
(15, 'Harry Potter and the Sorcerer\'s Stone', 15, 4, '1997', 9, 'available', 9),
(16, 'The Hunger Games', 16, 4, '2008', 7, 'available', 7),
(17, 'The Fault in Our Stars', 17, 4, '2012', 6, 'available', 6),
(18, 'The Shining', 18, 1, '1977', 5, 'available', 5),
(19, 'The Dark Tower', 19, 1, '1982', 4, 'available', 4),
(20, 'A Game of Thrones', 20, 4, '1996', 11, 'available', 10),
(21, 'Lord of the Flies', 21, 1, '1954', 3, 'available', 3),
(22, 'The Outsiders', 22, 1, '1967', 5, 'available', 5),
(23, 'The Chronicles of Narnia', 23, 4, '1950', 7, 'available', 7),
(24, 'Gone with the Wind', 24, 1, '1936', 4, 'available', 4),
(25, 'The Road', 25, 4, '2006', 6, 'available', 6),
(26, 'The Great Alone', 26, 4, '2018', 3, 'available', 3),
(27, 'The Secret Garden', 27, 1, '1911', 5, 'available', 5),
(28, 'Frankenstein', 28, 1, '0000', 6, 'available', 6),
(29, 'The Scarlet Letter', 29, 1, '0000', 4, 'available', 4),
(30, 'Dracula', 30, 1, '0000', 7, 'available', 7),
(31, 'The Bell Jar', 31, 1, '1963', 0, 'available', 3),
(32, 'Fahrenheit 451', 32, 4, '1953', 4, 'available', 6),
(33, 'The Giver', 33, 4, '1993', 8, 'available', 8),
(34, 'The Kite Runner', 34, 4, '2003', 5, 'available', 5),
(35, 'A Wrinkle in Time', 35, 4, '1962', 7, 'available', 6),
(36, 'The Handmaid\'s Tale', 36, 1, '1985', 4, 'available', 4),
(37, 'The Hobbit', 37, 4, '1937', 9, 'available', 9),
(38, 'The Color Purple', 38, 1, '1982', 6, 'available', 6),
(39, 'The Sun Also Rises', 39, 2, '1926', 5, 'available', 5),
(40, 'The Grapes of Wrath', 40, 1, '1939', 7, 'available', 7),
(41, 'The Catcher in the Rye', 41, 2, '1951', 8, 'available', 8),
(42, '1984', 42, 2, '1949', 3, 'available', 3),
(43, 'One Hundred Years of Solitude', 43, 1, '1967', 6, 'available', 6),
(44, 'The Invisible Man', 44, 2, '0000', 5, 'available', 5),
(45, 'The Hitchhiker\'s Guide to the Galaxy', 45, 4, '1979', 9, 'available', 10),
(46, 'Wuthering Heights', 46, 1, '0000', 4, 'available', 4);

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
  `status` enum('borrowed','returned','overdue','pending_rebind') NOT NULL DEFAULT 'borrowed',
  `book_condition` enum('good','damaged','lost','pending_rebind','overdue') DEFAULT 'good',
  `payment_status` enum('pending','paid') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowingtransactions`
--

INSERT INTO `borrowingtransactions` (`transaction_id`, `borrower_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `status`, `book_condition`, `payment_status`) VALUES
(139, 9, 42, '2024-11-21 15:40:10', '2024-11-23 15:40:10', '2024-11-26 15:41:33', 'returned', 'good', 'pending'),
(140, 4, 42, '2024-11-21 15:42:14', '2024-11-23 15:42:14', '2024-11-26 15:42:57', 'returned', 'damaged', 'pending'),
(142, 6, 42, '2024-11-21 16:04:42', '2024-11-23 16:04:42', '2024-11-26 16:05:11', 'returned', 'damaged', 'pending'),
(146, 9, 35, '2024-11-27 01:37:30', '2024-12-04 01:37:30', '2024-11-27 01:37:47', 'returned', 'good', 'pending'),
(147, 4, 32, '2024-11-27 01:37:55', '2024-12-04 01:37:55', '2024-11-27 01:38:00', '', 'lost', 'pending'),
(148, 6, 10, '2024-11-27 01:41:16', '2024-12-04 01:41:16', '2024-11-27 01:41:22', '', 'lost', 'pending');

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
(4, 'Fantasy'),
(5, 'Historical'),
(6, 'Horror'),
(7, 'Mystery'),
(8, 'Biography'),
(9, 'Romance'),
(10, 'Adventure'),
(11, 'Young Adult'),
(12, 'Children');

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
  `book_condition` enum('good','damaged','lost','overdue') DEFAULT NULL,
  `penalty_fee` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_date` datetime DEFAULT NULL,
  `penalty_reason` enum('overdue','damage','loss','overdue') NOT NULL DEFAULT 'overdue',
  `overdue_charges` decimal(10,2) NOT NULL DEFAULT 10.00,
  `total_penalty_fee` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penalties`
--

INSERT INTO `penalties` (`penalty_id`, `transaction_id`, `borrower_id`, `book_id`, `overdue_days`, `book_condition`, `penalty_fee`, `created_at`, `status`, `amount_paid`, `payment_date`, `penalty_reason`, `overdue_charges`, `total_penalty_fee`) VALUES
(87, 139, 9, 42, 3, 'good', 30.00, '2024-11-26 14:41:33', 'unpaid', 0.00, NULL, 'overdue', 10.00, NULL),
(88, 140, 4, 42, 3, 'damaged', 70.00, '2024-11-26 14:42:57', 'paid', 100.00, '2024-11-27 01:28:21', 'damage', 10.00, NULL),
(90, 142, 6, 42, 3, 'damaged', 70.00, '2024-11-26 15:05:11', 'paid', 70.00, '2024-11-27 01:27:46', 'damage', 10.00, NULL),
(94, 147, 4, 32, 0, 'lost', 100.00, '2024-11-27 00:38:00', 'unpaid', 0.00, NULL, 'loss', 10.00, NULL),
(95, 148, 6, 10, 0, 'lost', 100.00, '2024-11-27 00:41:22', 'unpaid', 0.00, NULL, 'loss', 10.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(30) NOT NULL,
  `grade_section` text DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `membership_status` enum('active','expired','suspended') DEFAULT 'active',
  `max_books` int(11) DEFAULT 3,
  `current_borrowed` int(11) DEFAULT 0,
  `total_penalties` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `first_name`, `middle_name`, `last_name`, `email`, `grade_section`, `contact_number`, `created_at`, `membership_status`, `max_books`, `current_borrowed`, `total_penalties`) VALUES
(4, 'Ferdinand', 'BAclado', 'baculado', 'rio@gmail.com', '12 Rizal', '09510875309', '2024-11-24 21:40:55', 'active', 3, 0, 0.00),
(5, 'eren', NULL, 'yeager', '', NULL, NULL, '2024-11-24 21:40:55', 'active', 3, 0, 0.00),
(6, 'Alice', NULL, 'Johnson', '', NULL, NULL, '2024-11-24 21:40:55', 'active', 3, 0, 0.00),
(7, 'Bob', NULL, 'rose', '', NULL, NULL, '2024-11-24 21:40:55', 'active', 3, 0, 0.00),
(9, 'Francine', 'M', 'nanoy', 'juan@yahoo.com', '12 Mabini', '09510875309', '2024-11-26 20:23:14', 'active', 3, 0, 0.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`alert_id`),
  ADD KEY `borrower_id` (`borrower_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `transaction_id` (`transaction_id`);

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
  ADD KEY `borrower_id` (`borrower_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `fk_penalty_transaction` (`transaction_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `alert_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `author_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `borrowingtransactions`
--
ALTER TABLE `borrowingtransactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  MODIFY `penalty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `alerts_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`),
  ADD CONSTRAINT `alerts_ibfk_3` FOREIGN KEY (`transaction_id`) REFERENCES `borrowingtransactions` (`transaction_id`);

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
  ADD CONSTRAINT `fk_penalty_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `borrowingtransactions` (`transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penalties_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `borrowingtransactions` (`transaction_id`),
  ADD CONSTRAINT `penalties_ibfk_2` FOREIGN KEY (`borrower_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `penalties_ibfk_3` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
