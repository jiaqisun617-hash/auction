<?php
function connectDB() {
    $host = 'localhost';
    $user = 'jiaqi_user';  
    $password = 'UCL2025';  
    $database = 'auctiondb';

    $conn = mysqli_connect($host, $user, $password, $database);
    if (!$conn) {
        die('Database connection failed.');
    }
    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
}
