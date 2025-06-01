-- Database initialization script for News and Events system

-- Create database
CREATE DATABASE IF NOT EXISTS news_events_db;
USE news_events_db;

-- Create news table
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    images JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_title (title),
    INDEX idx_created_at (created_at)
);

-- Create events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    images JSON DEFAULT NULL,
    event_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_title (title),
    INDEX idx_event_date (event_date),
    INDEX idx_created_at (created_at)
);

-- Create junction table for many-to-many relationship
CREATE TABLE news_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    news_id INT NOT NULL,
    event_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_news_event (news_id, event_id),
    INDEX idx_news_id (news_id),
    INDEX idx_event_id (event_id)
);

-- Insert sample data
INSERT INTO news (title, content, images) VALUES
('Breaking: New Technology Launched', 'A revolutionary new technology has been announced today that will change the industry forever.', '["tech1.jpg", "tech2.jpg"]'),
('Market Update: Stocks Rise', 'The stock market showed significant gains today as investors responded positively to recent economic indicators.', '["market1.jpg"]'),
('Weather Alert Issued', 'Meteorologists have issued a weather alert for the upcoming weekend due to expected severe storms.', '["weather1.jpg", "storm.jpg"]');

INSERT INTO events (title, content, images, event_date) VALUES
('Tech Conference 2025', 'Annual technology conference featuring the latest innovations and industry leaders.', '["conference1.jpg", "speakers.jpg"]', '2025-07-15 09:00:00'),
('Market Analysis Seminar', 'Deep dive into current market trends and investment strategies.', '["seminar1.jpg"]', '2025-06-20 14:00:00'),
('Emergency Preparedness Workshop', 'Learn how to prepare for severe weather and natural disasters.', '["workshop1.jpg"]', '2025-06-10 10:00:00'),
('Past Tech Summit', 'Previous technology summit that showcased emerging trends.', '["summit1.jpg"]', '2025-04-15 09:00:00');

-- Create relationships
INSERT INTO news_events (news_id, event_id) VALUES
(1, 1), -- Breaking tech news connected to Tech Conference
(2, 2), -- Market update connected to Market Analysis Seminar
(3, 3), -- Weather alert connected to Emergency Preparedness Workshop
(1, 4); -- Breaking tech news also connected to Past Tech Summit

-- Test data insertion queries for News and Events system

-- Insert additional news articles
INSERT INTO news (title, content, images) VALUES
('AI Revolution in Healthcare', 'Artificial intelligence is transforming healthcare with new diagnostic tools and treatment recommendations. Hospitals worldwide are implementing AI-powered systems to improve patient outcomes and reduce costs. The technology is showing remarkable results in early disease detection and personalized medicine approaches.', '["ai_healthcare.jpg", "medical_ai.jpg", "hospital_tech.jpg"]'),

('Climate Change Summit Announces New Initiatives', 'World leaders gathered at the international climate summit to announce groundbreaking initiatives for carbon reduction. The comprehensive plan includes renewable energy investments, forest preservation programs, and innovative carbon capture technologies. This represents the most ambitious climate action plan to date.', '["climate_summit.jpg", "renewable_energy.jpg"]'),

('Cryptocurrency Market Reaches All-Time High', 'The cryptocurrency market has surged to unprecedented levels, with Bitcoin leading the charge. Market analysts attribute the growth to institutional adoption and regulatory clarity in major economies. Investors are showing renewed confidence in digital assets as inflation hedges.', '["crypto_chart.jpg", "bitcoin.jpg", "trading_floor.jpg"]'),

('Space Exploration Milestone Achieved', 'NASA successfully launched its latest Mars exploration mission, marking a significant milestone in space exploration. The mission includes advanced rovers equipped with cutting-edge scientific instruments designed to search for signs of ancient microbial life. This represents humanity\'s most sophisticated attempt to understand Mars\' geological history.', '["mars_rover.jpg", "rocket_launch.jpg", "nasa_control.jpg"]'),

('Global Food Security Initiative Launched', 'International organizations have launched a comprehensive food security initiative aimed at addressing hunger in developing nations. The program combines sustainable farming techniques, technology transfer, and climate-resilient crop varieties. Early pilot programs show promising results in increasing crop yields while reducing environmental impact.', '["farming_tech.jpg", "food_security.jpg"]'),

('Cybersecurity Breach Affects Major Corporations', 'A sophisticated cyberattack has compromised data from several Fortune 500 companies, highlighting the urgent need for enhanced cybersecurity measures. Security experts are working around the clock to assess the damage and implement stronger protection protocols. The incident serves as a wake-up call for corporate digital security.', '["cybersecurity.jpg", "data_breach.jpg", "security_team.jpg"]');

-- Insert additional events
INSERT INTO events (title, content, images, event_date) VALUES
('AI Healthcare Innovation Conference', 'Leading experts in artificial intelligence and healthcare will present the latest breakthroughs in medical AI technology. Sessions cover diagnostic AI, robotic surgery, drug discovery, and ethical considerations in medical AI implementation.', '["ai_conference.jpg", "medical_experts.jpg"]', '2025-08-15 09:00:00'),

('Global Climate Action Summit 2025', 'International summit bringing together world leaders, scientists, and environmental activists to discuss climate change solutions. The event features keynote speeches, panel discussions, and the unveiling of new climate initiatives.', '["climate_conference.jpg", "world_leaders.jpg", "green_tech.jpg"]', '2025-09-22 08:00:00'),

