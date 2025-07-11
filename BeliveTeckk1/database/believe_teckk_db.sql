-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2025 at 01:37 PM
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
-- Database: `believe_teckk_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `created_at`, `updated_at`) VALUES
(1, 'believeTeckk', '$2y$10$DRpTMV1oBvFLDy/CSF.OxevvWyTsAfm3E9heoyln7vH4s2keV4Jyu', 'believebrat@gmail.com', '2025-04-02 10:37:20', '2025-05-20 08:30:16');

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `author_name` varchar(100) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `category_id`, `title`, `slug`, `content`, `excerpt`, `featured_image`, `author_id`, `author_name`, `status`, `published_at`, `created_at`, `updated_at`, `is_active`) VALUES
(1, NULL, 'The Future is Now: Embracing Tech to Build a Better Tomorrow', 'the-future-is-now-embracing-tech-to-build-a-better-tomorrow', 'In today’s fast-paced world, technology is no longer a luxury — it’s a necessity. From streamlining business operations to enhancing education and healthcare, technology is shaping the way we live, learn, and grow. At BelieveTeck, we believe in harnessing the power of innovation to create real, lasting impact.\r\n\r\n💡 Why Tech Matters More Than Ever\r\nEvery second, a new solution is born. Startups are solving age-old problems with a few lines of code, and AI is transforming industries at breakneck speed. Whether you\'re a student, entrepreneur, developer, or small business owner, embracing digital tools can open doors to opportunities you never imagined.\r\n\r\nHere’s what’s trending and why it matters:\r\n\r\nAI & Automation are saving time and reducing human error in everything from customer support to agriculture.\r\n\r\nCloud Solutions make collaboration possible from anywhere, giving rise to global teamwork.\r\n\r\nMobile App Development is allowing businesses to reach customers in the palm of their hands.\r\n\r\nCybersecurity is becoming a frontline defense as we trust tech with our most sensitive data.\r\n\r\n🌱 Building Tech for Communities\r\nAt BelieveTeck, we’re passionate about using technology to solve real community challenges. Projects like Okuani Dwaso (a farmers’ marketplace), Hostel Room Allocation System, and Digital Transcript Systems are just the beginning. Our goal is simple: empower people with smart solutions tailored to their needs.\r\n\r\n🔧 What We’re Working On\r\nFrom intuitive websites to scalable apps, we’re building platforms that serve real users. Our focus areas include:\r\n\r\nWeb & Mobile Development\r\n\r\nUI/UX Design\r\n\r\nIT Consulting\r\n\r\nDigital Marketing\r\n\r\nCloud Integration\r\n\r\nTraining & Mentorship\r\n\r\n✨ Final Thoughts\r\nTech isn\'t just about machines — it\'s about people. Let’s keep pushing boundaries, learning, and creating tools that make life better for everyone.\r\n\r\nIf you\'re passionate about tech and want to collaborate, feel free to reach out. Let\'s build the future — one line of code at a time.', NULL, 'uploads/blog/67fe8d8ec1096.jpg', NULL, 'Evans Adu', 'published', '2025-04-15 16:47:10', '2025-04-15 16:47:10', '2025-04-15 16:47:10', 1),
(2, NULL, 'Welcome to the BelieveTeck Blog', 'welcome-to-the-believeteck-blog', 'Welcome to the official blog of BelieveTeck — your new home for tech tutorials, digital innovation stories, startup tips, and software development insights tailored for Ghana and Africa.\r\n\r\nAt BelieveTeck, we don’t just build websites and apps — we solve real-world problems through technology. From helping farmers reach better markets with Okuani Dwaso, to building school management and hostel allocation systems, everything we do is focused on impact, innovation, and simplicity.\r\n\r\n🚀 What You’ll Find on This Blog:\r\nWeb Development Tutorials – PHP, MySQL, JavaScript, Laravel, Tailwind, and more.\r\n\r\nReal Project Case Studies – Stories behind the systems we build, with code samples.\r\n\r\nTech for Ghana & Africa – How local problems inspire digital solutions.\r\n\r\nEntrepreneurship & Startups – Building a tech business from the ground up.\r\n\r\nDigital Security Tips – For small businesses and individuals.\r\n\r\nBeginner-Friendly Coding – Whether you\'re a SHS student or a university learner.\r\n\r\n🧑‍💻 Who Should Read This?\r\nStudents in tech and ICT\r\n\r\nFreelance developers in Ghana and beyond\r\n\r\nEntrepreneurs and small business owners\r\n\r\nCurious minds who want to learn how tech is changing lives\r\n\r\nLet’s build a digital future where everyone has access to tools, knowledge, and opportunities.\r\n\r\n“At BelieveTeck, we don’t just teach tech — we use it to transform lives.”\r\n\r\n', NULL, 'uploads/blog/681cb5c9839cf.jpg', NULL, 'Evans Adu – Founder & Lead Developer, BelieveTeck', 'published', '2025-05-08 13:46:49', '2025-05-08 13:46:49', '2025-05-08 13:46:49', 1);

-- --------------------------------------------------------

--
-- Table structure for table `careers`
--

CREATE TABLE `careers` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `careers`
--

INSERT INTO `careers` (`id`, `title`, `slug`, `description`, `requirements`, `benefits`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Software Developer', 'software-developer', 'We are looking for a skilled software developer.', 'Experience with PHP, JavaScript, and MySQL.', 'Healthcare, 401k, Paid time off', 1, '2025-03-30 12:55:00', '2025-03-30 12:55:00'),
(2, 'Graphic Designer', 'graphic-designer', 'Join our creative team as a graphic designer.', 'Proficient in Adobe Suite, Creative design skills.', 'Flexible hours, Work from home, Health benefits', 1, '2025-03-30 12:55:00', '2025-03-30 12:55:00');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `order_index` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `logo`, `website`, `order_index`, `created_at`, `updated_at`, `status`) VALUES
(1, 'Heritage Christian University', 'uploads/clients/682791c730323.png', 'https://www.hcu.edu.gh', 0, '2025-05-16 19:28:07', '2025-05-16 19:28:07', 'approved'),
(2, 'Cystal Ice', 'uploads/clients/6827926433636.png', 'https://www.crystalice.com', 0, '2025-05-16 19:30:44', '2025-05-16 19:30:44', 'approved'),
(3, 'Green Harvest Ghana', 'uploads/clients/682797ab1da61.jpg', 'https://www.greenharvest.com', 0, '2025-05-16 19:53:15', '2025-05-16 19:53:15', 'approved'),
(4, 'Believe', 'uploads/clients/6827b52bc5ffb.png', 'http://localhost/BelieveTeckk1', 0, '2025-05-16 21:59:07', '2025-05-16 21:59:07', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `duration_weeks` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(20) DEFAULT 'draft',
  `level` varchar(20) NOT NULL DEFAULT 'beginner'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `price`, `duration_weeks`, `created_at`, `updated_at`, `status`, `level`) VALUES
(1, 'Web Development for Beginners', 'Learn how to build beautiful, responsive websites from scratch using HTML, CSS, JavaScript, and Bootstrap. This course covers the fundamentals of front-end development with practical projects.', 200.00, 4, '2025-05-08 17:56:46', '2025-05-08 17:56:46', 'published', 'beginner');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_status` enum('pending','completed','rejected') NOT NULL DEFAULT 'pending',
  `payment_amount` decimal(10,2) NOT NULL,
  `payment_proof` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `enrollment_date`, `payment_status`, `payment_amount`, `payment_proof`) VALUES
