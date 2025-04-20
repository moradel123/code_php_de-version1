<?php
require_once 'config.php';

// Données du médecin (normalement venant d'un formulaire)
$doctorData = [
    'nom' => $_POST['nom'],
    'prenom' => $_POST['prenom'],
    'pb' => $_POST['pb'],
    'age' => $_POST['age'],
    'numero_Tele' => $_POST['numero_Tele'],
    'email' => $_POST['email'],
    'adress' => $_POST['adress'],
    'gender' => $_POST['gender'],
    'salary' => $_POST['salary'],
    'departement' => $_POST['departement'],
    'role' => $_POST['role']
];

try {
    $pdo->beginTransaction();
    
    // Insertion du médecin
    $stmt = $pdo->prepare("INSERT INTO doctors 
                          (nom, prenom, pb, age, numero_Tele, email, adress, gender, salary, departement, role) 
                          VALUES 
                          (:nom, :prenom, :pb, :age, :numero_Tele, :email, :adress, :gender, :salary, :departement, :role)");
    $stmt->execute($doctorData);
    $doctorId = $pdo->lastInsertId();
    
    // Emploi du temps par défaut
    $defaultSchedule = [
        'Lundi' => [1, 2, 3],    // 02:00-08:00
        'Mardi' => [4, 5, 6],    // 08:00-14:00
        'Mercredi' => [7, 8, 9], // 14:00-20:00
        'Jeudi' => [0, 10, 11],  // 00:00-02:00 + 20:00-00:00
        'Vendredi' => [],        // Repos
        'Samedi' => [3, 4, 5],   // 06:00-12:00
        'Dimanche' => [6, 7, 8]  // 12:00-18:00
    ];
    
    // Insertion de l'emploi du temps
    $scheduleStmt = $pdo->prepare("INSERT INTO doctor_schedules 
                                  (doctor_id, day_of_week, time_slots, is_day_off) 
                                  VALUES (:doctor_id, :day_of_week, :time_slots, :is_day_off)");
    
    foreach ($defaultSchedule as $day => $slots) {
        $isDayOff = ($day === 'Vendredi');
        $scheduleStmt->execute([
            'doctor_id' => $doctorId,
            'day_of_week' => $day,
            'time_slots' => json_encode($slots),
            'is_day_off' => $isDayOff
        ]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'doctorId' => $doctorId]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>