-- Données de test pour NYABUNGO RESTAURANT & BAR

-- Branches
INSERT INTO branches (id, name, address, phone, email, google_maps_link, opening_hours) VALUES
(1, 'Mutanga', 'Avenue de la Jeunesse, Quartier Mutanga Nord, Bujumbura', '+25722223333', 'mutanga@nyabungo.com', 'https://maps.google.com/?q=-3.3822,29.3644', '11h30–14h30, 18h30–22h30'),
(2, 'Mutakura', 'Avenue de l''Unité, Quartier Mutakura, Bujumbura', '+25722224444', 'mutakura@nyabungo.com', 'https://maps.google.com/?q=-3.3800,29.3700', '12h00–15h00, 19h00–23h00');

-- Utilisateurs (admin)
INSERT INTO users (id, username, password_hash, email, is_active, role) VALUES
(1, 'admin', '$2y$10$abcdefghijklmnopqrstuv', 'admin@nyabungo.com', 1, 'admin'),
(2, 'editor', '$2y$10$abcdefghijklmnopqrstuv', 'editor@nyabungo.com', 1, 'editor');

-- Menus (catégories)
INSERT INTO menus (id, branch_id, type, name, description, is_active) VALUES
(1, 1, 'restaurant', 'Menu Restaurant Mutanga', 'Plats raffinés du chef', 1),
(2, 1, 'bar', 'Menu Bar Mutanga', 'Sélection de boissons', 1),
(3, 2, 'restaurant', 'Menu Restaurant Mutakura', 'Cuisine locale et internationale', 1),
(4, 2, 'bar', 'Menu Bar Mutakura', 'Cocktails et softs', 1);

-- Items de menu
INSERT INTO menu_items (menu_id, name, description, price, allergens, is_available, image) VALUES
(1, 'Filet de capitaine', 'Filet de poisson grillé, sauce citronnée', 18000, 'Poisson', 1, 'capitaine.jpg'),
(1, 'Poulet façon chef', 'Poulet fermier, légumes croquants', 15000, '', 1, 'poulet.jpg'),
(2, 'Cocktail maison', 'Cocktail signature du bar', 8000, 'Alcool', 1, 'cocktail.jpg'),
(2, 'Jus de fruits frais', 'Jus pressé à la demande', 5000, '', 1, 'jus.jpg'),
(3, 'Brochettes de boeuf', 'Brochettes grillées, frites maison', 12000, '', 1, 'brochettes.jpg'),
(4, 'Bière locale', 'Sélection de bières burundaises', 4000, 'Gluten', 1, 'biere.jpg');

-- Réservations
INSERT INTO reservations (branch_id, reservation_date, reservation_time, guests, name, phone, email, special_requests, status) VALUES
(1, '2025-06-15', '19:30:00', 4, 'Jean Dupont', '+25761234567', 'jean.dupont@email.com', 'Table près de la fenêtre', 'confirmed'),
(2, '2025-06-16', '20:00:00', 2, 'Aline Niyonzima', '+25769876543', 'aline@email.com', '', 'pending');

-- Événements
INSERT INTO events (branch_id, event_type, requested_date, guests, name, phone, email, message, status) VALUES
(1, 'Anniversaire', '2025-07-01', 10, 'Paul Bizimana', '+25761231231', 'paul@email.com', 'Décoration spéciale', 'pending'),
(2, 'Dîner d''affaires', '2025-07-05', 6, 'Marie Irakoze', '+25769998877', 'marie@email.com', '', 'confirmed');

-- Images d'événements
INSERT INTO event_images (branch_id, image_path, alt_text, display_order) VALUES
(1, 'event_mutanga_1.jpg', 'Réception élégante à Mutanga', 1),
(1, 'event_mutanga_2.jpg', 'Soirée festive Mutanga', 2),
(1, 'event_mutanga_3.jpg', 'Mariage au jardin Mutanga', 3),
(1, 'event_mutanga_4.jpg', 'Séminaire professionnel Mutanga', 4),
(2, 'event_mutakura_1.jpg', 'Dîner privé à Mutakura', 1),
(2, 'event_mutakura_2.jpg', 'Cérémonie Mutakura', 2),
(2, 'event_mutakura_3.jpg', 'Anniversaire enfant Mutakura', 3),
(2, 'event_mutakura_4.jpg', 'Conférence Mutakura', 4);

-- Galeries
INSERT INTO galleries (branch_id, image, caption, is_active, display_order) VALUES
(1, 'salle1.jpg', 'Salle principale Mutanga', 1, 1),
(1, 'plat1.jpg', 'Plat signature', 1, 2),
(2, 'salle2.jpg', 'Salle Mutakura', 1, 1),
(2, 'cocktail2.jpg', 'Cocktail du bar', 1, 2);

-- Témoignages
INSERT INTO testimonials (branch_id, author, content, is_active) VALUES
(1, 'Claire', 'Un service exceptionnel et une cuisine délicieuse !', 1),
(2, 'Eric', 'Ambiance chaleureuse, je recommande.', 1);

-- Contenu dynamique (site_content)
INSERT INTO site_content (page, block, title, content, status, display_order) VALUES
('accueil', 'intro', 'Bienvenue', '<h2>Bienvenue chez NYABUNGO</h2><p>Découvrez nos deux adresses d''exception à Bujumbura.</p>', 'published', 1),
('accueil', 'valeurs', 'Nos valeurs', '<p>Raffinement, convivialité, excellence culinaire.</p>', 'published', 2),
('contact', 'intro', 'Contactez-nous', '<p>Pour toute question ou réservation, contactez-nous via ce formulaire ou par téléphone.</p>', 'published', 1),
('footer', 'main', NULL, '&copy; 2025 NYABUNGO RESTAURANT & BAR - Tous droits réservés', 'published', 1),
('a-propos', 'histoire', 'Notre histoire', '<p>Fondé en 2022, NYABUNGO RESTAURANT & BAR est né de la passion pour la gastronomie et le raffinement.</p>', 'published', 1),
('a-propos', 'equipe', 'Notre équipe', '<p>Une équipe passionnée, engagée à faire de chaque visite un souvenir mémorable.</p>', 'published', 2);

-- Données de test pour la table `site_content`
INSERT INTO `site_content` (`key_name`, `content_value`) VALUES
('contact', '<h3>Bienvenue sur notre page de contact !</h3><p>N\'hésitez pas à nous contacter pour toute question, suggestion ou réservation. Notre équipe est disponible pour vous assister.</p><p><strong>Heures d\'ouverture:</strong> Lundi - Dimanche: 10:00 - 22:00</p>');

-- Données de test pour la table `contact_messages` (si nécessaire pour le développement)
INSERT INTO `contact_messages` (`name`, `email`, `message`) VALUES
('Jean Dupont', 'jean.dupont@example.com', 'Ceci est un message de test.'),
('Alice Smith', 'alice.smith@example.com', 'Demande d\'information sur les réservations.'); 