-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 24, 2015 at 12:52 PM
-- Server version: 5.6.11
-- PHP Version: 5.5.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `rpac_payroll`
--

-- --------------------------------------------------------

--
-- Table structure for table `module_page`
--

CREATE TABLE IF NOT EXISTS `module_page` (
  `module_page_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_headline` varchar(255) DEFAULT NULL,
  `module_page_title` varchar(255) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `rules_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`module_page_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=33 ;

--
-- Dumping data for table `module_page`
--

INSERT INTO `module_page` (`module_page_id`, `module_headline`, `module_page_title`, `status`, `rules_id`) VALUES
(1, 'Upload Attendance File', 'job_card/upload_folder.php', 'true', 4),
(2, 'Company', 'company/index.php', 'true', 4),
(3, 'Country', 'country/index.php', 'true', 4),
(4, 'City', 'city/index.php', 'true', 4),
(5, 'Department', 'department/index.php', 'true', 4),
(6, 'Subsection', 'subsection/index.php', 'true', 4),
(7, 'Designation', 'designation/index.php', 'true', 4),
(8, 'Staff Grade', 'staff_grade/index.php', 'true', 4),
(9, 'Attendance Policy', 'attendance_policy/index.php', 'true', 4),
(10, 'Shift Settings', 'shift/index.php', 'true', 4),
(11, 'Shift Swap', 'shift_schdule/shift_swift.php', 'true', 4),
(12, 'Shift and Users', 'shift_schdule/index.php', 'true', 4),
(13, 'Reporting Method', 'reporting_method/index.php', 'true', 4),
(14, 'Holiday', 'holiday/index.php', 'true', 4),
(15, 'Month Settings', 'dates/index.php', 'true', 4),
(16, 'Dashboard', 'dashboard/index.php', 'true', 5),
(17, 'Job Card', 'job_card/edit_1.php', 'true', 2),
(18, 'OT Report', 'report/ot_report.php', 'true', 3),
(19, 'Additional OT Report', 'report/additional_ot.php', 'true', 3),
(20, 'Daily Attendance Report', 'report/daily_attendance_report.php', 'true', 3),
(21, 'Missing Machine Punch', 'report/missing_outtime.php', 'true', 3),
(22, 'Add Employee', 'employee/add.php', 'true', 1),
(23, 'All Employee', 'user_management/index.php', 'true', 1),
(24, 'Assign Permissions', 'modulewise_page/index.php', 'true', 6),
(26, 'Manage Role', 'employee_role/index.php', 'true', 6),
(28, 'All Struckoff', 'struckoff/all.php', 'true', 7),
(29, 'List of Employees', 'struckoff/index.php', 'true', 7),
(32, 'Assign To Group', 'modulewise_page/assign_Togroup.php', 'true', 6);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