(1, 1, 1, '2025-05-09 13:36:23', 'pending', 200.00, 'uploads/payments/681e04d764da5.png');

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','in_progress','completed','archived') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiries`
--

INSERT INTO `inquiries` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 'believe  Lincoln  brat', 'evoppong36@gmail.com', '0558092525', 'want to join the company', 'I have read more about your company and i would like to join', 'completed', '2025-04-12 15:34:58', '2025-04-12 15:40:57'),
(2, 'Lana', 'yngpryncez125@gmail.com', '0561928638', 'message', 'Want to know more about your website', 'completed', '2025-04-13 10:42:02', '2025-04-15 18:53:40'),
(3, 'Lana', 'yngpryncez125@gmail.com', '0561928638', 'message', 'Want to know more about your website', 'completed', '2025-04-13 10:42:22', '2025-04-15 18:53:37'),
(4, 'Lana', 'yngpryncez125@gmail.com', '0561928638', 'message', 'Want to know more about your website', 'completed', '2025-04-13 10:42:32', '2025-04-15 18:53:34');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `career_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `type` enum('full-time','part-time','contract','internship') NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `responsibilities` text DEFAULT NULL,
  `salary_range` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `career_id`, `title`, `location`, `type`, `description`, `requirements`, `responsibilities`, `salary_range`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 2, 'Head of Design', 'accra', 'part-time', 'To manage designs and creative visuals', '3 years in designing', 'lead the team', '1000 - 3000', 1, '2025-03-30 12:55:48', '2025-04-15 18:07:35'),
(2, 1, 'Frontend Developer', 'Accra, Ghana / Remote', 'full-time', 'We are seeking a passionate and detail-oriented Frontend Developer to join our creative tech team at BelieveTeck. You will be responsible for creating visually engaging and interactive user interfaces for our web and mobile platforms.', 'Bachelor’s degree in Computer Science, Information Technology, or a related field.\r\nMinimum of 2 years of professional experience in front-end development.\r\nStrong proficiency in HTML, CSS (Tailwind/Bootstrap), and JavaScript.\r\nExperience with modern frameworks like React, Vue, or Angular.\r\nFamiliarity with RESTful APIs and version control systems (e.g., Git).\r\nUnderstanding of responsive design and cross-browser compatibility.\r\nGood communication and teamwork abilities.\r\nA portfolio or GitHub with past front-end projects is a plus.', 'Develop and maintain responsive user interfaces for web and mobile platforms.\r\n\r\nCollaborate with designers, backend developers, and product managers to deliver high-quality features.\r\nConvert UI/UX design wireframes into functional components using HTML, CSS, and JavaScript frameworks.\r\nOptimize applications for speed, performance, and scalability.\r\nParticipate in daily stand-ups and sprint planning sessions.\r\nEnsure cross-browser and mobile compatibility across all frontend outputs.\r\nDebug and troubleshoot frontend-related issues and provide timely resolutions\r\nImplement feedback from users and stakeholders to improve UI/UX.\r\nMaintain clean, reusable, and well-documented code.\r\nStay up-to-date with new frontend technologies and best practices.\r\n\r\n', ' GHC 2,000 - GHC3,500 per project', 1, '2025-04-12 15:28:32', '2025-04-15 18:07:32');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `cover_letter` text DEFAULT NULL,
  `status` enum('pending','reviewed','shortlisted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`id`, `job_id`, `name`, `email`, `phone`, `resume_path`, `cover_letter`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Nneoma Kucharski', 'evoppong36@gmail.com', '0558092525', 'uploads/resumes/67ed3f9bd3523.docx', 'hello', 'pending', '2025-04-02 13:46:03', '2025-04-12 15:15:45');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_campaigns`
