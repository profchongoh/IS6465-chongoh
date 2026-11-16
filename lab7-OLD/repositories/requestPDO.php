<?php
function insertTutoringRequest($pdo, $studentId, $data) {
    $stmt=$pdo->prepare('INSERT INTO tutoring_requests(student_id,course,topic,preferred_date,preferred_time) VALUES(?,?,?,?,?)');
    $stmt->execute([$studentId,$data['course'],$data['topic'],$data['preferred_date'],$data['preferred_time']]);
    return (int)$pdo->lastInsertId();
}
function findRequest($pdo, $id) {
    $stmt=$pdo->prepare('SELECT r.*,s.student_name,s.student_email FROM tutoring_requests r JOIN students s ON r.student_id=s.student_id WHERE r.request_id=?');
    $stmt->execute([$id]);return $stmt->fetch();
}
function listRequests($pdo, $course = '', $status = '') {
    $sql='SELECT r.*,s.student_name FROM tutoring_requests r JOIN students s ON r.student_id=s.student_id WHERE 1=1';$args=[];
    if($course!==''){$sql.=' AND r.course = ?';$args[]=$course;}
    if($status!==''){$sql.=' AND r.status = ?';$args[]=$status;}
    $sql.=' ORDER BY r.request_id DESC';$stmt=$pdo->prepare($sql);$stmt->execute($args);return $stmt->fetchAll();
}
function updateOpenRequest($pdo, $id, $data) {
    $stmt=$pdo->prepare("UPDATE tutoring_requests SET course=?,topic=?,preferred_date=?,preferred_time=? WHERE request_id=? AND status='Open'");
    $stmt->execute([$data['course'],$data['topic'],$data['preferred_date'],$data['preferred_time'],$id]);return $stmt->rowCount()===1;
}
function cancelOpenRequest($pdo, $id) {
    $stmt=$pdo->prepare("UPDATE tutoring_requests SET status='Cancelled' WHERE request_id=? AND status='Open'");
    $stmt->execute([$id]);return $stmt->rowCount()===1;
}
function findRequestForStudentUser($pdo, $id, $userId) {
    $stmt=$pdo->prepare('SELECT r.*,s.student_name,s.student_email FROM tutoring_requests r JOIN students s ON r.student_id=s.student_id WHERE r.request_id=:id AND s.user_id=:user_id');
    $stmt->execute(['id'=>$id,'user_id'=>$userId]); return $stmt->fetch();
}
function listRequestsForStudentUser($pdo, $userId, $course = '', $status = '') {
    $sql='SELECT r.*,s.student_name FROM tutoring_requests r JOIN students s ON r.student_id=s.student_id WHERE s.user_id=:user_id';$args=['user_id'=>$userId];
    if($course!==''){$sql.=' AND r.course=:course';$args['course']=$course;}if($status!==''){$sql.=' AND r.status=:status';$args['status']=$status;}
    $sql.=' ORDER BY r.request_id DESC';$stmt=$pdo->prepare($sql);$stmt->execute($args);return $stmt->fetchAll();
}
function updateOpenRequestForStudentUser($pdo, $id, $userId, $data) {
    $stmt=$pdo->prepare("UPDATE tutoring_requests SET course=:course,topic=:topic,preferred_date=:date,preferred_time=:time WHERE request_id=:id AND status='Open' AND student_id=(SELECT student_id FROM students WHERE user_id=:user_id)");
    $stmt->execute(['course'=>$data['course'],'topic'=>$data['topic'],'date'=>$data['preferred_date'],'time'=>$data['preferred_time'],'id'=>$id,'user_id'=>$userId]);return $stmt->rowCount()===1;
}
function cancelOpenRequestForStudentUser($pdo, $id, $userId) {
    $stmt=$pdo->prepare("UPDATE tutoring_requests SET status='Cancelled' WHERE request_id=:id AND status='Open' AND student_id=(SELECT student_id FROM students WHERE user_id=:user_id)");$stmt->execute(['id'=>$id,'user_id'=>$userId]);return $stmt->rowCount()===1;
}