('Blockchain and Cryptocurrency Expo', 'Comprehensive exhibition showcasing the latest developments in blockchain technology and cryptocurrency applications. Industry leaders will demonstrate new platforms, discuss regulatory developments, and explore future trends in digital finance.', '["blockchain_expo.jpg", "crypto_displays.jpg"]', '2025-07-10 10:00:00'),

('Mars Mission Launch Event', 'Live coverage and celebration of NASA\'s Mars exploration mission launch. The event includes pre-launch presentations, real-time launch viewing, and post-launch analysis with mission scientists and engineers.', '["launch_event.jpg", "mission_control.jpg"]', '2025-06-05 06:00:00'),

('Sustainable Agriculture Workshop', 'Hands-on workshop focusing on sustainable farming practices and food security solutions. Participants will learn about climate-resilient crops, efficient irrigation systems, and technology integration in modern agriculture.', '["agriculture_workshop.jpg", "sustainable_farming.jpg"]', '2025-10-08 09:30:00'),

('Cybersecurity Defense Summit', 'Emergency summit addressing recent cybersecurity threats and discussing advanced protection strategies. Industry experts will share best practices, demonstrate new security tools, and collaborate on threat response protocols.', '["security_summit.jpg", "cyber_experts.jpg"]', '2025-06-12 13:00:00'),

('Future of Work Conference', 'Conference exploring how technology is reshaping the workplace, featuring discussions on remote work, AI automation, digital transformation, and workforce development strategies for the digital age.', '["future_work.jpg", "digital_workplace.jpg"]', '2025-11-18 09:00:00'),

('Renewable Energy Innovation Fair', 'Exhibition showcasing cutting-edge renewable energy technologies including solar innovations, wind power developments, and energy storage solutions. The fair features live demonstrations and investment opportunities.', '["renewable_fair.jpg", "solar_panels.jpg", "wind_turbines.jpg"]', '2025-08-28 10:00:00'),

('International Space Collaboration Meeting', 'High-level meeting between space agencies to discuss future collaborative missions, technology sharing, and joint exploration initiatives. Topics include lunar bases, asteroid mining, and interplanetary travel.', '["space_meeting.jpg", "astronauts.jpg"]', '2025-12-03 14:00:00'),

('Digital Health Technology Symposium', 'Symposium focusing on telemedicine, wearable health devices, health data analytics, and patient privacy in the digital health ecosystem. Medical professionals and tech innovators will share insights and case studies.', '["digital_health.jpg", "telemedicine.jpg"]', '2025-07-25 08:30:00');

-- Create relationships between news and events
INSERT INTO news_events (news_id, event_id) VALUES
-- AI Healthcare news connected to AI Healthcare Conference
((SELECT id FROM news WHERE title = 'AI Revolution in Healthcare'), (SELECT id FROM events WHERE title = 'AI Healthcare Innovation Conference')),

-- Climate Change news connected to Climate Summit
((SELECT id FROM news WHERE title = 'Climate Change Summit Announces New Initiatives'), (SELECT id FROM events WHERE title = 'Global Climate Action Summit 2025')),

-- Cryptocurrency news connected to Blockchain Expo
((SELECT id FROM news WHERE title = 'Cryptocurrency Market Reaches All-Time High'), (SELECT id FROM events WHERE title = 'Blockchain and Cryptocurrency Expo')),

-- Space Exploration news connected to Mars Mission Launch Event
((SELECT id FROM news WHERE title = 'Space Exploration Milestone Achieved'), (SELECT id FROM events WHERE title = 'Mars Mission Launch Event')),

-- Food Security news connected to Sustainable Agriculture Workshop
((SELECT id FROM news WHERE title = 'Global Food Security Initiative Launched'), (SELECT id FROM events WHERE title = 'Sustainable Agriculture Workshop')),

-- Cybersecurity news connected to Cybersecurity Defense Summit
((SELECT id FROM news WHERE title = 'Cybersecurity Breach Affects Major Corporations'), (SELECT id FROM events WHERE title = 'Cybersecurity Defense Summit')),

-- Additional cross-connections for demonstration
-- AI Healthcare news also connected to Digital Health Symposium
((SELECT id FROM news WHERE title = 'AI Revolution in Healthcare'), (SELECT id FROM events WHERE title = 'Digital Health Technology Symposium')),

-- Climate Change news also connected to Renewable Energy Fair
((SELECT id FROM news WHERE title = 'Climate Change Summit Announces New Initiatives'), (SELECT id FROM events WHERE title = 'Renewable Energy Innovation Fair')),

-- Space Exploration news also connected to International Space Collaboration Meeting
((SELECT id FROM news WHERE title = 'Space Exploration Milestone Achieved'), (SELECT id FROM events WHERE title = 'International Space Collaboration Meeting'));

-- Test queries to verify the data
SELECT 'News Articles Created:' as info;
SELECT id, title, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as created 
FROM news 
ORDER BY created_at DESC;

SELECT 'Events Created:' as info;
SELECT id, title, DATE_FORMAT(event_date, '%Y-%m-%d %H:%i') as event_date 
FROM events 
ORDER BY event_date ASC;

SELECT 'News-Events Relationships:' as info;
SELECT 
    n.title as news_title,
    e.title as event_title,
    DATE_FORMAT(e.event_date, '%Y-%m-%d') as event_date
FROM news_events ne
JOIN news n ON ne.news_id = n.id
JOIN events e ON ne.event_id = e.id
ORDER BY e.event_date ASC;