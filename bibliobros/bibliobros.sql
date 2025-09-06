-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-06-2025 a las 11:05:19
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bibliobros`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chats`
--

CREATE TABLE `chats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `chats`
--

INSERT INTO `chats` (`id`, `request_id`, `created_at`, `active`) VALUES
(1, 2, '2025-06-15 18:24:46', 0),
(2, 16, '2025-06-17 12:25:46', 1),
(3, 24, '2025-06-17 12:29:09', 1),
(4, 17, '2025-06-17 12:34:42', 1),
(5, 9, '2025-06-18 09:44:35', 1),
(6, 21, '2025-06-18 09:44:41', 1),
(7, 13, '2025-06-18 09:44:44', 0),
(8, 26, '2025-06-19 11:20:03', 0),
(9, 27, '2025-06-21 09:00:22', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `faculties`
--

CREATE TABLE `faculties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `university_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `faculties`
--

INSERT INTO `faculties` (`id`, `university_id`, `name`, `description`) VALUES
(1, 1, 'Architecture', 'School of Architecture and Urban Planning, focusing on architectural design, sustainable urbanism, and heritage conservation.'),
(2, 1, 'Economics', 'School of Economics and Business, offering studies in macroeconomics, finance, and international trade.'),
(3, 1, 'Pharmacy and Medicine', 'Faculty of Pharmacy and Medicine, dedicated to biomedical research, clinical pharmacology, and public health.'),
(4, 1, 'Law', 'Faculty of Law, covering civil, criminal, international law, and human rights advocacy.'),
(5, 1, 'Civil and Industrial Engineering', 'Engineering faculty specializing in structural engineering, construction management, and industrial systems.'),
(6, 1, 'Information Engineering, Computer Science and Statistics', 'Faculty covering data science, artificial intelligence, network engineering, and applied statistics.'),
(7, 1, 'Humanities and Philosophy', 'Faculty of Humanities, including literature studies, philosophy, linguistics, and cultural analysis.'),
(8, 1, 'Medicine and Dentistry', 'Faculty of Medicine and Dentistry, with programs in clinical medicine, dental surgery, and oral health.'),
(9, 1, 'Medicine and Psychology', 'Interdisciplinary faculty combining medical sciences and clinical psychology for holistic care.'),
(10, 1, 'Mathematical, Physical and Natural Sciences', 'Faculty dedicated to pure mathematics, theoretical physics, chemistry, and environmental biology.'),
(11, 1, 'Political Science, Sociology and Communication', 'Faculty exploring public policy, social theory, and media studies.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mentor_subject`
--

