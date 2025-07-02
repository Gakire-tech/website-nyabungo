-- Schéma de base de données pour NYABUNGO RESTAURANT & BAR

-- Table des succursales
CREATE TABLE branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(30),
    email VARCHAR(100),
    google_maps_link VARCHAR(255),
    opening_hours TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des utilisateurs (admin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    role ENUM('admin','editor') DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des menus (catégories)
CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    type ENUM('restaurant','bar') NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

-- Table des items de menu
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    allergens VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
);

-- Table des réservations
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    guests INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(100),
    special_requests TEXT,
    status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

-- Table des demandes d'événements
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    requested_date DATE NOT NULL,
    guests INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(100),
    message TEXT,
    status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

-- Table des images d'événements
CREATE TABLE event_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

-- Table des galeries
CREATE TABLE galleries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT,
    image VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

-- Table des témoignages
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT,
    author VARCHAR(100),
    content TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

-- Table du contenu du site (pages dynamiques) améliorée
DROP TABLE IF EXISTS site_content;
CREATE TABLE site_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page VARCHAR(50) NOT NULL,           -- ex: 'accueil', 'contact', 'footer'
    block VARCHAR(50) DEFAULT NULL,      -- ex: 'intro', 'section1', 'footer-main' (pour plusieurs blocs par page)
    title VARCHAR(255) DEFAULT NULL,     -- Titre du bloc de contenu (optionnel)
    content TEXT NOT NULL,               -- Contenu HTML ou texte
    status ENUM('published','draft') DEFAULT 'published', -- Statut de publication
    display_order INT DEFAULT 0,         -- Ordre d'affichage si plusieurs blocs
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
); 