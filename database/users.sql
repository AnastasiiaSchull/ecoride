INSERT INTO users (id, pseudo, email, password, created_at, credits, is_active, photo) VALUES
(1, 'Jean Dupont', 'jean@gmail.com', '$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 120, 1, 'default.png'),
(2, 'Alice Martin', 'alice@gmail.com', '$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 50, 1, 'default.png'),
(3, 'Luc Moreau', 'luc@gmail.com', '$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 150, 1, 'default.png'),
(4, 'Emma Lefevre', 'emma@gmail.com', '$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 40, 1, 'default.png'),
(5, 'Francis Dupont', 'francis@gmail.com', '$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 120, 1, 'default.png'),
(6, 'Alice Spivak', 'spivak@gmail.com', '$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 50, 1, 'default.png'),
(7, 'Luc Schull', 'schull@gmail.com', '$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 150, 1, 'default.png'),
(8, 'Nastia Schull', 'nastia@gmail.com', '$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 40, 1, 'default.png');
(9, 'Employé1', 'employe1@gmail.com', '$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 0, 1, 'default.png'),
(10, 'Employé2', 'employe2@gmail.com', '$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 0, 1, 'default.png');

-- password==motdepasse


INSERT INTO user_roles (user_id, role_id) VALUES
(1, 2),
(2, 2),
(3, 1),
(4, 1),
(5, 1),
(6, 2),
(7, 2),
(8, 2);


INSERT INTO user_roles (user_id, role_id) VALUES
(6, 1),
(7, 1);

-- (Employé)
INSERT INTO user_roles (user_id, role_id) VALUES
(9, 3),
(10, 3);
