<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $character_id = $_POST['character_id'];
    $name = $_POST['name'];
    $alias = $_POST['alias'];
    $powers = $_POST['powers'];
    $affiliations = $_POST['affiliations'];
    $backstory = $_POST['backstory'];

    $sql = "UPDATE Characters 
            SET name = :name, alias = :alias, powers = :powers, affiliations = :affiliations, backstory = :backstory 
            WHERE character_id = :character_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':character_id' => $character_id,
        ':name' => $name,
        ':alias' => $alias,
        ':powers' => $powers,
        ':affiliations' => $affiliations,
        ':backstory' => $backstory
    ]);

    echo "Character updated successfully!";
}
?>
