// Importation des modules nécessaires
const express = require('express');
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const nodemailer = require('nodemailer');
const mysql = require('mysql2/promise');
const cookieParser = require('cookie-parser');
const crypto = require('crypto');

// Initialisation de l'application Express
const app = express();
app.use(express.json());
app.use(cookieParser());

// Configuration des variables d'environnement
const PORT = 3000;
const SECRET_KEY = "votre_clé_secrète";
const RESET_TOKEN_EXPIRATION = 10 * 60 * 1000; // 10 minutes

// Configuration MySQL
const db = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: 'votre_mot_de_passe',
    database: 'authSystem'
});

// Fonction utilitaire pour exécuter des requêtes MySQL
async function query(sql, params) {
    const [rows] = await db.execute(sql, params);
    return rows;
}

// Configuration de Nodemailer
const transporter = nodemailer.createTransport({
    service: 'gmail',
    auth: {
        user: 'votre_email@gmail.com',
        pass: 'votre_mot_de_passe'
    }
});

// Fonction utilitaire pour générer un token JWT
function generateToken(userId) {
    return jwt.sign({ id: userId }, SECRET_KEY, { expiresIn: '1h' });
}

// Route d'inscription
app.post('/signup', async (req, res) => {
    const { fullName, email, password } = req.body;
    try {
        // Vérification si l'utilisateur existe déjà
        const existingUser = await query('SELECT * FROM users WHERE email = ?', [email]);
        if (existingUser.length > 0) return res.status(400).json({ message: 'Email déjà utilisé.' });

        // Hachage du mot de passe
        const hashedPassword = await bcrypt.hash(password, 10);

        // Création de l'utilisateur
        await query('INSERT INTO users (fullName, email, password) VALUES (?, ?, ?)', [fullName, email, hashedPassword]);

        res.status(201).json({ message: 'Utilisateur créé avec succès !' });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// Route de connexion
app.post('/login', async (req, res) => {
    const { email, password } = req.body;
    try {
        // Vérification de l'utilisateur
        const users = await query('SELECT * FROM users WHERE email = ?', [email]);
        if (users.length === 0) return res.status(400).json({ message: 'Email ou mot de passe incorrect.' });
        const user = users[0];

        // Vérification du mot de passe
        const isPasswordValid = await bcrypt.compare(password, user.password);
        if (!isPasswordValid) return res.status(400).json({ message: 'Email ou mot de passe incorrect.' });

        // Génération du token
        const token = generateToken(user.id);

        // Envoi du cookie
        res.cookie('token', token, { httpOnly: true, secure: false });
        res.status(200).json({ message: 'Connexion réussie !' });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// Route de mot de passe oublié
app.post('/forgot-password', async (req, res) => {
    const { email } = req.body;
    try {
        const users = await query('SELECT * FROM users WHERE email = ?', [email]);
        if (users.length === 0) return res.status(404).json({ message: "Utilisateur non trouvé." });
        const user = users[0];

        // Génération d'un token sécurisé
        const resetToken = crypto.randomBytes(32).toString('hex');
        const resetTokenExpiration = Date.now() + RESET_TOKEN_EXPIRATION;

        // Mise à jour du token dans la base de données
        await query('UPDATE users SET resetToken = ?, resetTokenExpiration = ? WHERE id = ?', [resetToken, resetTokenExpiration, user.id]);

        // Envoi de l'email
        const resetLink = `http://localhost:${PORT}/reset-password/${resetToken}`;
        await transporter.sendMail({
            to: user.email,
            subject: 'Réinitialisation du mot de passe',
            text: `Cliquez sur ce lien pour réinitialiser votre mot de passe : ${resetLink}`
        });

        res.status(200).json({ message: 'Lien de réinitialisation envoyé par email.' });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// Route de réinitialisation du mot de passe
app.post('/reset-password/:token', async (req, res) => {
    const { token } = req.params;
    const { newPassword } = req.body;
    try {
        const users = await query('SELECT * FROM users WHERE resetToken = ? AND resetTokenExpiration > ?', [token, Date.now()]);
        if (users.length === 0) return res.status(400).json({ message: 'Lien invalide ou expiré.' });
        const user = users[0];

        // Mise à jour du mot de passe
        const hashedPassword = await bcrypt.hash(newPassword, 10);
        await query('UPDATE users SET password = ?, resetToken = NULL, resetTokenExpiration = NULL WHERE id = ?', [hashedPassword, user.id]);

        res.status(200).json({ message: 'Mot de passe réinitialisé avec succès !' });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// Route de déconnexion
app.post('/logout', (req, res) => {
    res.clearCookie('token');
    res.status(200).json({ message: 'Déconnexion réussie.' });
});

// Démarrage du serveur
app.listen(PORT, () => {
    console.log(`Serveur en cours d'exécution sur http://localhost:${PORT}`);
});