--

CREATE TABLE `newsletter_campaigns` (
  `id` int(11) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` enum('draft','scheduled','sent','failed') DEFAULT 'draft',
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscriptions`
--

CREATE TABLE `newsletter_subscriptions` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` enum('active','unsubscribed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newsletter_subscriptions`
--

INSERT INTO `newsletter_subscriptions` (`id`, `email`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'evoppong36@gmail.com', 'believe  Lincoln  brat', 'active', '2025-03-31 15:40:04', '2025-04-15 19:18:02');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_templates`
--

CREATE TABLE `newsletter_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newsletter_templates`
--

INSERT INTO `newsletter_templates` (`id`, `name`, `subject`, `content`, `image_url`, `created_at`, `updated_at`) VALUES
(1, '', 'Thank you to Our Newsletter', 'Hello, i want to use this opportunity to thank you on this platform', NULL, '2025-03-31 15:50:24', '2025-03-31 19:07:08'),
(2, 'We’re Hiring! Join Our Innovative IT Team at BelieveTeck', 'We\'re Looking for Brilliant Minds in Tech!', 'Dear Tech Enthusiast,\r\n\r\nExciting news from BelieveTeck! We\'re expanding our team and looking for passionate, creative, and skilled individuals to join us in shaping the future of technology.\r\n\r\nAs a fast-growing tech company specializing in Web & Mobile Development, UI/UX Design, IT Consulting, Cloud Solutions, and more — we are on the lookout for talent that thrives on innovation and impact.\r\n\r\n💼 Current Openings:\r\nFrontend Developer (HTML/CSS/JavaScript)\r\n\r\nBackend Developer (PHP/MySQL)\r\n\r\nMobile App Developer (Flutter/React Native)\r\n\r\nUI/UX Designer\r\n\r\nCybersecurity Analyst\r\n\r\nCloud Engineer (AWS/GCP/Azure)\r\n\r\nIT Support & Systems Admin\r\n\r\n🌟 What We Offer:\r\nA collaborative and creative work environment\r\n\r\nHands-on projects with real impact\r\n\r\nRemote and hybrid work options\r\n\r\nGrowth opportunities and continuous learning\r\n\r\nCompetitive salaries and bonuses\r\n\r\n📍 Location:\r\nRemote & On-site (Accra, Ghana)\r\n\r\n📅 Deadline to Apply:\r\nApril 30, 2025\r\n\r\n👉 How to Apply:\r\nSend your CV, portfolio (if any), and a short cover letter to careers@believeteck.com.\r\nUse the subject line: Application – [Your Role] – [Your Name]', NULL, '2025-04-12 15:04:13', '2025-04-12 15:04:13'),
(3, 'Nneoma Kucharski', 'believebrat@gmail.com', 'Tech isn\'t just about machines — it\'s about people. Let’s keep pushing boundaries, learning, and creating tools that make life better for everyone.\r\n\r\nIf you\'re passionate about tech and want to collaborate, feel free to reach out. Let\'s build the future — one line of code at a time.', '', '2025-04-15 19:19:15', '2025-04-15 19:19:15');

-- --------------------------------------------------------

--
-- Table structure for table `page_contents`
--

CREATE TABLE `page_contents` (
  `id` int(11) NOT NULL,
  `page_slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `mission` text DEFAULT NULL,
  `vision` text DEFAULT NULL,
  `values` text DEFAULT NULL,
  `founder_name` varchar(100) DEFAULT NULL,
  `founder_position` varchar(100) DEFAULT NULL,
  `founder_image` varchar(255) DEFAULT NULL,
  `founder_content` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_contents`
--

INSERT INTO `page_contents` (`id`, `page_slug`, `title`, `content`, `mission`, `vision`, `values`, `founder_name`, `founder_position`, `founder_image`, `founder_content`, `meta_description`, `meta_keywords`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'home', 'Welcome to Believe Teckk', NULL, 'Welcome to Believe Teckk - Your Technology Partner', 'Our vision for innovation', 'Excellence, Innovation, Integrity', NULL, NULL, NULL, NULL, '', '', 1, '2025-03-30 10:26:36', '2025-04-02 14:20:19'),
(2, 'about', 'About Us', 'At BelieveTeck, we are passionate about leveraging technology to drive innovation and create transformative digital experiences. Founded on the principles of excellence, creativity, and reliability, our mission is to empower businesses and individuals with cutting-edge IT solutions. From web and mobile development to cloud solutions and IT consulting, we are committed to delivering excellence at every stage.', 'To provide innovative, reliable, and scalable tech solutions that empower businesses and individuals to thrive in the digital age. We aim to bridge the gap between ideas and execution, turning visions into reality through technology.', 'To be a global leader in IT solutions and digital transformation, recognized for our innovation, expertise, and commitment to client success. We envision a world where technology simplifies lives, enhances productivity, and fuels progress.', 'At BelieveTeck, we are driven by innovation, embracing creativity and technology to redefine industries. Integrity is at our core, fostering trust through honesty and transparency. We uphold excellence, delivering top-tier solutions that meet the highest standards. Our customer-centric approach ensures we prioritize client success with tailored solutions. Above all, we value reliability, providing 24/7 support for seamless operations.', 'Adu Evans', 'Founder', 'uploads/founder/founder_1744552068.png', 'Evans Adu, the visionary behind BelieveTeck, is a dynamic tech entrepreneur, software engineer, and innovator with a passion for solving real-world problems through technology. With expertise spanning web development, mobile app development, cybersecurity, UI/UX design, and IT consulting, he has dedicated his career to creating impactful digital solutions. Driven by a mission to empower businesses and individuals, Believe Brat has transformed BelieveTeck into a powerhouse of innovation, helping clients harness the full potential of technology. His leadership, technical prowess, and commitment to excellence set the foundation for a company that delivers not just solutions\'; but results. \r\n\"Technology is not just about codes and algorithms; it\'s about solving problems, improving lives, and shaping the future.\"\r\n - Evans Adu', 'Learn more about Believe Teckk and our commitment to excellence', 'about us, company profile, technology company', 1, '2025-03-30 10:26:36', '2025-04-13 13:56:36'),
(3, 'services', 'Our Services', NULL, 'Explore our comprehensive range of technology services', 'Innovative solutions for modern businesses', 'Quality, Reliability, Innovation', NULL, NULL, NULL, NULL, '', '', 1, '2025-03-30 10:26:36', '2025-04-02 14:22:24'),
(4, 'portfolio', 'Our Portfolio', '', 'View our portfolio of successful projects', 'Showcasing our best work', 'Excellence in Delivery', NULL, NULL, NULL, NULL, 'Explore our successful projects and implementations', 'portfolio, projects, case studies', 1, '2025-03-30 10:26:36', '2025-03-30 10:26:36'),
(5, 'blog', 'Blog', '', 'Read our latest articles and insights', 'Stay updated with technology trends', 'Knowledge Sharing', NULL, NULL, NULL, NULL, 'Latest insights and updates from our technology experts', 'blog, articles, technology news', 1, '2025-03-30 10:26:36', '2025-03-30 10:26:36'),
(6, 'contact', 'Contact Us', '', 'Get in touch with Believe Teckk', 'Let discuss your next project', 'Customer Focus', NULL, NULL, NULL, NULL, 'Contact Believe Teckk for your technology needs', 'contact, get in touch, support', 1, '2025-03-30 10:26:36', '2025-03-30 10:26:36');

-- --------------------------------------------------------

--
-- Table structure for table `portfolio_categories`
--

CREATE TABLE `portfolio_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portfolio_categories`
--

INSERT INTO `portfolio_categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Web Development', 'web-development', 'Web development projects showcasing our expertise in building modern web applications', '2025-03-30 09:12:10', '2025-04-15 19:00:08'),
(2, 'Mobile Apps', 'mobile-apps', 'Mobile application development projects for iOS and Android platforms', '2025-03-30 09:12:10', '2025-04-15 19:00:16'),
(3, 'UI/UX Design', 'ui-ux-design', 'User interface and user experience design projects', '2025-03-30 09:12:10', '2025-03-30 09:12:10'),
(4, 'Digital Marketing', 'digital-marketing', 'Digital marketing campaigns and strategies', '2025-03-30 09:12:10', '2025-03-30 09:12:10'),
(5, 'Cloud Solutions', 'cloud-solutions', 'Cloud infrastructure and deployment projects', '2025-03-30 09:12:10', '2025-03-30 09:12:10'),
(6, 'Animation', 'animation', 'For all animated works', '2025-03-30 09:40:37', '2025-04-15 18:59:59'),
(7, 'Graphic Designs', 'graphic-designs', 'Bringing your dream design into reality', '2025-03-31 13:39:20', '2025-04-15 18:59:40');

-- --------------------------------------------------------

--
-- Table structure for table `portfolio_items`
--

CREATE TABLE `portfolio_items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `client` varchar(100) DEFAULT NULL,
  `project_date` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `technologies` text DEFAULT NULL,
  `project_url` varchar(255) DEFAULT NULL,
  `testimonial` text DEFAULT NULL,
  `testimonial_author` varchar(100) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portfolio_items`
--

INSERT INTO `portfolio_items` (`id`, `category_id`, `title`, `slug`, `description`, `content`, `client`, `project_date`, `image`, `technologies`, `project_url`, `testimonial`, `testimonial_author`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Smart Attendance System', '', 'Marking attendance with QR code', 'N/A', 'Heritage Christian University', '2025-04-06', 'uploads/portfolio/67fa76dbf2b5a.png', 'PHP, MySQL, JavaScript', 'http://localhost/smart-attendance', 'I love the outcome of the project, and it was delivered on time as discussed', 'Kelvin', 1, 1, '2025-04-12 14:21:15', '2025-05-16 22:01:57');

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `title`, `slug`, `description`, `icon`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Web Development', 'web-development', 'Custom web development solutions using the latest technologies and best practices.', 'fas fa-code', 1, '2025-03-29 18:45:41', '2025-03-29 18:45:41'),
(2, 'Mobile App Development', 'mobile-app-development', 'Native and cross-platform mobile application development for iOS and Android.', 'fas fa-mobile-alt', 1, '2025-03-29 18:45:41', '2025-03-29 18:45:41'),
(3, 'UI/UX Design', 'ui-ux-design', 'User-centered design solutions that enhance user experience and engagement.', 'fas fa-paint-brush', 1, '2025-03-29 18:45:41', '2025-03-29 18:45:41'),
(4, 'Digital Marketing', 'digital-marketing', 'Comprehensive digital marketing strategies to grow your online presence.', 'fas fa-bullhorn', 1, '2025-03-29 18:45:41', '2025-03-29 18:45:41'),
(5, 'Cloud Solutions', 'cloud-solutions', 'Cloud infrastructure and deployment solutions for scalable applications.', 'fas fa-cloud', 1, '2025-03-29 18:45:41', '2025-03-29 18:45:41'),
(6, 'IT Consulting', 'it-consulting', 'Expert IT consulting services to help you make informed technology decisions.', 'fas fa-lightbulb', 1, '2025-03-29 18:45:41', '2025-03-29 18:45:41'),
(7, 'Training', 'training', 'We train learners in various tech skill', 'fas fa-award', 1, '2025-03-31 13:54:50', '2025-04-01 11:54:02'),
(8, 'Data Analystics', 'data-analystics', 'A Data Analyst is a professional who collects and analyzes data across the business to make informed decisions or assist other team members and leadership in making sound decisions.', 'fa-solid fa-user-secret', 1, '2025-04-17 17:29:35', '2025-04-17 17:35:02');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Believe Teckk', 'general', '2025-03-30 09:09:39', '2025-03-30 09:09:39'),
(2, 'site_description', 'Your Trusted Technology Partner', 'general', '2025-03-30 09:09:39', '2025-03-30 09:09:39'),
(3, 'contact_email', 'believebrat@gmail.com', 'contact', '2025-03-30 09:09:39', '2025-03-31 15:37:13'),
(4, 'contact_phone', '+233558092525', 'contact', '2025-03-30 09:09:39', '2025-03-30 12:40:21'),
(5, 'contact_address', 'Accra Ghana', 'contact', '2025-03-30 09:09:39', '2025-03-30 12:39:55'),
(6, 'social_linkedin', 'https://linkedin.com/company/believeteckk', 'social', '2025-03-30 09:09:39', '2025-03-30 09:09:39'),
(7, 'social_twitter', 'https://twitter.com/believeteckk', 'social', '2025-03-30 09:09:39', '2025-03-30 09:09:39'),
(8, 'social_facebook', 'https://facebook.com/believeteckk', 'social', '2025-03-30 09:09:39', '2025-03-30 09:09:39'),
(9, 'google_analytics_id', '', 'integration', '2025-03-30 09:09:39', '2025-03-30 09:09:39'),
(10, 'recaptcha_site_key', '', 'integration', '2025-03-30 09:09:39', '2025-03-30 09:09:39'),
(11, 'recaptcha_secret_key', '', 'integration', '2025-03-30 09:09:39', '2025-03-30 09:09:39'),
(12, 'tinymce_api_key', '0p96e0w162qkno4pdy6anlcdg615r53ej8uco22xrysdayg5', 'integration', '2025-03-30 09:09:39', '2025-03-30 09:09:39'),
(13, 'working_hours', 'Monday - friday (7:00 am to 7:00 pm)', 'contact', '2025-04-03 11:59:56', '2025-04-03 12:01:53');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `bio` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `order_index` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `position`, `bio`, `image`, `linkedin_url`, `twitter_url`, `order_index`, `is_active`, `created_at`, `updated_at`) VALUES
(4, 'Believe', 'Project Manager', 'cccc', 'uploads/team/67fe95a6bb14b_1744737702.jpg', '', '', 1, 0, '2025-04-15 17:21:42', '2025-04-15 18:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_position` varchar(100) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `testimonial` text NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `client_name`, `client_position`, `company_name`, `testimonial`, `rating`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Lawarencia Naa', 'CEO', 'Crystal Ice Ghana', 'Partnering with Believe Teckk was one of the best decisions we\'ve made for our company. They developed a modern, secure website and an inventory system tailored to our daily operations. Their team took time to understand our business and delivered exactly what we needed. Today, we manage our orders, clients, and deliveries more efficiently. Believe Teckk is not just a service provider—they\'re a tech partner we trust.', 5, 'uploads/testimonials/68279a9f6e2f1.png', 'approved', '2025-05-16 19:42:21', '2025-05-16 20:05:51'),
(2, 'Kwame Agyeman', 'Founder', 'GreenHarvest Ghana', 'Believe Teckk transformed our online presence with a stunning website that’s fast, secure, and user-friendly. Their attention to detail and understanding of our needs made the whole process smooth. We\'ve seen a significant increase in customer engagement since launching the new site!', 5, 'uploads/testimonials/6827971464102.jpg', 'approved', '2025-05-16 19:50:44', '2025-05-16 20:19:37'),
(3, 'Esi Nyarko', 'COO', 'EduLink Africa', 'I was amazed at how quickly Believe Teckk delivered our mobile app. They understood the assignment, offered great suggestions, and built something that’s both beautiful and functional. Our users love it, and so do we!', 5, '', 'approved', '2025-05-16 19:56:54', '2025-05-16 19:56:54'),
(4, 'Raymond Ofori', 'Product Lead', 'ShopNowGH', 'Believe Teckk brought our vision to life with a clean, intuitive design. Their UI/UX team thinks like real users, and it shows. Every screen they designed had purpose and flow—it elevated our entire platform.', 4, '', 'approved', '2025-05-16 19:58:27', '2025-05-16 19:58:27'),
(5, 'Afia Boateng', 'Operations Manager', 'RoyalPrint Ghana', 'As a growing business, we needed expert IT advice—and Believe Teckk delivered just that. From infrastructure planning to security audits, their consulting helped us avoid costly mistakes and scale confidently.', 5, '', 'approved', '2025-05-16 19:59:25', '2025-05-16 19:59:25'),
(6, 'Daniel Tetteh', 'Junior Software Developer', '', 'Believe Teckk’s mentorship program helped me land my first tech job. Their practical training, real-world projects, and consistent support gave me the confidence and skills I needed. I’m proud to be one of their success stories', 5, '', 'approved', '2025-05-16 20:00:50', '2025-05-16 20:00:50');

-- --------------------------------------------------------

--
-- Table structure for table `training_programs`
--

CREATE TABLE `training_programs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `duration` varchar(100) NOT NULL,
  `price` varchar(100) NOT NULL,
  `whatsapp_group` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training_programs`
--

INSERT INTO `training_programs` (`id`, `title`, `description`, `duration`, `price`, `whatsapp_group`, `created_at`, `updated_at`) VALUES
(1, 'Mobile App Development', 'Master mobile app development for iOS and Android platforms using React Native.', '4 months', '1299', 'https://chat.whatsapp.com/example-group-2', '2025-03-29 18:45:15', '2025-04-01 11:55:54'),
(2, 'UI/UX Design Fundamentals', 'Learn the principles of user interface and user experience design.', '2 months', '799', 'https://chat.whatsapp.com/example-group-3', '2025-03-29 18:45:15', '2025-04-01 11:56:11');

-- --------------------------------------------------------

--
-- Table structure for table `training_registrations`
--

CREATE TABLE `training_registrations` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training_registrations`
--

INSERT INTO `training_registrations` (`id`, `program_id`, `name`, `email`, `phone`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Nneoma Kucharski', 'believebrat@gmail.com', '0558092525', '', 'approved', '2025-04-02 10:46:26', '2025-04-02 10:52:50'),
(2, 2, 'Nneoma Kucharski', 'evoppong36@gmail.com', '0558092525', 'hekko', 'approved', '2025-04-02 13:37:23', '2025-04-02 13:48:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor') NOT NULL DEFAULT 'editor',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Believe', 'admin@believeteckk.com', '$2y$10$DRpTMV1oBvFLDy/CSF.OxevvWyTsAfm3E9heoyln7vH4s2keV4Jyu', 'admin', 1, '2025-03-29 20:18:07', '2025-04-02 10:32:33');

-- --------------------------------------------------------

--
-- Table structure for table `visitor_logs`
--

CREATE TABLE `visitor_logs` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `page_visited` varchar(255) NOT NULL,
  `visit_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `careers`
--
ALTER TABLE `careers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `career_id` (`career_id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `newsletter_campaigns`
--
ALTER TABLE `newsletter_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_id` (`template_id`);

--
-- Indexes for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `newsletter_templates`
--
ALTER TABLE `newsletter_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_contents`
--
ALTER TABLE `page_contents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_slug` (`page_slug`);

--
-- Indexes for table `portfolio_categories`
--
ALTER TABLE `portfolio_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `portfolio_items`
--
ALTER TABLE `portfolio_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `training_programs`
--
ALTER TABLE `training_programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`);

--
-- Indexes for table `training_registrations`
--
ALTER TABLE `training_registrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `visitor_logs`
--
ALTER TABLE `visitor_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `careers`
--
ALTER TABLE `careers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `newsletter_campaigns`
--
ALTER TABLE `newsletter_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `newsletter_templates`
--
ALTER TABLE `newsletter_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `page_contents`
--
ALTER TABLE `page_contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `portfolio_categories`
--
ALTER TABLE `portfolio_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `portfolio_items`
--
ALTER TABLE `portfolio_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `training_programs`
--
ALTER TABLE `training_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `training_registrations`
--
ALTER TABLE `training_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `visitor_logs`
--
ALTER TABLE `visitor_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