CREATE TABLE `mentor_subject` (
  `user_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `intro` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mentor_subject`
--

INSERT INTO `mentor_subject` (`user_id`, `subject_id`, `intro`) VALUES
(2, 5, 'I teach Microeconomics at Sapienza University'),
(2, 7, 'Passionate about data analysis and econometrics'),
(3, 9, 'I love helping students understand business concepts'),
(3, 10, 'Marketing made simple with real-world examples'),
(5, 2, 'hello'),
(5, 3, 'asdadsd'),
(5, 4, 'Hi, my name is Juan and I´m willing to help you with Structural mechanichs'),
(5, 7, 'Im willing to help'),
(6, 1, 'sdada'),
(6, 2, 'HOLA SOY UNA MENTOR'),
(27, 1, 'Specialized in historical urban development.'),
(27, 2, 'Experienced in conceptual design and critiques.'),
(27, 3, 'Worked with sustainable materials in practice.'),
(27, 4, 'Expertise in mechanical stress and architecture.'),
(28, 1, 'Love exploring classical architecture with students.'),
(28, 2, 'Guide students in early-stage design thinking.'),
(28, 3, 'Materials science enthusiast with lab experience.'),
(28, 4, 'I simplify mechanics through hands-on models.'),
(28, 7, 'I passed this lectures, I can help'),
(29, 1, 'Research assistant in architectural history.'),
(29, 2, 'Helped tutor students in studio projects.'),
(29, 3, 'Practical background in construction techniques.'),
(29, 4, 'Good at breaking down structural equations.'),
(30, 1, 'Focus on Renaissance architecture.'),
(30, 2, 'Studio critic for second-year students.'),
(30, 3, 'Knowledge in eco-friendly materials.'),
(30, 4, 'Passionate about structure and form.'),
(31, 1, 'I bring theory and history together in my sessions.'),
(31, 2, 'Studio design support from first sketch to model.'),
(31, 3, 'Construction site experience with real materials.'),
(31, 4, 'Clear explanation of forces and statics.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `chat_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `messages`
--

INSERT INTO `messages` (`id`, `chat_id`, `sender_id`, `content`, `timestamp`) VALUES
(1, 1, 6, 'hello', '2025-06-15 18:25:29'),
(2, 1, 6, 'hello', '2025-06-15 18:25:40'),
(3, 1, 6, 'hello', '2025-06-15 18:34:18'),
(4, 1, 6, 'hi', '2025-06-15 18:58:29'),
(5, 1, 5, 'hi', '2025-06-15 19:02:14'),
(6, 1, 5, 'wassup', '2025-06-15 20:11:16'),
(7, 1, 5, 'sda', '2025-06-15 20:25:30'),
(8, 1, 6, 'klk', '2025-06-15 20:39:41'),
(9, 2, 6, 'hi brother', '2025-06-17 12:25:50'),
(10, 3, 31, 'Hi mateo, what do you need help with?', '2025-06-17 12:29:20'),
(11, 3, 32, 'hi Chiara, im struggling with this subject a lot in general.', '2025-06-17 12:30:15'),
(12, 3, 32, 'sdada', '2025-06-17 12:30:22'),
(13, 4, 5, 'Hello, what do you need help with?', '2025-06-17 12:34:55'),
(14, 7, 31, 'Good morning Lorenzo, What can i help you with?', '2025-06-18 09:45:59'),
(15, 7, 34, 'Good morning Chiara, Thank you for accepting my question, i wanted to start with point 1.3', '2025-06-18 09:46:48'),
(16, 5, 31, 'hi', '2025-06-19 11:58:35'),
(17, 8, 31, 'asA', '2025-06-19 12:16:31'),
(18, 9, 28, 'Good morning Chiara, what do you need help with?', '2025-06-21 09:00:41'),
(19, 9, 31, 'Good morning Davide, Thank you for accepting the request, yes i need help', '2025-06-21 09:02:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ratings`
--

CREATE TABLE `ratings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `chat_id` int(11) NOT NULL,
  `score` int(11) NOT NULL CHECK (`score` between 1 and 5),
  `comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ratings`
--

INSERT INTO `ratings` (`id`, `chat_id`, `score`, `comment`) VALUES
(1, 1, 4, 'asdada'),
(2, 7, 4, 'THANK YOU FOR EVERYTHING, IT WAS VERY HELPFULL'),
(3, 8, 4, 'hE DID A GOOD JOB'),
(4, 9, 4, 'Thank you for your help');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `requests`
--

CREATE TABLE `requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` int(11) NOT NULL,
  `mentee_id` int(11) NOT NULL,
  `mentor_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `status` text NOT NULL DEFAULT 'pending' CHECK (`status` in ('pending','accepted','rejected','closed')),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `requests`
--

INSERT INTO `requests` (`id`, `subject_id`, `mentee_id`, `mentor_id`, `message`, `status`, `created_at`) VALUES
(1, 1, 5, NULL, 'dasda', 'pending', '2025-06-15 16:09:35'),
(2, 1, 5, 6, 'asaca', 'closed', '2025-06-15 16:38:34'),
(3, 6, 6, NULL, 'nECESITO AYUDA CON AQUITECTURA', 'pending', '2025-06-17 08:49:27'),
(4, 1, 32, NULL, 'Could someone help me understand the architectural styles covered in this course?', 'pending', '2025-06-17 11:27:28'),
(5, 2, 32, NULL, 'Struggling with my first design studio project. Need guidance.', 'pending', '2025-06-17 11:27:28'),
(6, 3, 32, NULL, 'Looking for help with construction materials and their applications.', 'pending', '2025-06-17 11:27:28'),
(7, 4, 32, NULL, 'Need clarification on structural force calculations.', 'pending', '2025-06-17 11:27:28'),
(8, 1, 33, NULL, 'I need support preparing for the architecture history exam.', 'pending', '2025-06-17 11:27:28'),
(9, 2, 33, 31, 'Can anyone review my studio design draft?', 'accepted', '2025-06-17 11:27:28'),
(10, 3, 33, NULL, 'I’m having trouble with material classifications.', 'pending', '2025-06-17 11:27:28'),
(11, 4, 33, NULL, 'Need assistance with mechanics-related homework.', 'pending', '2025-06-17 11:27:28'),
(12, 1, 34, NULL, 'What are the most important concepts from History of Architecture?', 'pending', '2025-06-17 11:27:28'),
(13, 2, 34, 31, 'Studio critiques are tough. Need advice.', 'closed', '2025-06-17 11:27:28'),
(14, 3, 34, NULL, 'Could someone explain thermal insulation materials?', 'pending', '2025-06-17 11:27:28'),
(15, 4, 34, NULL, 'I’m confused about stress distribution in beams.', 'pending', '2025-06-17 11:27:28'),
(16, 1, 35, 6, 'Looking for notes on Renaissance architecture.', 'accepted', '2025-06-17 11:27:28'),
(17, 2, 35, 5, 'Studio teacher gave complex feedback. Need interpretation.', 'accepted', '2025-06-17 11:27:28'),
(18, 3, 35, NULL, 'Searching for examples of eco-friendly materials.', 'pending', '2025-06-17 11:27:28'),
(19, 4, 35, NULL, 'Having trouble with equilibrium and forces.', 'pending', '2025-06-17 11:27:28'),
(20, 1, 36, NULL, 'Need help organizing study material for architecture history.', 'pending', '2025-06-17 11:27:28'),
(21, 2, 36, 31, 'Having issues starting my architectural design concept.', 'accepted', '2025-06-17 11:27:28'),
(22, 3, 36, NULL, 'Confused about construction layering techniques.', 'pending', '2025-06-17 11:27:28'),
(23, 4, 36, NULL, 'What’s the best way to understand moment diagrams?', 'pending', '2025-06-17 11:27:28'),
(24, 2, 32, 31, 'hi i need help', 'accepted', '2025-06-17 11:37:59'),
(25, 1, 5, NULL, 'i need help', 'pending', '2025-06-17 12:35:40'),
(26, 7, 31, 5, 'i need help', 'closed', '2025-06-19 11:12:02'),
(27, 7, 31, 28, 'I want Some help', 'closed', '2025-06-21 08:58:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `subjects`
--

INSERT INTO `subjects` (`id`, `faculty_id`, `name`) VALUES
(1, 1, 'History of Architecture I'),
(2, 1, 'Architectural Design Studio I'),
(3, 1, 'Building Materials and Construction Techniques'),
(4, 1, 'Structural Mechanics for Architects'),
(5, 2, 'Principles of Microeconomics'),
(6, 2, 'Principles of Macroeconomics'),
(7, 2, 'Introduction to Econometrics'),
(8, 3, 'Financial Accounting I'),
(9, 3, 'Principles of Management'),
(10, 3, 'Marketing Fundamentals'),
(11, 4, 'Human Anatomy and Physiology'),
(12, 4, 'Kinesiology and Biomechanics'),
(13, 4, 'Exercise Prescription and Programming');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `universities`
--

CREATE TABLE `universities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `universities`
--

INSERT INTO `universities` (`id`, `name`) VALUES
(1, 'Sapienza University of Rome'),
(2, 'University of Rome Tor Vergata'),
(3, 'Roma Tre University'),
(4, 'Foro Italico University');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT
  `fullname` text NOT NULL,
  `email` text NOT NULL,
  `password_hash` text NOT NULL,
  `university_id` int(11) NOT NULL,
  `public_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password_hash`, `university_id`, `public_description`) VALUES
(1, 'Alice Rossi', 'alice@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Economics student at Sapienza University'),
(2, 'Marco Bianchi', 'marco@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Teaching Assistant in Microeconomics'),
(3, 'Giulia Verdi', 'giulia@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Business and marketing expert'),
(4, 'Luca Neri', 'luca@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Business Administration undergraduate'),
(5, 'juan', 'juan@gmail.com', '$2y$10$DFJqIYFAO3ytBC1jqG4vG.55YTx1Yu8NHNVWR2bnxHnpBJMeXtlRG', 1, NULL),
(6, 'a', 'a@gmail.com', '$2y$10$uk.AoIL9HDaqGg.VDoQFluWip7Y7ozMHRbHmKjkQv28Sk4dVQD32y', 1, NULL),
(27, 'Elena Ferrari', 'elena.ferrari@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Architecture student passionate about sustainability'),
(28, 'Davide Russo', 'davide.russo@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Law undergraduate focused on international law'),
(29, 'Francesca Ricci', 'francesca.ricci@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Computer science major exploring AI and statistics'),
(30, 'Andrea Moretti', 'andrea.moretti@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Political science enthusiast and debater'),
(31, 'Chiara Gallo', 'chiara.gallo@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Economics student interested in finance'),
(32, 'Matteo Romano', 'matteo.romano@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Physics student focused on astrophysics'),
(33, 'Giulia Marchetti', 'giulia.marchetti@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Marketing and communication strategist'),
(34, 'Lorenzo Greco', 'lorenzo.greco@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Interested in biomechanics and sports science'),
(35, 'Beatrice Conti', 'beatrice.conti@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Pharmacy student researching clinical trials'),
(36, 'Federico Vitale', 'federico.vitale@example.com', '$2y$10$ny/fMf36fJKCwwKWVSEkBeeTgr/fkZRB2J83Uy6KSkQ31GNxsry7i', 1, 'Sociology major with interest in media studies');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_subject_role`
--

CREATE TABLE `user_subject_role` (
  `user_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `role` text NOT NULL CHECK (`role` in ('mentor','mentee'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_subject_role`
--

INSERT INTO `user_subject_role` (`user_id`, `subject_id`, `role`) VALUES
(1, 5, 'mentee'),
(1, 9, 'mentee'),
(2, 5, 'mentor'),
(2, 7, 'mentor'),
(3, 9, 'mentor'),
(3, 10, 'mentor'),
(4, 9, 'mentee'),
(4, 10, 'mentee'),
(5, 1, 'mentee'),
(5, 2, 'mentor'),
(5, 3, 'mentor'),
(5, 4, 'mentor'),
(5, 7, 'mentor'),
(6, 1, 'mentor'),
(6, 2, 'mentor'),
(6, 6, 'mentee'),
(27, 1, 'mentor'),
(27, 2, 'mentor'),
(27, 3, 'mentor'),
(27, 4, 'mentor'),
(28, 1, 'mentor'),
(28, 2, 'mentor'),
(28, 3, 'mentor'),
(28, 4, 'mentor'),
(28, 7, 'mentor'),
(29, 1, 'mentor'),
(29, 2, 'mentor'),
(29, 3, 'mentor'),
(29, 4, 'mentor'),
(30, 1, 'mentor'),
(30, 2, 'mentor'),
(30, 3, 'mentor'),
(30, 4, 'mentor'),
(31, 1, 'mentor'),
(31, 2, 'mentor'),
(31, 3, 'mentor'),
(31, 4, 'mentor'),
(31, 7, 'mentee'),
(32, 1, 'mentee'),
(32, 2, 'mentee'),
(32, 3, 'mentee'),
(32, 4, 'mentee'),
(33, 1, 'mentee'),
(33, 2, 'mentee'),
(33, 3, 'mentee'),
(33, 4, 'mentee'),
(34, 1, 'mentee'),
(34, 2, 'mentee'),
(34, 3, 'mentee'),
(34, 4, 'mentee'),
(35, 1, 'mentee'),
(35, 2, 'mentee'),
(35, 3, 'mentee'),
(35, 4, 'mentee'),
(36, 1, 'mentee'),
(36, 2, 'mentee'),
(36, 3, 'mentee'),
(36, 4, 'mentee');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `faculties`
--
ALTER TABLE `faculties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `university_id` (`university_id`,`name`) USING HASH;

--
-- Indices de la tabla `mentor_subject`
--
ALTER TABLE `mentor_subject`
  ADD PRIMARY KEY (`user_id`,`subject_id`);

--
-- Indices de la tabla `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `faculty_id` (`faculty_id`,`name`) USING HASH;

--
-- Indices de la tabla `universities`
--
ALTER TABLE `universities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`) USING HASH;

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`) USING HASH;

--
-- Indices de la tabla `user_subject_role`
--
ALTER TABLE `user_subject_role`
  ADD PRIMARY KEY (`user_id`,`subject_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `chats`
--
ALTER TABLE `chats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `faculties`
--
ALTER TABLE `faculties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `requests`
--
ALTER TABLE `requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `universities`
--
ALTER TABLE `universities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
