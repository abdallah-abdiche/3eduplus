<?php
session_start();
require_once '../../config.php';
require_once '../../auth.php';

// Security check
checkAuth();
checkRole(['Admin']);

if (isset($_GET['id'])) {
    $formation_id = intval($_GET['id']);

    // Start transaction for safety
    $conn->begin_transaction();

    try {
        // Broad list of tables that might have formation_id constraints
        $related_tables = [
            'inscriptions' => ['formation_id'],
            'paiement_formations' => ['formation_id'],
            'certificats' => ['formation_id'],
            'temoignages' => ['formation_id'],
            'user_progress' => ['formation_id'],
            'course_videos' => ['formation_id'],
            'quizzes' => ['formation_id'],
            'panier' => ['formation_id'],
            'pack_formations' => ['formation_id'],
            'sondages' => ['formation_id'],
            'commentaires' => ['formation_id'],
            'favoris' => ['formation_id'],
            'paiements' => ['formation_id']
        ];

        foreach ($related_tables as $table => $columns) {
            // Check if table exists
            $exists = $conn->query("SHOW TABLES LIKE '$table'");
            if ($exists && $exists->num_rows > 0) {
                foreach ($columns as $column) {
                    // Check if column exists
                    $col_exists = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
                    if ($col_exists && $col_exists->num_rows > 0) {
                        $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$column` = ?");
                        $stmt->bind_param("i", $formation_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
        }

        // Finally delete the course
        $stmt = $conn->prepare("DELETE FROM formations WHERE formation_id = ?");
        $stmt->bind_param("i", $formation_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        header("Location: courses.php?msg=deleted");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: courses.php?msg=error&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: courses.php");
    exit();
}
?>
