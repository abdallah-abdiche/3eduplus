<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

// Security check
checkAuth();
checkRole(['Admin']);

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Start transaction for safety
    $conn->begin_transaction();

    try {
        // Broad list of tables that might have user_id constraints
        $related_tables = [
            'panier' => ['utilisateur_id', 'user_id'],
            'inscriptions' => ['user_id'],
            'paiements' => ['user_id'],
            'certificats' => ['user_id'],
            'notifications' => ['user_id'],
            'temoignages' => ['user_id'],
            'user_progress' => ['user_id'],
            'user_certificates' => ['user_id'],
            'recus' => ['user_id'],
            'commentaires' => ['user_id'],
            'favoris' => ['user_id'],
            'messagesscriptions' => ['user_id'], // Noticed this weird table name in list
            'planning' => ['user_id'],
            'sessions' => ['user_id', 'formateur_id']
        ];

        foreach ($related_tables as $table => $columns) {
            // Check if table exists
            $exists = $conn->query("SHOW TABLES LIKE '$table'");
            if ($exists && $exists->num_rows > 0) {
                foreach ($columns as $column) {
                    // Check if column exists in this table
                    $col_exists = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
                    if ($col_exists && $col_exists->num_rows > 0) {
                        $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$column` = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
        }

        // Finally delete the user
        $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        header("Location: users.php?msg=deleted");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: users.php?msg=error&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: users.php");
    exit();
}
?>