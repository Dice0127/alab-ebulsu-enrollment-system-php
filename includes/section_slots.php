<?php
// ============================================================
//  section_slots.php — keeps sections.current_enrolled in sync
//  with actual enrollment records.
//
//  A section slot is considered "held" while an enrollment's
//  status is Pending or Approved, and "released" once it becomes
//  Rejected or the enrollment row is deleted. Every code path that
//  changes an enrollment's status or deletes an enrollment must
//  go through these helpers instead of touching current_enrolled
//  directly, or the count will drift from reality over time.
// ============================================================

/** Does this enrollment status currently occupy a section slot? */
function enrollment_holds_slot(string $status): bool {
    return in_array($status, ['Pending', 'Approved'], true);
}

/**
 * Reserves one slot in a section (used when an enrollment moves INTO
 * a slot-holding status). Row-locks the section so concurrent
 * requests can't oversell it. Returns true on success, false if the
 * section is full/closed/invalid — caller should abort the status
 * change and report the failure.
 */
function reserve_section_slot(mysqli $conn, int $sectionId): bool {
    if ($sectionId <= 0) return false;

    $stmt = $conn->prepare("SELECT current_enrolled, max_capacity, status FROM sections WHERE section_id = ? FOR UPDATE");
    $stmt->bind_param('i', $sectionId);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows === 0) return false;
    $section = $res->fetch_assoc();

    if ($section['status'] === 'Closed' || (int)$section['current_enrolled'] >= (int)$section['max_capacity']) {
        return false;
    }

    $upd = $conn->prepare(
        "UPDATE sections SET current_enrolled = current_enrolled + 1,
             status = CASE WHEN current_enrolled + 1 >= max_capacity THEN 'Full' ELSE status END
         WHERE section_id = ? AND current_enrolled < max_capacity"
    );
    $upd->bind_param('i', $sectionId);
    $upd->execute();
    return $conn->affected_rows > 0;
}

/**
 * Releases one slot in a section (used when an enrollment moves OUT
 * of a slot-holding status, e.g. Rejected, or is deleted). Reopens
 * the section if it had been marked Full purely due to capacity
 * (a manually-Closed section stays Closed).
 */
function release_section_slot(mysqli $conn, int $sectionId): void {
    if ($sectionId <= 0) return;

    $stmt = $conn->prepare(
        "UPDATE sections SET
             current_enrolled = GREATEST(0, current_enrolled - 1),
             status = CASE WHEN status = 'Full' THEN 'Open' ELSE status END
         WHERE section_id = ?"
    );
    $stmt->bind_param('i', $sectionId);
    $stmt->execute();
}
?>
